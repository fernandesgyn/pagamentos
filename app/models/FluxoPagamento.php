<?php
declare(strict_types=1);

final class FluxoPagamento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function dashboard(): array
    {
        $sql = "SELECT
          (SELECT COUNT(*) FROM obrigacoes WHERE ativo=1) obrigacoes,
          (SELECT COUNT(*) FROM documentos_pagamento) documentos,
          (SELECT COUNT(*) FROM inspecoes i JOIN status_inspecao s ON s.id=i.status_id WHERE s.encerra_inspecao=0) em_inspecao,
          (SELECT COUNT(*) FROM liquidacoes WHERE status='AGUARDANDO') aguardando_liquidacao,
          ((SELECT COUNT(*) FROM cmdf_grupos WHERE status IN ('FECHADA','LIBERADA')) +
           (SELECT COUNT(*) FROM liquidacoes l
              JOIN parcelas_pagamento p ON p.id=l.parcela_id
              JOIN documentos_pagamento d ON d.id=p.documento_id
              LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
             WHERE l.status='LIQUIDADA' AND d.data_atesto IS NOT NULL AND gp.parcela_id IS NULL)) aguardando_cmdf,
          (SELECT COUNT(*) FROM pagamentos WHERE status='PAGO') pagos";
        return $this->db->query($sql)->fetch() ?: [];
    }

    public function obrigacoes(): array
    {
        $sql = "SELECT o.*,t.nome tipo,f.razao_social fornecedor,
            GROUP_CONCAT(DISTINCT CONCAT(fr.codigo,' - ',fr.nome) ORDER BY fr.codigo SEPARATOR ' | ') fontes_recurso,
            GROUP_CONCAT(DISTINCT CONCAT(nd.codigo,' - ',nd.nome) ORDER BY nd.codigo SEPARATOR ' | ') naturezas_despesa
          FROM obrigacoes o
          JOIN tipos_obrigacao t ON t.id=o.tipo_obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          LEFT JOIN obrigacao_fontes_recurso ofr ON ofr.obrigacao_id=o.id
          LEFT JOIN fontes_recurso fr ON fr.id=ofr.fonte_recurso_id
          LEFT JOIN obrigacao_naturezas_despesa ond ON ond.obrigacao_id=o.id
          LEFT JOIN naturezas_despesa nd ON nd.id=ond.natureza_despesa_id
          GROUP BY o.id,t.nome,f.razao_social
          ORDER BY o.ano DESC,o.numero DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obrigacao(int $id): ?array
    {
        $st = $this->db->prepare("SELECT o.*,f.razao_social fornecedor,f.documento fornecedor_documento,t.nome tipo
          FROM obrigacoes o
          JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN tipos_obrigacao t ON t.id=o.tipo_obrigacao_id
          WHERE o.id=?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function fornecedores(): array
    {
        return $this->db->query("SELECT * FROM fornecedores WHERE ativo=1 ORDER BY razao_social")->fetchAll();
    }

    public function tiposObrigacao(): array
    {
        return $this->db->query("SELECT * FROM tipos_obrigacao WHERE ativo=1 ORDER BY nome")->fetchAll();
    }

    public function tiposDocumento(): array
    {
        return $this->db->query("SELECT * FROM tipos_documento_pagamento WHERE ativo=1 ORDER BY nome")->fetchAll();
    }

    public function fontesRecurso(): array
    {
        return $this->db->query("SELECT * FROM fontes_recurso WHERE ativo=1 ORDER BY codigo,nome")->fetchAll();
    }

    public function naturezasDespesa(): array
    {
        return $this->db->query("SELECT * FROM naturezas_despesa WHERE ativo=1 ORDER BY codigo,nome")->fetchAll();
    }

    public function origensRecurso(): array
    {
        return $this->db->query("SELECT * FROM origens_recurso WHERE ativo=1 ORDER BY codigo,nome")->fetchAll();
    }

    public function statusInspecao(): array
    {
        return $this->db->query("SELECT * FROM status_inspecao WHERE ativo=1 ORDER BY ordem,nome")->fetchAll();
    }

    public function criarObrigacao(array $d): int
    {
        $tipo = (int)($d['tipo_obrigacao_id'] ?? 0);
        $fornecedor = (int)($d['fornecedor_id'] ?? 0);
        $numero = trim((string)($d['numero'] ?? ''));
        $ano = (int)($d['ano'] ?? 0);
        $valor = $this->decimal($d['valor_total'] ?? null);
        $fontes = $this->ids($d['fontes_recurso_ids'] ?? []);
        $naturezas = $this->ids($d['naturezas_despesa_ids'] ?? []);

        if ($tipo <= 0 || $fornecedor <= 0 || $numero === '' || $ano < 2000 || $ano > 2100) {
            throw new InvalidArgumentException('Informe tipo, fornecedor, número e ano válidos.');
        }
        if ($valor === null || $valor <= 0) throw new InvalidArgumentException('Informe o Valor Total da Obrigação.');
        if (!$fontes) throw new InvalidArgumentException('Informe ao menos uma Fonte de recurso.');
        if (!$naturezas) throw new InvalidArgumentException('Informe ao menos uma Natureza da despesa.');

        ObrigacaoRegra::validarNumero($tipo, $numero);
        $this->validarCadastroAtivo('tipos_obrigacao', $tipo, 'Tipo de obrigação');
        $this->validarCadastroAtivo('fornecedores', $fornecedor, 'Fornecedor');
        $this->validarCadastrosAtivos('fontes_recurso', $fontes, 'Fonte de recurso');
        $this->validarCadastrosAtivos('naturezas_despesa', $naturezas, 'Natureza da despesa');

        $dup = $this->db->prepare("SELECT id FROM obrigacoes WHERE fornecedor_id=? AND tipo_obrigacao_id=? AND numero=? AND ano=? LIMIT 1");
        $dup->execute([$fornecedor, $tipo, $numero, $ano]);
        if ($dup->fetchColumn()) {
            throw new RuntimeException('Já existe esta obrigação para o fornecedor informado.');
        }

        $inicio = ($d['data_inicio'] ?? '') ?: null;
        $fim = ($d['data_fim'] ?? '') ?: null;
        if ($inicio && $fim && $fim < $inicio) throw new InvalidArgumentException('A data final não pode ser anterior à data inicial.');

        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare("INSERT INTO obrigacoes(tipo_obrigacao_id,fornecedor_id,numero,ano,valor_total,nr_sei_contratacao,data_inicio,data_fim,criado_por)
              VALUES(?,?,?,?,?,?,?,?,?)");
            $st->execute([$tipo,$fornecedor,$numero,$ano,$valor,trim((string)($d['nr_sei_contratacao'] ?? '')) ?: null,$inicio,$fim,$this->usuarioId()]);
            $id = (int)$this->db->lastInsertId();
            $this->vincularIds('obrigacao_fontes_recurso', 'fonte_recurso_id', $id, $fontes);
            $this->vincularIds('obrigacao_naturezas_despesa', 'natureza_despesa_id', $id, $naturezas);
            $this->db->commit();
            return $id;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ((string)$e->getCode() === '23000') throw new RuntimeException('Não foi possível cadastrar a obrigação devido a uma restrição de integridade.');
            throw $e;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function documentos(): array
    {
        $sql = "SELECT d.*,td.nome tipo_documento,o.numero obrigacao_numero,o.ano obrigacao_ano,o.id obrigacao_id,
            f.id fornecedor_id,f.razao_social fornecedor,s.nome status_inspecao,s.permite_avancar
          FROM documentos_pagamento d
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          LEFT JOIN inspecoes i ON i.documento_id=d.id
          LEFT JOIN status_inspecao s ON s.id=i.status_id
          ORDER BY d.data_lancamento DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function criarDocumento(array $d): int
    {
        $fornecedor = (int)($d['fornecedor_id'] ?? 0);
        $obrigacao = (int)($d['obrigacao_id'] ?? 0);
        $tipo = (int)($d['tipo_documento_id'] ?? 0);
        $numero = trim((string)($d['numero'] ?? ''));
        $data = (string)($d['data_emissao'] ?? '');
        $bruto = $this->decimal($d['valor_bruto'] ?? null);
        $liquido = $this->decimal($d['valor_liquido'] ?? null);

        if ($fornecedor <= 0 || $obrigacao <= 0 || $tipo <= 0 || $numero === '' || $data === '' || $bruto === null || $bruto <= 0 || $liquido === null || $liquido <= 0) {
            throw new InvalidArgumentException('Preencha fornecedor, obrigação, tipo, número, data de emissão, valor bruto e valor líquido.');
        }
        if ($liquido > $bruto) throw new InvalidArgumentException('O valor líquido não pode ser maior que o valor bruto.');

        $q = $this->db->prepare("SELECT 1 FROM obrigacoes WHERE id=? AND fornecedor_id=? AND ativo=1");
        $q->execute([$obrigacao, $fornecedor]);
        if (!$q->fetchColumn()) throw new RuntimeException('A obrigação selecionada não pertence ao fornecedor informado.');
        $this->validarCadastroAtivo('tipos_documento_pagamento', $tipo, 'Tipo de documento');

        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare("INSERT INTO documentos_pagamento(obrigacao_id,tipo_documento_id,numero,data_emissao,data_atesto,data_envio_cooinsp,valor_bruto,valor_liquido,criado_por)
              VALUES(?,?,?,?,?,?,?,?,?)");
            $st->execute([$obrigacao,$tipo,$numero,$data,($d['data_atesto'] ?? '') ?: null,($d['data_envio_cooinsp'] ?? '') ?: null,$bruto,$liquido,$this->usuarioId()]);
            $id = (int)$this->db->lastInsertId();
            $status = (int)$this->db->query("SELECT id FROM status_inspecao WHERE nome='Aguardando inspeção' AND ativo=1 LIMIT 1")->fetchColumn();
            if ($status <= 0) throw new RuntimeException('Status inicial de inspeção não configurado.');
            $this->db->prepare("INSERT INTO inspecoes(documento_id,status_id) VALUES(?,?)")->execute([$id, $status]);
            $this->db->commit();
            return $id;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ((string)$e->getCode() === '23000') throw new RuntimeException('Já existe documento com este número para a obrigação e tipo informados, ou algum vínculo é inválido.');
            throw $e;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function documento(int $id): ?array
    {
        $sql = "SELECT d.*,td.nome tipo_documento,o.numero obrigacao_numero,o.ano obrigacao_ano,o.id obrigacao_id,
            o.valor_total valor_total_obrigacao,o.nr_sei_contratacao,
            f.id fornecedor_id,f.razao_social fornecedor,f.documento fornecedor_documento,f.tipo_pessoa fornecedor_tipo,
            i.id inspecao_id,i.status_id,i.data_conclusao,s.nome status_inspecao,s.permite_avancar
          FROM documentos_pagamento d
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          LEFT JOIN inspecoes i ON i.documento_id=d.id
          LEFT JOIN status_inspecao s ON s.id=i.status_id
          WHERE d.id=?";
        $st = $this->db->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function obrigacoesFornecedor(int $fornecedorId): array
    {
        $st = $this->db->prepare("SELECT o.id,o.numero,o.ano,o.valor_total,t.nome tipo
          FROM obrigacoes o JOIN tipos_obrigacao t ON t.id=o.tipo_obrigacao_id
          WHERE o.fornecedor_id=? AND o.ativo=1 ORDER BY o.ano DESC,o.numero");
        $st->execute([$fornecedorId]);
        return $st->fetchAll();
    }

    public function atualizarInspecao(int $documentoId, array $d): void
    {
        $status = (int)($d['status_id'] ?? 0);
        $st = $this->db->prepare("SELECT * FROM status_inspecao WHERE id=? AND ativo=1");
        $st->execute([$status]);
        $statusRow = $st->fetch();
        if (!$statusRow) throw new InvalidArgumentException('Status de inspeção inválido.');

        $dataConclusao = (int)$statusRow['encerra_inspecao'] === 1 ? (($d['data_conclusao'] ?? '') ?: date('Y-m-d')) : null;
        $observacao = mb_substr(trim((string)($d['observacao'] ?? '')), 0, 500) ?: null;
        $usuario = $this->usuarioId();

        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("UPDATE inspecoes SET status_id=?,data_conclusao=?,responsavel_id=?,atualizado_em=NOW() WHERE documento_id=?");
            $q->execute([$status, $dataConclusao, $usuario, $documentoId]);
            $exists = $this->db->prepare("SELECT id FROM inspecoes WHERE documento_id=?");
            $exists->execute([$documentoId]);
            $inspecaoId = (int)$exists->fetchColumn();
            if ($inspecaoId <= 0) throw new RuntimeException('Inspeção não encontrada.');
            $h = $this->db->prepare("INSERT INTO inspecao_historico(inspecao_id,status_id,observacao,usuario_id) VALUES(?,?,?,?)");
            $h->execute([$inspecaoId, $status, $observacao, $usuario]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function parcelas(int $documentoId): array
    {
        $sql = "SELECT p.*,nd.codigo natureza_codigo,nd.nome natureza_nome,fr.codigo fonte_codigo,fr.nome fonte_nome,
            ori.codigo origem_codigo,ori.nome origem_nome,l.status status_liquidacao,l.data_liquidacao,
            g.id cmdf_grupo_id,g.status status_cmdf,pg.status status_pagamento,pg.data_pagamento
          FROM parcelas_pagamento p
          JOIN naturezas_despesa nd ON nd.id=p.natureza_despesa_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id
          JOIN origens_recurso ori ON ori.id=p.origem_recurso_id
          LEFT JOIN liquidacoes l ON l.parcela_id=p.id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
          LEFT JOIN cmdf_grupos g ON g.id=gp.grupo_id
          LEFT JOIN pagamentos pg ON pg.parcela_id=p.id
          WHERE p.documento_id=? ORDER BY p.numero_parcela";
        $st = $this->db->prepare($sql);
        $st->execute([$documentoId]);
        return $st->fetchAll();
    }

    public function fontesDaObrigacao(int $obrigacaoId): array
    {
        $st = $this->db->prepare("SELECT fr.* FROM fontes_recurso fr JOIN obrigacao_fontes_recurso x ON x.fonte_recurso_id=fr.id
          WHERE x.obrigacao_id=? AND fr.ativo=1 ORDER BY fr.codigo");
        $st->execute([$obrigacaoId]);
        return $st->fetchAll();
    }

    public function naturezasDaObrigacao(int $obrigacaoId): array
    {
        $st = $this->db->prepare("SELECT nd.* FROM naturezas_despesa nd JOIN obrigacao_naturezas_despesa x ON x.natureza_despesa_id=nd.id
          WHERE x.obrigacao_id=? AND nd.ativo=1 ORDER BY nd.codigo");
        $st->execute([$obrigacaoId]);
        return $st->fetchAll();
    }

    public function adicionarParcela(int $documentoId, array $d): int
    {
        $doc = $this->documento($documentoId);
        if (!$doc) throw new RuntimeException('Documento não encontrado.');
        if (!(bool)$doc['permite_avancar']) throw new RuntimeException('A inspeção precisa estar Finalizada ou Liberada para programar parcelas.');

        $empenho = trim((string)($d['numero_empenho'] ?? ''));
        $natureza = (int)($d['natureza_despesa_id'] ?? 0);
        $exercicio = (int)($d['exercicio_orcamentario'] ?? 0);
        $fonte = (int)($d['fonte_recurso_id'] ?? 0);
        $origem = (int)($d['origem_recurso_id'] ?? 0);
        $valor = $this->decimal($d['valor_liquido'] ?? null);
        $tipo = strtoupper(trim((string)($d['tipo'] ?? '')));
        $dataVencimento = trim((string)($d['data_vencimento'] ?? ''));
        $ipof = preg_replace('/\D/', '', (string)($d['ipof'] ?? ''));
        $apBenner = preg_replace('/\D/', '', (string)($d['ap_benner'] ?? ''));
        $sequencial = preg_replace('/\D/', '', (string)($d['sequencial'] ?? ''));
        $grupoDespesa = preg_replace('/\D/', '', (string)($d['grupo_despesa'] ?? ''));
        $historico = trim((string)($d['historico_parcela'] ?? ''));
        $justificativa = mb_substr(trim((string)($d['justificativa_ordem_cronologica'] ?? '')), 0, 150) ?: null;
        $tipos = ['IMPOSTO','DARE','INSS','PIS','COFINS','IR','ISS'];

        if ($empenho === '' || $natureza <= 0 || $exercicio < 2000 || $exercicio > 2100 || $fonte <= 0 || $origem <= 0 || $valor === null || $valor <= 0 || !in_array($tipo, $tipos, true) || $dataVencimento === '' || $historico === '') {
            throw new InvalidArgumentException('Preencha todos os campos obrigatórios da parcela.');
        }
        if (!preg_match('/^\d{10}$/', $ipof)) throw new InvalidArgumentException('IPOF deve possuir exatamente 10 dígitos.');
        if (!preg_match('/^\d{10}$/', $apBenner)) throw new InvalidArgumentException('AP Benner deve possuir exatamente 10 dígitos.');
        if (!preg_match('/^\d{3}$/', $sequencial)) throw new InvalidArgumentException('Sequencial deve possuir exatamente 3 dígitos.');
        if (!preg_match('/^\d{2}$/', $grupoDespesa)) throw new InvalidArgumentException('Grupo de Despesa deve possuir exatamente 2 dígitos.');

        $this->validarVinculoObrigacao('obrigacao_naturezas_despesa', 'natureza_despesa_id', (int)$doc['obrigacao_id'], $natureza, 'Natureza da despesa');
        $this->validarVinculoObrigacao('obrigacao_fontes_recurso', 'fonte_recurso_id', (int)$doc['obrigacao_id'], $fonte, 'Fonte de recurso');
        $this->validarCadastroAtivo('origens_recurso', $origem, 'Origem do Recurso');

        $s = $this->db->prepare("SELECT COALESCE(SUM(valor_liquido),0) FROM parcelas_pagamento WHERE documento_id=?");
        $s->execute([$documentoId]);
        $programado = (float)$s->fetchColumn();
        if (round($programado + $valor, 2) > round((float)$doc['valor_liquido'], 2)) {
            throw new RuntimeException('A soma das parcelas não pode ultrapassar o valor líquido do documento.');
        }

        $n = $this->db->prepare("SELECT COALESCE(MAX(numero_parcela),0)+1 FROM parcelas_pagamento WHERE documento_id=?");
        $n->execute([$documentoId]);
        $numero = (int)$n->fetchColumn();

        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare("INSERT INTO parcelas_pagamento(documento_id,numero_parcela,numero_empenho,natureza_despesa_id,exercicio_orcamentario,fonte_recurso_id,origem_recurso_id,valor_liquido,tipo,data_vencimento,ipof,ap_benner,sequencial,grupo_despesa,historico_parcela,justificativa_ordem_cronologica,criado_por)
              VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $st->execute([$documentoId,$numero,$empenho,$natureza,$exercicio,$fonte,$origem,$valor,$tipo,$dataVencimento,$ipof,$apBenner,$sequencial,$grupoDespesa,mb_substr($historico,0,255),$justificativa,$this->usuarioId()]);
            $id = (int)$this->db->lastInsertId();
            $this->db->prepare("INSERT INTO liquidacoes(parcela_id) VALUES(?)")->execute([$id]);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function documentoProgramacaoFechada(int $documentoId): bool
    {
        $st = $this->db->prepare("SELECT d.valor_liquido,COALESCE(SUM(p.valor_liquido),0) soma
          FROM documentos_pagamento d LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id
          WHERE d.id=? GROUP BY d.id,d.valor_liquido");
        $st->execute([$documentoId]);
        $row = $st->fetch();
        return (bool)$row && round((float)$row['valor_liquido'], 2) === round((float)$row['soma'], 2);
    }

    public function parcela(int $parcelaId): ?array
    {
        $sql = "SELECT p.*,d.obrigacao_id,d.numero documento_numero,d.valor_liquido valor_liquido_documento,d.data_atesto,
            td.nome tipo_documento,f.razao_social fornecedor,nd.codigo natureza_codigo,nd.nome natureza_nome,
            fr.codigo fonte_codigo,fr.nome fonte_nome,ori.codigo origem_codigo,ori.nome origem_nome,
            l.status status_liquidacao,l.data_liquidacao,g.id cmdf_grupo_id,g.status status_cmdf,
            pg.status status_pagamento,pg.data_pagamento
          FROM parcelas_pagamento p
          JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN naturezas_despesa nd ON nd.id=p.natureza_despesa_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id
          JOIN origens_recurso ori ON ori.id=p.origem_recurso_id
          LEFT JOIN liquidacoes l ON l.parcela_id=p.id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
          LEFT JOIN cmdf_grupos g ON g.id=gp.grupo_id
          LEFT JOIN pagamentos pg ON pg.parcela_id=p.id
          WHERE p.id=?";
        $st = $this->db->prepare($sql);
        $st->execute([$parcelaId]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function atualizarLiquidacao(int $parcelaId, array $d): void
    {
        $parcela = $this->parcela($parcelaId);
        if (!$parcela) throw new RuntimeException('Parcela não encontrada.');

        $status = strtoupper(trim((string)($d['status'] ?? '')));
        $validos = ['AGUARDANDO','LIQUIDADA','CANCELADA','ANULADA'];
        if (!in_array($status, $validos, true)) throw new InvalidArgumentException('Status de liquidação inválido.');

        $data = ($d['data_liquidacao'] ?? '') ?: null;
        if ($status === 'LIQUIDADA') {
            if (!$this->documentoProgramacaoFechada((int)$parcela['documento_id'])) {
                throw new RuntimeException('A programação do documento precisa fechar exatamente o valor líquido antes da liquidação.');
            }
            if (!$data) throw new InvalidArgumentException('Informe a data de liquidação.');
        } else {
            $data = null;
            if (!empty($parcela['cmdf_grupo_id'])) {
                throw new RuntimeException('A parcela pertence a um grupo CMDF. Remova-a do grupo antes de alterar a liquidação.');
            }
        }

        $q = $this->db->prepare("UPDATE liquidacoes SET status=?,data_liquidacao=?,usuario_id=?,atualizado_em=NOW() WHERE parcela_id=?");
        $q->execute([$status,$data,$this->usuarioId(),$parcelaId]);
        if ($q->rowCount() === 0) {
            $exists = $this->db->prepare("SELECT id FROM liquidacoes WHERE parcela_id=?");
            $exists->execute([$parcelaId]);
            if (!$exists->fetchColumn()) throw new RuntimeException('Registro de liquidação não encontrado.');
        }
    }

    private function ids(mixed $value): array
    {
        $array = is_array($value) ? $value : [$value];
        return array_values(array_unique(array_filter(array_map('intval', $array), static fn(int $id): bool => $id > 0)));
    }

    private function vincularIds(string $tabela, string $coluna, int $obrigacaoId, array $ids): void
    {
        foreach ($ids as $id) {
            $st = $this->db->prepare("INSERT INTO {$tabela}(obrigacao_id,{$coluna}) VALUES(?,?)");
            $st->execute([$obrigacaoId, $id]);
        }
    }

    private function validarVinculoObrigacao(string $tabela, string $coluna, int $obrigacaoId, int $id, string $rotulo): void
    {
        $st = $this->db->prepare("SELECT 1 FROM {$tabela} WHERE obrigacao_id=? AND {$coluna}=?");
        $st->execute([$obrigacaoId, $id]);
        if (!$st->fetchColumn()) throw new RuntimeException($rotulo.' não está cadastrada nesta obrigação.');
    }

    private function validarCadastroAtivo(string $tabela, int $id, string $rotulo): void
    {
        $permitidas = ['fornecedores','tipos_documento_pagamento','tipos_obrigacao','origens_recurso'];
        if (!in_array($tabela, $permitidas, true)) throw new LogicException('Tabela de validação não permitida.');
        $st = $this->db->prepare("SELECT 1 FROM {$tabela} WHERE id=? AND ativo=1");
        $st->execute([$id]);
        if (!$st->fetchColumn()) throw new InvalidArgumentException($rotulo.' inválido ou inativo.');
    }

    private function validarCadastrosAtivos(string $tabela, array $ids, string $rotulo): void
    {
        $permitidas = ['fontes_recurso','naturezas_despesa'];
        if (!in_array($tabela, $permitidas, true)) throw new LogicException('Tabela de validação não permitida.');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->db->prepare("SELECT COUNT(*) FROM {$tabela} WHERE ativo=1 AND id IN ({$marks})");
        $st->execute($ids);
        if ((int)$st->fetchColumn() !== count($ids)) throw new InvalidArgumentException($rotulo.' inválida ou inativa.');
    }

    private function usuarioId(): ?int
    {
        return Auth::id();
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;
        $s = str_replace(['R$',' '], '', (string)$value);
        if (str_contains($s, ',')) $s = str_replace(['.', ','], ['', '.'], $s);
        return is_numeric($s) ? round((float)$s, 2) : null;
    }
}
