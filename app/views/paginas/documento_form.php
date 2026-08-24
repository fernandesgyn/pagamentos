<?php
$pageBackUrl='/documentos';
require BASE_PATH.'/app/views/components/page_actions.php';
$obrigacaoSelecionada=(int)($_GET['obrigacao_id']??0);
?>
<form method="post" action="/documentos" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados do documento</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label">Obrigação *</label><select name="obrigacao_id" class="form-select" required><option value="">Selecione</option><?php foreach($obrigacoes as $o):?><option value="<?=e($o['id'])?>" <?=((int)$o['id']===$obrigacaoSelecionada)?'selected':''?>><?=e($o['tipo'])?> <?=e($o['numero'])?>/<?=e($o['ano'])?> — <?=e($o['fornecedor'])?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">Tipo do documento *</label><select name="tipo_documento_id" class="form-select" required><option value="">Selecione</option><?php foreach($tipos as $t):?><option value="<?=e($t['id'])?>"><?=e($t['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">Número *</label><input name="numero" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Data do documento *</label><input type="date" name="data_documento" value="<?=date('Y-m-d')?>" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Valor bruto *</label><input name="valor_bruto" class="form-control" required placeholder="0,00"></div>
      <div class="col-md-4"><label class="form-label">Data máxima liquidação</label><input type="date" name="data_maxima_liquidacao" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Limite anotação</label><input type="date" name="limite_anotacao" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Data do atesto</label><input type="date" name="data_atesto" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Tipo de serviço</label><input name="tipo_servico" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Nº SEI pagamento</label><input name="sei_pagamento" class="form-control"></div>
      <div class="col-12"><label class="form-label">Observações</label><textarea name="observacoes" class="form-control" rows="3"></textarea></div>
      <div class="col-12"><div class="form-text">A data e hora do lançamento são registradas automaticamente pelo sistema.</div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/documentos" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Salvar</button></div>
</form>
<?php unset($obrigacaoSelecionada);?>
