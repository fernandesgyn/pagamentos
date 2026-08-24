<?php
$pageBackUrl='/liquidacoes';
$pageRightActions=[[ 'href'=>'/documentos/'.$p['documento_id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
$statusAtual=(string)($p['status_liquidacao']??'AGUARDANDO');
?>
<div class="card mb-3"><div class="card-body"><div class="row g-3">
  <div class="col-md-3"><div class="small text-body-secondary">Documento / Parcela</div><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?> · Parcela <?=e($p['numero_parcela'])?></strong></div>
  <div class="col-md-3"><div class="small text-body-secondary">Fornecedor</div><strong><?=e($p['fornecedor'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Empenho</div><strong><?=e($p['numero_empenho'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Valor líquido</div><strong><?=money($p['valor_liquido'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Tipo</div><strong><?=e($p['tipo'])?></strong></div>
</div></div></div>

<form method="post" action="/liquidacoes/<?=e($p['id'])?>" class="card card-success card-outline">
  <div class="card-header"><h3 class="card-title">Liquidação da parcela</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Status *</label><select name="status" class="form-select" required><?php foreach(['AGUARDANDO'=>'Aguardando liquidação','LIQUIDADA'=>'Liquidada','CANCELADA'=>'Cancelada','ANULADA'=>'Anulada'] as $valor=>$rotulo):?><option value="<?=$valor?>" <?=$statusAtual===$valor?'selected':''?>><?=$rotulo?></option><?php endforeach;?></select><div class="form-text">Somente o status Liquidada libera esta parcela para CMDF.</div></div>
      <div class="col-md-3"><label class="form-label">Data de liquidação</label><input type="date" name="data_liquidacao" value="<?=e($p['data_liquidacao']??'')?>" class="form-control"><div class="form-text">Obrigatória para status Liquidada.</div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between"><div><?php if($statusAtual==='LIQUIDADA'&&Auth::can('cmdf.gerir')):?><a href="/cmdf/<?=e($p['id'])?>" class="btn btn-outline-dark"><i class="fa-solid fa-building-columns me-1"></i>Ir para CMDF</a><?php endif;?></div><div class="d-flex gap-2"><a href="/liquidacoes" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar liquidação</button></div></div>
</form>
<?php unset($statusAtual,$valor,$rotulo);?>
