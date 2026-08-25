<?php
declare(strict_types=1);

final class FluxoReversao
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function reabrirInspecao(int $documentoId, ?string $motivo = null): void
    {
        $motivo = $this->validarMotivo($motivo);
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT i.id,i.status_id,s.nome status_nome,s.encerra_inspecao
              FROM inspecoes i JOIN status_inspecao s ON s.id=i.status_id WHERE i.documento_id=? FOR UPDATE");
            $q->execute([$documentoId]);
            $inspecao = $q->fetch();
            if (!$inspecao) throw new RuntimeException('Inspeção não encontrada.');
            if ((int)$inspecao['encerra_inspecao'] !== 1) throw new RuntimeException('A inspeção ainda está aberta.');

            $dep = $this->db->prepare("SELECT COUNT(*) FROM parcelas_pagamento WHERE documento_id=?");
            $dep->execute([$documentoId]);
            if ((int)$dep->fetchColumn() > 0) throw new RuntimeException('Existem parcelas programadas. Desfaça a Programação antes de reabrir a Inspeção.');

            $novoStatus = (int)$this->db->query("SELECT id FROM status_inspecao WHERE nome='Inspeção andamento' AND ativo=1 LIMIT 1")->fetchColumn();
            if ($novoStatus <= 0) throw new RuntimeException('Status Inspeção andamento não configurado.');

            $usuario = Auth::id();
            $this->db->prepare("UPDATE inspecoes SET status_id=?,data_conclusao=NULL,responsavel_id=?,atualizado_em=NOW() WHERE documento_id=?")
                ->execute([$novoStatus,$usuario,$documentoId]);
            $this->db->prepare("INSERT INTO inspecao_historico(inspecao_id,status_id,observacao,usuario_id) VALUES(?,?,?,?)")
                ->execute([(int)$inspecao['id'],$novoStatus,mb_substr('Reabertura de inspeção: '.$motivo,0,500),$usuario]);
            $this->auditar('inspecoes',(int)$inspecao['id'],'DESFAZER_CONCLUSAO',['status'=>(string)$inspecao['status_nome']],['status'=>'Inspeção andamento','motivo'=>$motivo]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function desfazerProgramacao(int $parcelaId, ?string $motivo = null): int
    {
        $motivo = $this->validarMotivo($motivo);
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT p.*,l.status status_liquidacao,gp.grupo_id,pg.status status_pagamento
              FROM parcelas_pagamento p JOIN liquidacoes l ON l.parcela_id=p.id
              LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id LEFT JOIN pagamentos pg ON pg.parcela_id=p.id
              WHERE p.id=? FOR UPDATE");
            $q->execute([$parcelaId]);
            $p = $q->fetch();
            if (!$p) throw new RuntimeException('Parcela não encontrada.');
            if ((string)$p['status_liquidacao'] !== 'AGUARDANDO') throw new RuntimeException('A Liquidação desta parcela já foi movimentada. Desfaça a Liquidação antes de desfazer a Programação.');
            if (!empty($p['grupo_id'])) throw new RuntimeException('A parcela pertence a um grupo CMDF. Desfaça a CMDF e a Liquidação antes de desfazer a Programação.');
            if (!empty($p['status_pagamento'])) throw new RuntimeException('Existe registro de Pagamento para esta parcela. Desfaça as etapas posteriores antes de desfazer a Programação.');

            $documentoId = (int)$p['documento_id'];
            $this->auditar('parcelas_pagamento',$parcelaId,'DESFAZER_PROGRAMACAO',$p,['excluida'=>true,'motivo'=>$motivo]);
            $this->db->prepare("DELETE FROM parcelas_pagamento WHERE id=?")->execute([$parcelaId]);
            $this->renumerarParcelas($documentoId);
            $this->db->commit();
            return $documentoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function desfazerLiquidacao(int $parcelaId, ?string $motivo = null): void
    {
        $motivo = $this->validarMotivo($motivo);
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT l.id,l.status,l.data_liquidacao,l.usuario_id,gp.grupo_id
              FROM liquidacoes l LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=l.parcela_id WHERE l.parcela_id=? FOR UPDATE");
            $q->execute([$parcelaId]);
            $l = $q->fetch();
            if (!$l) throw new RuntimeException('Liquidação não encontrada.');
            if ((string)$l['status'] === 'AGUARDANDO') throw new RuntimeException('A Liquidação já está em Aguardando.');
            if (!empty($l['grupo_id'])) throw new RuntimeException('A parcela já está vinculada à CMDF. Remova/desfaça a CMDF antes de desfazer a Liquidação.');

            $this->db->prepare("UPDATE liquidacoes SET status='AGUARDANDO',data_liquidacao=NULL,usuario_id=NULL,atualizado_em=NOW() WHERE parcela_id=?")->execute([$parcelaId]);
            $this->auditar('liquidacoes',(int)$l['id'],'DESFAZER_LIQUIDACAO',$l,['status'=>'AGUARDANDO','motivo'=>$motivo]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function desfazerCmdf(int $grupoId, ?string $motivo = null): string
    {
        $motivo = $this->validarMotivo($motivo);
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT * FROM cmdf_grupos WHERE id=? FOR UPDATE");
            $q->execute([$grupoId]);
            $g = $q->fetch();
            if (!$g) throw new RuntimeException('Grupo CMDF não encontrado.');
            $status = (string)$g['status'];
            if ($status === 'FECHADA') throw new RuntimeException('O grupo CMDF já está em Fechada. Para corrigir a composição, inclua ou remova parcelas.');

            if ($status === 'ATENDIDA') {
                $pagos = $this->db->prepare("SELECT COUNT(*) FROM pagamentos pg JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=pg.parcela_id WHERE gp.grupo_id=? AND pg.status='PAGO'");
                $pagos->execute([$grupoId]);
                if ((int)$pagos->fetchColumn() > 0) throw new RuntimeException('Há parcela(s) paga(s) neste grupo. Desfaça o Pagamento antes de desfazer a CMDF Atendida.');
                $this->db->prepare("DELETE pg FROM pagamentos pg JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=pg.parcela_id WHERE gp.grupo_id=? AND pg.status='AGUARDANDO'")->execute([$grupoId]);
                $novo = 'LIBERADA';
            } else {
                $novo = 'FECHADA';
            }

            $this->db->prepare("UPDATE cmdf_grupos SET status=?,atualizado_por=?,atualizado_em=NOW() WHERE id=?")->execute([$novo,Auth::id(),$grupoId]);
            $this->auditar('cmdf_grupos',$grupoId,'DESFAZER_CMDF',$g,['status'=>$novo,'motivo'=>$motivo]);
            $this->db->commit();
            return $novo;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function desfazerPagamento(int $parcelaId, ?string $motivo = null): void
    {
        $motivo = $this->validarMotivo($motivo);
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("SELECT * FROM pagamentos WHERE parcela_id=? FOR UPDATE");
            $q->execute([$parcelaId]);
            $pg = $q->fetch();
            if (!$pg) throw new RuntimeException('Pagamento não encontrado.');
            if ((string)$pg['status'] !== 'PAGO') throw new RuntimeException('Somente um pagamento com status Pago pode ser desfeito.');

            $this->db->prepare("UPDATE pagamentos SET status='AGUARDANDO',data_pagamento=NULL,valor_liquido_pago=NULL,historico_pagamento=NULL,usuario_id=NULL,atualizado_em=NOW() WHERE parcela_id=?")->execute([$parcelaId]);
            $this->auditar('pagamentos',(int)$pg['id'],'DESFAZER_PAGAMENTO',$pg,['status'=>'AGUARDANDO','motivo'=>$motivo]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function renumerarParcelas(int $documentoId): void
    {
        $st = $this->db->prepare("SELECT id FROM parcelas_pagamento WHERE documento_id=? ORDER BY numero_parcela,id");
        $st->execute([$documentoId]);
        $ids = array_map('intval',$st->fetchAll(PDO::FETCH_COLUMN));
        $tmp = 100000;
        foreach ($ids as $id) $this->db->prepare("UPDATE parcelas_pagamento SET numero_parcela=? WHERE id=?")->execute([$tmp++,$id]);
        $n = 1;
        foreach ($ids as $id) $this->db->prepare("UPDATE parcelas_pagamento SET numero_parcela=? WHERE id=?")->execute([$n++,$id]);
    }

    private function auditar(string $entidade, int $entidadeId, string $acao, mixed $antes, mixed $depois): void
    {
        $st = $this->db->prepare("INSERT INTO auditoria(usuario_id,entidade,entidade_id,acao,dados_anteriores,dados_novos,ip) VALUES(?,?,?,?,?,?,?)");
        $st->execute([Auth::id(),$entidade,$entidadeId,$acao,$this->json($antes),$this->json($depois),$_SERVER['REMOTE_ADDR'] ?? null]);
    }

    private function validarMotivo(?string $motivo): string
    {
        $motivo = trim((string)$motivo);
        if (mb_strlen($motivo) < 5) throw new InvalidArgumentException('Informe o motivo da reversão com pelo menos 5 caracteres.');
        return mb_substr($motivo,0,255);
    }

    private function json(mixed $value): ?string
    {
        if ($value === null) return null;
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
