<?php
declare(strict_types=1);

final class FluxoPagamento
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function dashboard():array{
        $sql="SELECT
          (SELECT COUNT(*) FROM obrigacoes WHERE ativo=1) obrigacoes,
          (SELECT COUNT(*) FROM documentos_pagamento) documentos,
          (SELECT COUNT(*) FROM inspecoes i JOIN status_inspecao s ON s.id=i.status_id WHERE s.encerra_inspecao=0) em_inspecao,
          (SELECT COUNT(*) FROM liquidacoes WHERE status='AGUARDANDO') aguardando_liquidacao,
          (SELECT COUNT(*) FROM cmdf_etapas WHERE status='AGUARDANDO') aguardando_cmdf,
          (SELECT COUNT(*) FROM pagamentos WHERE status='PAGO') pagos";
        return $this->db->query($sql)->fetch()?:[];
    }

    public function obrigacoes():array{
        $sql="SELECT o.*,t.nome tipo,f.razao_social fornecedor,
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

    public function obrigacao(int $id):?array{
        $st=$this->db->prepare("SELECT o.*,f.razao_social fornecedor,f.documento fornecedor_documento,t.nome tipo
          FROM obrigacoes o JOIN fornecedores f ON f.id=o.fornecedor_id JOIN tipos_obrigacao t ON t.id=o.tipo_obrigacao_id
          WHERE o.id=?");
        $st->execute([$id]);$r=$st->fetch();return $r?:null;
    }

    public function fornecedores():array{return $this->db->query("SELECT * FROM fornecedores WHERE ativo=1 ORDER BY razao_social")->fetchAll();}
    public function tiposObrigacao():array{return $this->db->query("SELECT * FROM tipos_obrigacao WHERE ativo=1 ORDER BY nome")->fetchAll();}
    public function tiposDocumento():array{return $this->db->query("SELECT * FROM tipos_documento_pagamento WHERE ativo=1 ORDER BY nome")->fetchAll();}
    public function fontesRecurso():array{return $this->db->query("SELECT * FROM fontes_recurso WHERE ativo=1 ORDER BY codigo,nome")->fetchAll();}
    public function naturezasDespesa():array{return $this->db->query("SELECT * FROM naturezas_despesa WHERE ativo=1 ORDER BY codigo,nome")->fetchAll();}
    public function tiposRecurso():array{return $this->db->query("SELECT * FROM tipos_recurso WHERE ativo=1 ORDER BY codigo,nome")->fetchAll();}
    public function statusInspecao():array{return $this->db->query("SELECT * FROM status_inspecao WHERE ativo=1 ORDER BY ordem,nome")->fetchAll();}

    public function criarObrigacao(array $d):int{
        $tipo=(int)($d['tipo_obrigacao_id']??0);
        $fornecedor=(int)($d['fornecedor_id']??0);
        $numero=trim((string)($d['numero']??''));
        $ano=(int)($d['ano']??0);
        $fontes=$this->ids($d['fontes_recurso_ids']??[]);
        $naturezas=$this->ids($d['naturezas_despesa_ids']??[]);
        if(!$tipo||!$fornecedor||$numero===''||$ano<2000||$ano>2100)throw new InvalidArgumentException('Informe tipo, fornecedor, número e ano válidos.');
        if(!$fontes)throw new InvalidArgumentException('Informe ao menos uma Fonte de recurso.');
        if(!$naturezas)throw new InvalidArgumentException('Informe ao menos uma Natureza da despesa.');
        ObrigacaoRegra::validarNumero($tipo,$numero);
        $valor=$this->decimal($d['valor_total']??null);

        $this->db->beginTransaction();
        try{
            $st=$this->db->prepare("INSERT INTO obrigacoes(tipo_obrigacao_id,fornecedor_id,numero,ano,valor_total,nr_sei_contratacao,data_inicio,data_fim,criado_por)
              VALUES(?,?,?,?,?,?,?,?,?)");
            $st->execute([
                $tipo,$fornecedor,$numero,$ano,$valor,
                trim((string)($d['nr_sei_contratacao']??''))?:null,
                ($d['data_inicio']??'')?:null,($d['data_fim']??'')?:null,
                (int)(Auth::user()['id']??0)?:null
            ]);
            $id=(int)$this->db->lastInsertId();
            $this->vincularIds('obrigacao_fontes_recurso','fonte_recurso_id',$id,$fontes);
            $this->vincularIds('obrigacao_naturezas_despesa','natureza_despesa_id',$id,$naturezas);
            $this->db->commit();
            return $id;
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function documentos():array{
        $sql="SELECT d.*,td.nome tipo_documento,o.numero obrigacao_numero,o.ano obrigacao_ano,o.id obrigacao_id,
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

    public function criarDocumento(array $d):int{
        $fornecedor=(int)($d['fornecedor_id']??0);
        $ob=(int)($d['obrigacao_id']??0);
        $tipo=(int)($d['tipo_documento_id']??0);
        $numero=trim((string)($d['numero']??''));
        $data=(string)($d['data_emissao']??'');
        $bruto=$this->decimal($d['valor_bruto']??null);
        $liquido=$this->decimal($d['valor_liquido']??null);
        if(!$fornecedor||!$ob||!$tipo||$numero===''||$data===''||$bruto===null||$bruto<=0||$liquido===null||$liquido<0)
            throw new InvalidArgumentException('Preencha fornecedor, obrigação, tipo, número, data de emissão, valor bruto e valor líquido.');
        if($liquido>$bruto)throw new InvalidArgumentException('O valor líquido não pode ser maior que o valor bruto.');

        $q=$this->db->prepare("SELECT 1 FROM obrigacoes WHERE id=? AND fornecedor_id=? AND ativo=1");
        $q->execute([$ob,$fornecedor]);
        if(!$q->fetchColumn())throw new RuntimeException('A obrigação selecionada não pertence ao fornecedor informado.');

        $this->db->beginTransaction();
        try{
            $st=$this->db->prepare("INSERT INTO documentos_pagamento(obrigacao_id,tipo_documento_id,numero,data_emissao,data_atesto,data_envio_cooinsp,valor_bruto,valor_liquido,criado_por)
              VALUES(?,?,?,?,?,?,?,?,?)");
            $st->execute([
                $ob,$tipo,$numero,$data,
                ($d['data_atesto']??'')?:null,($d['data_envio_cooinsp']??'')?:null,
                $bruto,$liquido,(int)(Auth::user()['id']??0)?:null
            ]);
            $id=(int)$this->db->lastInsertId();
            $status=(int)$this->db->query("SELECT id FROM status_inspecao WHERE nome='Aguardando inspeção' LIMIT 1")->fetchColumn();
            $this->db->prepare("INSERT INTO inspecoes(documento_id,status_id) VALUES(?,?)")->execute([$id,$status]);
            $this->db->commit();return $id;
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function documento(int $id):?array{
        $sql="SELECT d.*,td.nome tipo_documento,o.numero obrigacao_numero,o.ano obrigacao_ano,o.valor_total valor_total_obrigacao,
            f.id fornecedor_id,f.razao_social fornecedor,f.documento fornecedor_documento,
            i.id inspecao_id,i.status_id,i.data_conclusao,s.nome status_inspecao,s.permite_avancar
          FROM documentos_pagamento d
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          LEFT JOIN inspecoes i ON i.documento_id=d.id
          LEFT JOIN status_inspecao s ON s.id=i.status_id
          WHERE d.id=?";
        $st=$this->db->prepare($sql);$st->execute([$id]);$r=$st->fetch();return $r?:null;
    }

    public function obrigacoesFornecedor(int $fornecedorId):array{
        $st=$this->db->prepare("SELECT o.id,o.numero,o.ano,t.nome tipo FROM obrigacoes o JOIN tipos_obrigacao t ON t.id=o.tipo_obrigacao_id WHERE o.fornecedor_id=? AND o.ativo=1 ORDER BY o.ano DESC,o.numero");
        $st->execute([$fornecedorId]);return $st->fetchAll();
    }

    public function atualizarInspecao(int $documentoId,array $d):void{
        $status=(int)($d['status_id']??0);
        $st=$this->db->prepare("SELECT * FROM status_inspecao WHERE id=? AND ativo=1");$st->execute([$status]);$row=$st->fetch();
        if(!$row)throw new InvalidArgumentException('Status de inspeção inválido.');
        $data=(int)$row['encerra_inspecao']===1?date('Y-m-d'):null;
        $usuario=(int)(Auth::user()['id']??0)?:null;
        $this->db->beginTransaction();
        try{
            $q=$this->db->prepare("UPDATE inspecoes SET status_id=?,data_conclusao=?,responsavel_id=?,atualizado_em=NOW() WHERE documento_id=?");
            $q->execute([$status,$data,$usuario,$documentoId]);
            if($q->rowCount()===0){
                $exists=$this->db->prepare("SELECT id FROM inspecoes WHERE documento_id=?");$exists->execute([$documentoId]);
                if(!$exists->fetchColumn())throw new RuntimeException('Inspeção não encontrada.');
            }
            $h=$this->db->prepare("INSERT INTO inspecao_historico(inspecao_id,status_id,usuario_id) SELECT id,?,? FROM inspecoes WHERE documento_id=?");
            $h->execute([$status,$usuario,$documentoId]);
            $this->db->commit();
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function parcelas(int $documentoId):array{
        $sql="SELECT p.*,nd.codigo natureza_codigo,nd.nome natureza_nome,fr.codigo fonte_codigo,fr.nome fonte_nome,tr.codigo tipo_recurso_codigo,
            l.status status_liquidacao,l.data_liquidacao,c.status status_cmdf,c.data_conclusao data_cmdf,pg.status status_pagamento,pg.data_pagamento
          FROM parcelas_pagamento p
          JOIN naturezas_despesa nd ON nd.id=p.natureza_despesa_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id
          JOIN tipos_recurso tr ON tr.id=p.tipo_recurso_id
          LEFT JOIN liquidacoes l ON l.parcela_id=p.id
          LEFT JOIN cmdf_etapas c ON c.parcela_id=p.id
          LEFT JOIN pagamentos pg ON pg.parcela_id=p.id
          WHERE p.documento_id=?
          ORDER BY p.numero_parcela";
        $st=$this->db->prepare($sql);$st->execute([$documentoId]);return $st->fetchAll();
    }

    public function fontesDaObrigacao(int $obrigacaoId):array{
        $st=$this->db->prepare("SELECT fr.* FROM fontes_recurso fr JOIN obrigacao_fontes_recurso x ON x.fonte_recurso_id=fr.id WHERE x.obrigacao_id=? ORDER BY fr.codigo");
        $st->execute([$obrigacaoId]);return $st->fetchAll();
    }
    public function naturezasDaObrigacao(int $obrigacaoId):array{
        $st=$this->db->prepare("SELECT nd.* FROM naturezas_despesa nd JOIN obrigacao_naturezas_despesa x ON x.natureza_despesa_id=nd.id WHERE x.obrigacao_id=? ORDER BY nd.codigo");
        $st->execute([$obrigacaoId]);return $st->fetchAll();
    }

    public function adicionarParcela(int $documentoId,array $d):int{
        $doc=$this->documento($documentoId);
        if(!$doc)throw new RuntimeException('Documento não encontrado.');
        if(!(bool)$doc['permite_avancar'])throw new RuntimeException('A inspeção precisa estar em "Liberada liquidação de imposto" para programar parcelas.');

        $empenho=trim((string)($d['numero_empenho']??''));
        $natureza=(int)($d['natureza_despesa_id']??0);
        $exercicio=(int)($d['exercicio_orcamentario']??0);
        $fonte=(int)($d['fonte_recurso_id']??0);
        $tipoRecurso=(int)($d['tipo_recurso_id']??0);
        $valor=$this->decimal($d['valor_liquido']??null);
        $tipo=(string)($d['tipo']??'');
        $tipos=['IMPOSTO','DARE','INSS','PIS','COFINS','IR','ISS'];

        if($empenho===''||!$natureza||$exercicio<2000||$exercicio>2100||!$fonte||!$tipoRecurso||$valor===null||$valor<=0||!in_array($tipo,$tipos,true))
            throw new InvalidArgumentException('Preencha empenho, natureza, exercício, fonte, tipo de recurso, valor líquido e tipo da parcela.');

        $this->validarVinculoObrigacao('obrigacao_naturezas_despesa','natureza_despesa_id',(int)$doc['obrigacao_id'],$natureza,'Natureza da despesa');
        $this->validarVinculoObrigacao('obrigacao_fontes_recurso','fonte_recurso_id',(int)$doc['obrigacao_id'],$fonte,'Fonte de recurso');

        $s=$this->db->prepare("SELECT COALESCE(SUM(valor_liquido),0) FROM parcelas_pagamento WHERE documento_id=?");$s->execute([$documentoId]);
        $ja=(float)$s->fetchColumn();
        if(round($ja+$valor,2)>round((float)$doc['valor_liquido'],2))throw new RuntimeException('A soma das parcelas não pode ultrapassar o valor líquido do documento.');

        $n=$this->db->prepare("SELECT COALESCE(MAX(numero_parcela),0)+1 FROM parcelas_pagamento WHERE documento_id=?");$n->execute([$documentoId]);
        $numero=(int)$n->fetchColumn();
        $usuario=(int)(Auth::user()['id']??0)?:null;

        $this->db->beginTransaction();
        try{
            $st=$this->db->prepare("INSERT INTO parcelas_pagamento(documento_id,numero_parcela,numero_empenho,natureza_despesa_id,exercicio_orcamentario,fonte_recurso_id,tipo_recurso_id,valor_liquido,tipo,data_vencimento,historico_parcela,justificativa_ordem_cronologica,criado_por)
              VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $st->execute([
                $documentoId,$numero,$empenho,$natureza,$exercicio,$fonte,$tipoRecurso,$valor,$tipo,
                ($d['data_vencimento']??'')?:null,
                mb_substr(trim((string)($d['historico_parcela']??'')),0,255),
                mb_substr(trim((string)($d['justificativa_ordem_cronologica']??'')),0,150),
                $usuario
            ]);
            $id=(int)$this->db->lastInsertId();
            $this->db->prepare("INSERT INTO liquidacoes(parcela_id) VALUES(?)")->execute([$id]);
            $this->db->commit();return $id;
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function documentoProgramacaoFechada(int $documentoId):bool{
        $st=$this->db->prepare("SELECT d.valor_liquido,COALESCE(SUM(p.valor_liquido),0) soma FROM documentos_pagamento d LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id WHERE d.id=? GROUP BY d.id");
        $st->execute([$documentoId]);$r=$st->fetch();
        return $r&&round((float)$r['valor_liquido'],2)===round((float)$r['soma'],2);
    }

    public function parcela(int $parcelaId):?array{
        $sql="SELECT p.*,d.obrigacao_id,d.numero documento_numero,d.valor_liquido valor_liquido_documento,td.nome tipo_documento,
            f.razao_social fornecedor,nd.codigo natureza_codigo,nd.nome natureza_nome,fr.codigo fonte_codigo,fr.nome fonte_nome,tr.codigo tipo_recurso_codigo,
            l.status status_liquidacao,l.data_liquidacao,c.status status_cmdf,c.data_conclusao data_cmdf
          FROM parcelas_pagamento p
          JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN naturezas_despesa nd ON nd.id=p.natureza_despesa_id
          JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id
          JOIN tipos_recurso tr ON tr.id=p.tipo_recurso_id
          LEFT JOIN liquidacoes l ON l.parcela_id=p.id
          LEFT JOIN cmdf_etapas c ON c.parcela_id=p.id
          WHERE p.id=?";
        $st=$this->db->prepare($sql);$st->execute([$parcelaId]);$r=$st->fetch();return $r?:null;
    }

    public function atualizarLiquidacao(int $parcelaId,array $d):void{
        $parcela=$this->parcela($parcelaId);
        if(!$parcela)throw new RuntimeException('Parcela não encontrada.');
        $status=(string)($d['status']??'');
        $validos=['AGUARDANDO','LIQUIDADA','CANCELADA','ANULADA'];
        if(!in_array($status,$validos,true))throw new InvalidArgumentException('Status de liquidação inválido.');
        $data=($d['data_liquidacao']??'')?:null;
        if($status==='LIQUIDADA'){
            if(!$this->documentoProgramacaoFechada((int)$parcela['documento_id']))throw new RuntimeException('A programação do documento precisa fechar exatamente o valor líquido antes da liquidação.');
            if(!$data)throw new InvalidArgumentException('Informe a data de liquidação.');
        }else{$data=null;}

        $usuario=(int)(Auth::user()['id']??0)?:null;
        $this->db->beginTransaction();
        try{
            if($status!=='LIQUIDADA'){
                $down=$this->db->prepare("SELECT c.status cmdf_status,pg.status pagamento_status FROM cmdf_etapas c LEFT JOIN pagamentos pg ON pg.parcela_id=c.parcela_id WHERE c.parcela_id=?");
                $down->execute([$parcelaId]);$downstream=$down->fetch();
                if($downstream&&(($downstream['cmdf_status']??'')==='LIQUIDADA'||($downstream['pagamento_status']??'')==='PAGO')){
                    throw new RuntimeException('Não é possível alterar a liquidação porque a parcela já avançou no fluxo.');
                }
                $this->db->prepare("DELETE FROM pagamentos WHERE parcela_id=? AND status='AGUARDANDO'")->execute([$parcelaId]);
                $this->db->prepare("DELETE FROM cmdf_etapas WHERE parcela_id=? AND status='AGUARDANDO'")->execute([$parcelaId]);
            }
            $q=$this->db->prepare("UPDATE liquidacoes SET status=?,data_liquidacao=?,usuario_id=?,atualizado_em=NOW() WHERE parcela_id=?");
            $q->execute([$status,$data,$usuario,$parcelaId]);
            if($status==='LIQUIDADA'){
                $this->db->prepare("INSERT INTO cmdf_etapas(parcela_id,status) VALUES(?,'AGUARDANDO') ON DUPLICATE KEY UPDATE status=IF(status='LIQUIDADA','LIQUIDADA','AGUARDANDO'),atualizado_em=NOW()")->execute([$parcelaId]);
            }
            $this->db->commit();
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function atualizarCmdf(int $parcelaId,array $d):void{
        $status=(string)($d['status']??'');
        $validos=['AGUARDANDO','LIQUIDADA','CANCELADA','ANULADA'];
        if(!in_array($status,$validos,true))throw new InvalidArgumentException('Status de CMDF inválido.');

        $st=$this->db->prepare("SELECT status FROM liquidacoes WHERE parcela_id=?");$st->execute([$parcelaId]);
        if((string)$st->fetchColumn()!=='LIQUIDADA')throw new RuntimeException('Somente parcela liquidada pode seguir para CMDF.');

        $data=$status==='LIQUIDADA'?(($d['data_conclusao']??'')?:date('Y-m-d')):null;
        $usuario=(int)(Auth::user()['id']??0)?:null;
        $this->db->beginTransaction();
        try{
            if($status!=='LIQUIDADA'){
                $pg=$this->db->prepare("SELECT status FROM pagamentos WHERE parcela_id=?");$pg->execute([$parcelaId]);$pgStatus=$pg->fetchColumn();
                if($pgStatus==='PAGO')throw new RuntimeException('Não é possível alterar a CMDF porque a parcela já foi paga.');
                $this->db->prepare("DELETE FROM pagamentos WHERE parcela_id=? AND status='AGUARDANDO'")->execute([$parcelaId]);
            }
            $q=$this->db->prepare("UPDATE cmdf_etapas SET status=?,data_conclusao=?,usuario_id=?,atualizado_em=NOW() WHERE parcela_id=?");
            $q->execute([$status,$data,$usuario,$parcelaId]);
            if($q->rowCount()===0){
                $this->db->prepare("INSERT INTO cmdf_etapas(parcela_id,status,data_conclusao,usuario_id) VALUES(?,?,?,?)")->execute([$parcelaId,$status,$data,$usuario]);
            }
            if($status==='LIQUIDADA'){
                $this->db->prepare("INSERT INTO pagamentos(parcela_id,status) VALUES(?,'AGUARDANDO') ON DUPLICATE KEY UPDATE status=IF(status='PAGO','PAGO','AGUARDANDO'),atualizado_em=NOW()")->execute([$parcelaId]);
            }
            $this->db->commit();
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    private function ids(mixed $v):array{
        $arr=is_array($v)?$v:[$v];
        return array_values(array_unique(array_filter(array_map('intval',$arr),fn($id)=>$id>0)));
    }
    private function vincularIds(string $tabela,string $coluna,int $obrigacaoId,array $ids):void{
        foreach($ids as $id){
            $st=$this->db->prepare("INSERT INTO {$tabela}(obrigacao_id,{$coluna}) VALUES(?,?)");
            $st->execute([$obrigacaoId,$id]);
        }
    }
    private function validarVinculoObrigacao(string $tabela,string $coluna,int $obrigacaoId,int $id,string $rotulo):void{
        $st=$this->db->prepare("SELECT 1 FROM {$tabela} WHERE obrigacao_id=? AND {$coluna}=?");
        $st->execute([$obrigacaoId,$id]);
        if(!$st->fetchColumn())throw new RuntimeException($rotulo.' não está cadastrada nesta obrigação.');
    }
    private function decimal(mixed $v):?float{
        if($v===null||$v==='')return null;
        $s=str_replace(['R$',' '],'',(string)$v);
        if(str_contains($s,','))$s=str_replace(['.',','],['','.'],$s);
        return is_numeric($s)?round((float)$s,2):null;
    }
}
