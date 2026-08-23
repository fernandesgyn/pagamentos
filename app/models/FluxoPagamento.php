<?php
declare(strict_types=1);
final class FluxoPagamento{
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
    return $this->db->query("SELECT o.*,t.nome tipo,f.nome fornecedor FROM obrigacoes o JOIN tipos_obrigacao t ON t.id=o.tipo_obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id ORDER BY o.ano DESC,CAST(o.numero AS UNSIGNED) DESC,o.numero DESC")->fetchAll();
  }
  public function fornecedores():array{return $this->db->query("SELECT * FROM fornecedores WHERE ativo=1 ORDER BY nome")->fetchAll();}
  public function tiposObrigacao():array{return $this->db->query("SELECT * FROM tipos_obrigacao WHERE ativo=1 ORDER BY id")->fetchAll();}
  public function tiposDocumento():array{return $this->db->query("SELECT * FROM tipos_documento_pagamento WHERE ativo=1 ORDER BY nome")->fetchAll();}
  public function statusInspecao():array{return $this->db->query("SELECT * FROM status_inspecao WHERE ativo=1 ORDER BY ordem,nome")->fetchAll();}
  public function componentes():array{return $this->db->query("SELECT * FROM tipos_componente_pagamento WHERE ativo=1 ORDER BY ordem,nome")->fetchAll();}
  public function empenhos():array{return $this->db->query("SELECT * FROM empenhos_pagamento WHERE ativo=1 ORDER BY ano DESC,numero")->fetchAll();}

  public function criarObrigacao(array $d):int{
    $numero=trim((string)($d['numero']??''));$ano=(int)($d['ano']??0);$tipo=(int)($d['tipo_obrigacao_id']??0);$fornecedor=(int)($d['fornecedor_id']??0);
    if(!$tipo||!$fornecedor||$numero===''||$ano<2000||$ano>2100)throw new InvalidArgumentException('Informe tipo, fornecedor, número e ano válidos.');
    $st=$this->db->prepare("INSERT INTO obrigacoes(tipo_obrigacao_id,fornecedor_id,numero,ano,objeto,valor_global,sei) VALUES(?,?,?,?,?,?,?)");
    $st->execute([$tipo,$fornecedor,$numero,$ano,trim((string)($d['objeto']??'')),$this->decimal($d['valor_global']??null),trim((string)($d['sei']??''))?:null]);
    return (int)$this->db->lastInsertId();
  }

  public function documentos():array{
    return $this->db->query("SELECT d.*,td.nome tipo_documento,o.numero obrigacao_numero,o.ano obrigacao_ano,f.nome fornecedor,s.nome status_inspecao,s.permite_avancar FROM documentos_pagamento d JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id LEFT JOIN inspecoes i ON i.documento_id=d.id LEFT JOIN status_inspecao s ON s.id=i.status_id ORDER BY d.data_lancamento DESC")->fetchAll();
  }

  public function criarDocumento(array $d):int{
    $ob=(int)($d['obrigacao_id']??0);$tipo=(int)($d['tipo_documento_id']??0);$numero=trim((string)($d['numero']??''));$data=(string)($d['data_documento']??'');$valor=$this->decimal($d['valor_bruto']??null);
    if(!$ob||!$tipo||$numero===''||$data===''||$valor===null||$valor<=0)throw new InvalidArgumentException('Preencha obrigação, tipo, número, data e valor.');
    $this->db->beginTransaction();
    try{
      $st=$this->db->prepare("INSERT INTO documentos_pagamento(obrigacao_id,tipo_documento_id,numero,data_documento,valor_bruto,data_maxima_liquidacao,limite_anotacao,data_atesto,tipo_servico,sei_pagamento,observacoes) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
      $st->execute([$ob,$tipo,$numero,$data,$valor,$d['data_maxima_liquidacao']?:null,$d['limite_anotacao']?:null,$d['data_atesto']?:null,trim((string)($d['tipo_servico']??''))?:null,trim((string)($d['sei_pagamento']??''))?:null,trim((string)($d['observacoes']??''))?:null]);
      $id=(int)$this->db->lastInsertId();
      $status=(int)$this->db->query("SELECT id FROM status_inspecao WHERE nome='Aguardando inspeção' LIMIT 1")->fetchColumn();
      $q=$this->db->prepare("INSERT INTO inspecoes(documento_id,status_id) VALUES(?,?)");$q->execute([$id,$status]);
      $this->db->commit();return $id;
    }catch(Throwable $e){$this->db->rollBack();throw $e;}
  }

  public function documento(int $id):?array{
    $st=$this->db->prepare("SELECT d.*,td.nome tipo_documento,o.numero obrigacao_numero,o.ano obrigacao_ano,f.nome fornecedor,i.id inspecao_id,i.status_id,i.data_envio_cooinsp,i.hora_envio_cooinsp,i.data_devolucao_pendencia,i.motivo_devolucao,i.data_retorno_pendencia,i.hora_retorno_cooinsp,i.data_conclusao,i.observacoes inspecao_observacoes,s.nome status_inspecao,s.permite_avancar FROM documentos_pagamento d JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id LEFT JOIN inspecoes i ON i.documento_id=d.id LEFT JOIN status_inspecao s ON s.id=i.status_id WHERE d.id=?");$st->execute([$id]);$r=$st->fetch();return $r?:null;
  }

  public function atualizarInspecao(int $documentoId,array $d):void{
    $doc=$this->documento($documentoId);if(!$doc)throw new RuntimeException('Documento não encontrado.');
    $status=(int)($d['status_id']??0);$s=$this->db->prepare("SELECT * FROM status_inspecao WHERE id=? AND ativo=1");$s->execute([$status]);$statusRow=$s->fetch();if(!$statusRow)throw new InvalidArgumentException('Status inválido.');
    $conclusao=(int)$statusRow['encerra_inspecao']===1?($d['data_conclusao']?:date('Y-m-d')):null;
    $st=$this->db->prepare("UPDATE inspecoes SET status_id=?,data_envio_cooinsp=?,hora_envio_cooinsp=?,data_devolucao_pendencia=?,motivo_devolucao=?,data_retorno_pendencia=?,hora_retorno_cooinsp=?,data_conclusao=?,observacoes=?,atualizado_em=NOW() WHERE documento_id=?");
    $st->execute([$status,$d['data_envio_cooinsp']?:null,$d['hora_envio_cooinsp']?:null,$d['data_devolucao_pendencia']?:null,trim((string)($d['motivo_devolucao']??''))?:null,$d['data_retorno_pendencia']?:null,$d['hora_retorno_cooinsp']?:null,$conclusao,trim((string)($d['observacoes']??''))?:null,$documentoId]);
    $hist=$this->db->prepare("INSERT INTO inspecao_historico(inspecao_id,status_id,observacao) SELECT id,?,? FROM inspecoes WHERE documento_id=?");$hist->execute([$status,trim((string)($d['observacoes']??''))?:null,$documentoId]);
  }

  public function parcelas(int $documentoId):array{
    $st=$this->db->prepare("SELECT p.*,e.numero empenho_numero,e.ano empenho_ano,(SELECT COALESCE(SUM(pc.valor),0) FROM parcela_componentes pc WHERE pc.parcela_id=p.id) soma_componentes,l.status status_liquidacao,l.data_liquidacao,c.status status_cmdf,c.data_conclusao data_cmdf,pg.status status_pagamento,pg.data_pagamento FROM parcelas_pagamento p JOIN empenhos_pagamento e ON e.id=p.empenho_pagamento_id LEFT JOIN liquidacoes l ON l.parcela_id=p.id LEFT JOIN cmdf_etapas c ON c.parcela_id=p.id LEFT JOIN pagamentos pg ON pg.parcela_id=p.id WHERE p.documento_id=? ORDER BY p.numero_parcela");$st->execute([$documentoId]);return $st->fetchAll();
  }

  public function adicionarParcela(int $documentoId,array $d):int{
    $doc=$this->documento($documentoId);if(!$doc)throw new RuntimeException('Documento não encontrado.');if(!(bool)$doc['permite_avancar'])throw new RuntimeException('A inspeção precisa estar Concluída ou Concluída com ressalvas.');
    $valor=$this->decimal($d['valor_total']??null);$emp=(int)($d['empenho_pagamento_id']??0);if(!$emp||$valor===null||$valor<=0)throw new InvalidArgumentException('Informe empenho e valor da parcela.');
    $s=$this->db->prepare("SELECT COALESCE(SUM(valor_total),0) FROM parcelas_pagamento WHERE documento_id=?");$s->execute([$documentoId]);$ja=(float)$s->fetchColumn();if(round($ja+$valor,2)>round((float)$doc['valor_bruto'],2))throw new RuntimeException('A soma das parcelas não pode ultrapassar o valor do documento.');
    $n=$this->db->prepare("SELECT COALESCE(MAX(numero_parcela),0)+1 FROM parcelas_pagamento WHERE documento_id=?");$n->execute([$documentoId]);$numero=(int)$n->fetchColumn();
    $this->db->beginTransaction();try{$st=$this->db->prepare("INSERT INTO parcelas_pagamento(documento_id,empenho_pagamento_id,numero_parcela,valor_total,historico_liquidacao,fila,justificativa_ordem_cronologica,justificativa_atraso) VALUES(?,?,?,?,?,?,?,?)");$st->execute([$documentoId,$emp,$numero,$valor,mb_substr(trim((string)($d['historico_liquidacao']??'')),0,119),trim((string)($d['fila']??''))?:null,mb_substr(trim((string)($d['justificativa_ordem_cronologica']??'')),0,150),trim((string)($d['justificativa_atraso']??''))?:null]);$id=(int)$this->db->lastInsertId();$this->db->prepare("INSERT INTO liquidacoes(parcela_id) VALUES(?)")->execute([$id]);$this->db->commit();return $id;}catch(Throwable $e){$this->db->rollBack();throw $e;}
  }

  public function adicionarComponente(int $parcelaId,array $d):void{
    $tipo=(int)($d['tipo_componente_id']??0);$valor=$this->decimal($d['valor']??null);if(!$tipo||$valor===null||$valor<=0)throw new InvalidArgumentException('Informe tipo e valor do componente.');
    $st=$this->db->prepare("SELECT p.valor_total,COALESCE(SUM(pc.valor),0) soma FROM parcelas_pagamento p LEFT JOIN parcela_componentes pc ON pc.parcela_id=p.id WHERE p.id=? GROUP BY p.id");$st->execute([$parcelaId]);$p=$st->fetch();if(!$p)throw new RuntimeException('Parcela não encontrada.');if(round((float)$p['soma']+$valor,2)>round((float)$p['valor_total'],2))throw new RuntimeException('Componentes não podem ultrapassar o valor da parcela.');
    $q=$this->db->prepare("INSERT INTO parcela_componentes(parcela_id,tipo_componente_id,valor,observacao) VALUES(?,?,?,?)");$q->execute([$parcelaId,$tipo,$valor,trim((string)($d['observacao']??''))?:null]);
  }

  public function componentesParcela(int $parcelaId):array{$st=$this->db->prepare("SELECT pc.*,t.nome,t.codigo,t.categoria FROM parcela_componentes pc JOIN tipos_componente_pagamento t ON t.id=pc.tipo_componente_id WHERE pc.parcela_id=? ORDER BY t.ordem,pc.id");$st->execute([$parcelaId]);return $st->fetchAll();}

  public function documentoFechado(int $documentoId):bool{$st=$this->db->prepare("SELECT d.valor_bruto,COALESCE(SUM(p.valor_total),0) soma FROM documentos_pagamento d LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id WHERE d.id=? GROUP BY d.id");$st->execute([$documentoId]);$r=$st->fetch();return $r&&round((float)$r['valor_bruto'],2)===round((float)$r['soma'],2);}
  public function parcelaComposicaoFechada(int $parcelaId):bool{$st=$this->db->prepare("SELECT p.valor_total,COALESCE(SUM(pc.valor),0) soma FROM parcelas_pagamento p LEFT JOIN parcela_componentes pc ON pc.parcela_id=p.id WHERE p.id=? GROUP BY p.id");$st->execute([$parcelaId]);$r=$st->fetch();return $r&&round((float)$r['valor_total'],2)===round((float)$r['soma'],2);}

  public function concluirLiquidacao(int $parcelaId,string $data):void{
    $st=$this->db->prepare("SELECT documento_id FROM parcelas_pagamento WHERE id=?");$st->execute([$parcelaId]);$doc=(int)$st->fetchColumn();if(!$doc||!$this->documentoFechado($doc))throw new RuntimeException('A soma das parcelas deve ser exatamente igual ao valor do documento.');if(!$this->parcelaComposicaoFechada($parcelaId))throw new RuntimeException('A composição da parcela deve fechar exatamente o valor da parcela.');
    $this->db->beginTransaction();try{$this->db->prepare("UPDATE liquidacoes SET status='CONCLUIDA',data_liquidacao=?,atualizado_em=NOW() WHERE parcela_id=?")->execute([$data,$parcelaId]);$this->db->prepare("INSERT INTO cmdf_etapas(parcela_id) VALUES(?) ON DUPLICATE KEY UPDATE parcela_id=VALUES(parcela_id)")->execute([$parcelaId]);$this->db->commit();}catch(Throwable $e){$this->db->rollBack();throw $e;}
  }

  public function concluirCmdf(int $parcelaId,array $d):void{
    $st=$this->db->prepare("SELECT status FROM liquidacoes WHERE parcela_id=?");$st->execute([$parcelaId]);if($st->fetchColumn()!=='CONCLUIDA')throw new RuntimeException('A liquidação precisa estar concluída.');$data=$d['data_conclusao']?:date('Y-m-d');
    $q=$this->db->prepare("UPDATE cmdf_etapas SET status='CONCLUIDA',data_envio_seinfra=?,data_despacho_seinfra=?,data_envio_economia=?,data_atendimento_economia=?,data_conclusao=?,observacoes=?,atualizado_em=NOW() WHERE parcela_id=?");$q->execute([$d['data_envio_seinfra']?:null,$d['data_despacho_seinfra']?:null,$d['data_envio_economia']?:null,$d['data_atendimento_economia']?:null,$data,trim((string)($d['observacoes']??''))?:null,$parcelaId]);
    $this->db->prepare("INSERT INTO pagamentos(parcela_id) VALUES(?) ON DUPLICATE KEY UPDATE parcela_id=VALUES(parcela_id)")->execute([$parcelaId]);
  }

  private function decimal(mixed $v):?float{if($v===null||$v==='')return null;$s=str_replace(['R$',' '],'',(string)$v);if(str_contains($s,','))$s=str_replace(['.',','],['','.'],$s);return is_numeric($s)?round((float)$s,2):null;}
}
