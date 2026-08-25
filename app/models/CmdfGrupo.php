<?php
declare(strict_types=1);

final class CmdfGrupo
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function grupos():array
    {
        $sql="SELECT g.id,g.status,g.gerado_automaticamente,g.exercicio_orcamentario,g.sequencial,g.grupo_despesa,
            fr.codigo fonte_codigo,fr.nome fonte_nome,ori.codigo origem_codigo,ori.nome origem_nome,
            COUNT(gp.parcela_id) parcelas_total,COALESCE(SUM(p.valor_liquido),0) valor_total,
            MIN(d.data_atesto) menor_data_atesto,MAX(d.data_atesto) maior_data_atesto,
            SUM(CASE WHEN pg.status='PAGO' THEN 1 ELSE 0 END) pagamentos_pagos,
            SUM(CASE WHEN pg.status IS NOT NULL AND pg.status<>'AGUARDANDO' THEN 1 ELSE 0 END) pagamentos_movimentados,
            g.criado_em,g.atualizado_em
          FROM cmdf_grupos g
          JOIN fontes_recurso fr ON fr.id=g.fonte_recurso_id
          JOIN origens_recurso ori ON ori.id=g.origem_recurso_id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.grupo_id=g.id
          LEFT JOIN parcelas_pagamento p ON p.id=gp.parcela_id
          LEFT JOIN documentos_pagamento d ON d.id=p.documento_id
          LEFT JOIN pagamentos pg ON pg.parcela_id=p.id
          GROUP BY g.id,g.status,g.gerado_automaticamente,g.exercicio_orcamentario,g.sequencial,g.grupo_despesa,fr.codigo,fr.nome,ori.codigo,ori.nome,g.criado_em,g.atualizado_em
          ORDER BY CASE g.status WHEN 'FECHADA' THEN 0 WHEN 'LIBERADA' THEN 1 ELSE 2 END,g.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function parcelasDisponiveis():array
    {
        return $this->db->query($this->sqlParcelasElegiveis()." ORDER BY fr.codigo,p.exercicio_orcamentario,p.sequencial,p.grupo_despesa,ori.codigo,d.data_atesto,p.id")->fetchAll();
    }

    public function sugestoes():array
    {
        $sql="SELECT p.fonte_recurso_id,p.exercicio_orcamentario,p.sequencial,p.grupo_despesa,p.origem_recurso_id,
            fr.codigo fonte_codigo,ori.codigo origem_codigo,COUNT(*) parcelas_total,SUM(p.valor_liquido) valor_total
          FROM liquidacoes l
          JOIN parcelas_pagamento p ON p.id=l.parcela_id
          JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id
          JOIN origens_recurso ori ON ori.id=p.origem_recurso_id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
          WHERE l.status='LIQUIDADA' AND d.data_atesto IS NOT NULL AND gp.parcela_id IS NULL
          GROUP BY p.fonte_recurso_id,p.exercicio_orcamentario,p.sequencial,p.grupo_despesa,p.origem_recurso_id,fr.codigo,ori.codigo
          ORDER BY fr.codigo,p.exercicio_orcamentario,p.sequencial,p.grupo_despesa,ori.codigo";
        return $this->db->query($sql)->fetchAll();
    }

    public function criarGruposSugeridos():int
    {
        $sugestoes=$this->sugestoes();$criados=0;
        foreach($sugestoes as $s){
            $st=$this->db->prepare("SELECT p.id
              FROM liquidacoes l JOIN parcelas_pagamento p ON p.id=l.parcela_id JOIN documentos_pagamento d ON d.id=p.documento_id
              LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
              WHERE l.status='LIQUIDADA' AND d.data_atesto IS NOT NULL AND gp.parcela_id IS NULL
                AND p.fonte_recurso_id=? AND p.exercicio_orcamentario=? AND p.sequencial=? AND p.grupo_despesa=? AND p.origem_recurso_id=? ORDER BY p.id");
            $st->execute([(int)$s['fonte_recurso_id'],(int)$s['exercicio_orcamentario'],(string)$s['sequencial'],(string)$s['grupo_despesa'],(int)$s['origem_recurso_id']]);
            $ids=array_map('intval',$st->fetchAll(PDO::FETCH_COLUMN));if($ids){$this->criarGrupo($ids,true);$criados++;}
        }
        return $criados;
    }

    public function criarGrupoManual(array $parcelaIds):int{return $this->criarGrupo($this->ids($parcelaIds),false);}

    public function grupo(int $grupoId):?array
    {
        $st=$this->db->prepare("SELECT g.*,fr.codigo fonte_codigo,fr.nome fonte_nome,ori.codigo origem_codigo,ori.nome origem_nome,
            u1.nome criado_por_nome,u2.nome atualizado_por_nome
          FROM cmdf_grupos g JOIN fontes_recurso fr ON fr.id=g.fonte_recurso_id JOIN origens_recurso ori ON ori.id=g.origem_recurso_id
          LEFT JOIN usuarios u1 ON u1.id=g.criado_por LEFT JOIN usuarios u2 ON u2.id=g.atualizado_por WHERE g.id=?");
        $st->execute([$grupoId]);$r=$st->fetch();return $r?:null;
    }

    public function parcelasDoGrupo(int $grupoId):array
    {
        $st=$this->db->prepare("SELECT p.id parcela_id,p.numero_parcela,p.numero_empenho,p.exercicio_orcamentario,p.valor_liquido,p.tipo,p.data_vencimento,p.ipof,p.ap_benner,p.sequencial,p.grupo_despesa,p.fonte_recurso_id,p.origem_recurso_id,
            d.id documento_id,d.numero documento_numero,d.data_atesto,td.nome tipo_documento,f.razao_social fornecedor,
            fr.codigo fonte_codigo,ori.codigo origem_codigo,l.data_liquidacao,pg.status status_pagamento
          FROM cmdf_grupo_parcelas gp JOIN parcelas_pagamento p ON p.id=gp.parcela_id JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id JOIN origens_recurso ori ON ori.id=p.origem_recurso_id JOIN liquidacoes l ON l.parcela_id=p.id
          LEFT JOIN pagamentos pg ON pg.parcela_id=p.id WHERE gp.grupo_id=? ORDER BY d.data_atesto,d.id,p.numero_parcela");
        $st->execute([$grupoId]);return $st->fetchAll();
    }

    public function candidatasCompativeis(int $grupoId):array
    {
        $g=$this->obterGrupo($grupoId);
        $sql=$this->sqlParcelasElegiveis()." AND p.fonte_recurso_id=? AND p.exercicio_orcamentario=? AND p.sequencial=? AND p.grupo_despesa=? AND p.origem_recurso_id=? ORDER BY d.data_atesto,p.id";
        $st=$this->db->prepare($sql);$st->execute([(int)$g['fonte_recurso_id'],(int)$g['exercicio_orcamentario'],(string)$g['sequencial'],(string)$g['grupo_despesa'],(int)$g['origem_recurso_id']]);return $st->fetchAll();
    }

    public function adicionarParcelas(int $grupoId,array $parcelaIds):void
    {
        $g=$this->obterGrupoEditavel($grupoId);$ids=$this->ids($parcelaIds);if(!$ids)throw new InvalidArgumentException('Selecione ao menos uma parcela para adicionar.');
        $this->db->beginTransaction();
        try{
            $ins=$this->db->prepare("INSERT INTO cmdf_grupo_parcelas(grupo_id,parcela_id,adicionado_por) VALUES(?,?,?)");
            foreach($ids as $id){$p=$this->parcelaElegivel($id,true);$this->validarChave($g,$p);$ins->execute([$grupoId,$id,Auth::id()]);}
            $this->db->prepare("UPDATE cmdf_grupos SET atualizado_por=?,atualizado_em=NOW() WHERE id=?")->execute([Auth::id(),$grupoId]);$this->db->commit();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    public function removerParcela(int $grupoId,int $parcelaId):void
    {
        $this->obterGrupoEditavel($grupoId);$this->db->beginTransaction();
        try{
            $del=$this->db->prepare("DELETE FROM cmdf_grupo_parcelas WHERE grupo_id=? AND parcela_id=?");$del->execute([$grupoId,$parcelaId]);
            if($del->rowCount()!==1)throw new RuntimeException('Parcela não pertence a este grupo.');
            $st=$this->db->prepare("SELECT COUNT(*) FROM cmdf_grupo_parcelas WHERE grupo_id=?");$st->execute([$grupoId]);
            if((int)$st->fetchColumn()===0){$this->db->prepare("DELETE FROM cmdf_grupos WHERE id=? AND status='FECHADA'")->execute([$grupoId]);}
            else{$this->db->prepare("UPDATE cmdf_grupos SET atualizado_por=?,atualizado_em=NOW() WHERE id=?")->execute([Auth::id(),$grupoId]);}
            $this->db->commit();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    public function atualizarStatus(int $grupoId,string $novoStatus):void
    {
        $novoStatus=strtoupper(trim($novoStatus));if(!in_array($novoStatus,['FECHADA','LIBERADA','ATENDIDA'],true))throw new InvalidArgumentException('Status da CMDF inválido.');
        $g=$this->obterGrupo($grupoId);$atual=(string)$g['status'];if($atual===$novoStatus)return;
        $transicoes=['FECHADA'=>'LIBERADA','LIBERADA'=>'ATENDIDA'];if(($transicoes[$atual]??null)!==$novoStatus)throw new RuntimeException('A CMDF deve seguir a sequência Fechada → Liberada → Atendida.');
        $parcelas=$this->parcelasDoGrupo($grupoId);if(!$parcelas)throw new RuntimeException('O grupo CMDF precisa possuir ao menos uma parcela.');
        foreach($parcelas as $p){if(empty($p['data_atesto']))throw new RuntimeException('Todas as parcelas do grupo precisam possuir Data do atesto.');$this->validarChave($g,$p);}
        $this->db->beginTransaction();
        try{
            $this->db->prepare("UPDATE cmdf_grupos SET status=?,atualizado_por=?,atualizado_em=NOW() WHERE id=?")->execute([$novoStatus,Auth::id(),$grupoId]);
            if($novoStatus==='ATENDIDA'){
                $ins=$this->db->prepare("INSERT INTO pagamentos(parcela_id,status) VALUES(?,'AGUARDANDO') ON DUPLICATE KEY UPDATE status=IF(status='PAGO','PAGO','AGUARDANDO'),atualizado_em=NOW()");
                foreach($parcelas as $p)$ins->execute([(int)$p['parcela_id']]);
            }
            $this->db->commit();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    private function criarGrupo(array $ids,bool $automatico):int
    {
        if(!$ids)throw new InvalidArgumentException('Selecione ao menos uma parcela para formar o grupo CMDF.');
        $parcelas=[];foreach($ids as $id)$parcelas[]=$this->parcelaElegivel($id,true);$base=$parcelas[0];foreach($parcelas as $p)$this->validarChave($base,$p);
        $this->db->beginTransaction();
        try{
            $st=$this->db->prepare("INSERT INTO cmdf_grupos(fonte_recurso_id,exercicio_orcamentario,sequencial,grupo_despesa,origem_recurso_id,status,gerado_automaticamente,criado_por) VALUES(?,?,?,?,?,'FECHADA',?,?)");
            $st->execute([(int)$base['fonte_recurso_id'],(int)$base['exercicio_orcamentario'],(string)$base['sequencial'],(string)$base['grupo_despesa'],(int)$base['origem_recurso_id'],$automatico?1:0,Auth::id()]);
            $grupoId=(int)$this->db->lastInsertId();$ins=$this->db->prepare("INSERT INTO cmdf_grupo_parcelas(grupo_id,parcela_id,adicionado_por) VALUES(?,?,?)");foreach($ids as $id)$ins->execute([$grupoId,$id,Auth::id()]);
            $this->db->commit();return $grupoId;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    private function parcelaElegivel(int $parcelaId,bool $exigirSemGrupo):array
    {
        $sql="SELECT p.*,d.data_atesto,l.status status_liquidacao,gp.grupo_id FROM parcelas_pagamento p JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN liquidacoes l ON l.parcela_id=p.id LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id WHERE p.id=?";
        $st=$this->db->prepare($sql);$st->execute([$parcelaId]);$p=$st->fetch();
        if(!$p)throw new RuntimeException('Parcela não encontrada.');
        if((string)$p['status_liquidacao']!=='LIQUIDADA')throw new RuntimeException('Somente parcelas liquidadas podem compor grupo CMDF.');
        if(empty($p['data_atesto']))throw new RuntimeException('A parcela só pode entrar na CMDF quando o documento possuir Data do atesto.');
        if($exigirSemGrupo&&!empty($p['grupo_id']))throw new RuntimeException('A parcela já pertence a outro grupo CMDF.');
        return $p;
    }

    private function validarChave(array $base,array $p):void
    {
        foreach(['fonte_recurso_id','exercicio_orcamentario','sequencial','grupo_despesa','origem_recurso_id'] as $campo){
            if((string)$base[$campo]!==(string)$p[$campo])throw new RuntimeException('As parcelas do grupo precisam possuir a mesma Fonte de recurso, Exercício orçamentário, Sequencial, Grupo de Despesa e Origem do Recurso.');
        }
    }

    private function obterGrupo(int $grupoId):array
    {
        $st=$this->db->prepare("SELECT * FROM cmdf_grupos WHERE id=?");$st->execute([$grupoId]);$g=$st->fetch();if(!$g)throw new RuntimeException('Grupo CMDF não encontrado.');return $g;
    }

    private function obterGrupoEditavel(int $grupoId):array
    {
        $g=$this->obterGrupo($grupoId);if((string)$g['status']!=='FECHADA')throw new RuntimeException('Só é permitido adicionar ou remover parcelas enquanto o grupo CMDF estiver Fechada.');return $g;
    }

    private function ids(mixed $value):array
    {
        $array=is_array($value)?$value:[$value];return array_values(array_unique(array_filter(array_map('intval',$array),static fn(int $id):bool=>$id>0)));
    }

    private function sqlParcelasElegiveis():string
    {
        return "SELECT p.id parcela_id,p.numero_parcela,p.numero_empenho,p.exercicio_orcamentario,p.valor_liquido,p.tipo,p.data_vencimento,p.ipof,p.ap_benner,p.sequencial,p.grupo_despesa,p.fonte_recurso_id,p.origem_recurso_id,
            d.id documento_id,d.numero documento_numero,d.data_atesto,td.nome tipo_documento,f.razao_social fornecedor,
            fr.codigo fonte_codigo,fr.nome fonte_nome,ori.codigo origem_codigo,ori.nome origem_nome,l.data_liquidacao
          FROM liquidacoes l JOIN parcelas_pagamento p ON p.id=l.parcela_id JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id JOIN origens_recurso ori ON ori.id=p.origem_recurso_id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
          WHERE l.status='LIQUIDADA' AND d.data_atesto IS NOT NULL AND gp.parcela_id IS NULL";
    }
}
