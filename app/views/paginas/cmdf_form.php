<?php
$pageBackUrl='/cmdf';
$pageRightActions=[[ 'href'=>'/documentos/'.$p['documento_id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
$statusAtual=(string)($p['status_cmdf']??'AGUARDANDO');
?>
<div class="card mb-3"><div class="card-body"><div class="row g-3">
  <div class="col-md-3"><div class="small text-body-secondary">Documento / Parcela</div><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?> · Parcela <?=e($p['numero_parcela'])?></strong></div>
  <div class="col-md-3"><div class="small text-body-secondary">Fornecedor</div><strong><?=e($p['fornecedor'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Empenho</div><strong><?=e($p['numero_empenho'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Valor líquido</div><strong><?=money($p['valor_liquido'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Liquidação</div><strong><?=e($p['status_liquidacao']??'—')?></strong><div class="small"><?=e($p['data_liquidacao']??'')?></div></div>
</div></div></div>

<form method="post" action="/cmdf/<?=e($p['id'])?>" class="card card-dark card-outline">
  <div class="card-header"><h3 class="card-title">CMDF da parcela</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Status *</label><select name="status" class="form-select" required><?php foreach(['AGUARDANDO'=>'Aguardando CMDF','LIQUIDADA'=>'Liquidada','CANCELADA'=>'Cancelada','ANULADA'=>'Anulada'] as $valor=>$rotulo):?><option value="<?=$valor?>" <?=$statusAtual===$valor?'selected':''?>><?=$rotulo?></option><?php endforeach;?></select><div class="form-text">O status Liquidada conclui a CMDF desta parcela e a libera para Pagamento.</div></div>
      <div class="col-md-3"><label class="form-label">Data de conclusão</label><input type="date" name="data_conclusao" value="<?=e($p['data_cmdf']??'')?>" class="form-control"><div class="form-text">Utilizada quando a CMDF for Liquidada.</div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/cmdf" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-dark"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar CMDF</button></div>
</form>
<?php unset($statusAtual,$valor,$rotulo);?>
