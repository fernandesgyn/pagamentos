<?php
$pageBackUrl='/inspecoes';
$pageRightActions=[[ 'href'=>'/documentos/'.$doc['id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="card mb-3">
  <div class="card-body"><div class="row g-3">
    <div class="col-md-3"><div class="small text-body-secondary">Documento</div><strong><?=e($doc['tipo_documento'])?> <?=e($doc['numero'])?></strong></div>
    <div class="col-md-3"><div class="small text-body-secondary">Fornecedor</div><strong><?=e($doc['fornecedor'])?></strong></div>
    <div class="col-md-2"><div class="small text-body-secondary">Valor bruto</div><strong><?=money($doc['valor_bruto'])?></strong></div>
    <div class="col-md-2"><div class="small text-body-secondary">Valor líquido</div><strong><?=money($doc['valor_liquido'])?></strong></div>
    <div class="col-md-2"><div class="small text-body-secondary">Envio COOINSP</div><strong><?=e($doc['data_envio_cooinsp']??'—')?></strong></div>
  </div></div>
</div>

<form method="post" action="/inspecoes/<?=e($doc['id'])?>" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Atualizar inspeção</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Status *</label>
        <select name="status_id" class="form-select" required>
          <?php foreach($status as $s):?><option value="<?=e($s['id'])?>" <?=((int)$s['id']===(int)$doc['status_id'])?'selected':''?>><?=e($s['nome'])?></option><?php endforeach;?>
        </select>
        <div class="form-text">Somente “Liberada liquidação de imposto” libera o documento para Programação.</div>
      </div>
      <div class="col-md-3"><label class="form-label">Data de conclusão</label><input type="date" name="data_conclusao" value="<?=e($doc['data_conclusao']??'')?>" class="form-control"><div class="form-text">Usada nos status que encerram a inspeção.</div></div>
      <div class="col-12"><label class="form-label">Observação</label><textarea name="observacao" class="form-control" maxlength="500" rows="3" placeholder="Registro para o histórico da inspeção"></textarea></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/inspecoes" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar inspeção</button></div>
</form>
