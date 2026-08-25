<?php
$pageBackUrl='/liquidacoes';
$pageRightActions=[[ 'href'=>'/documentos/'.$p['documento_id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
if(!empty($p['cmdf_grupo_id'])&&Auth::can('cmdf.gerir'))$pageRightActions[]=['href'=>'/cmdf/grupos/'.$p['cmdf_grupo_id'],'label'=>'Abrir grupo CMDF','icon'=>'fa-layer-group','class'=>'btn btn-sm btn-outline-dark'];
require BASE_PATH.'/app/views/components/page_actions.php';
$statusAtual=(string)($p['status_liquidacao']??'AGUARDANDO');
?>
<div class="card mb-3"><div class="card-body"><div class="row g-3">
  <div class="col-md-3"><div class="small text-body-secondary">Documento / Parcela</div><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?> · Parcela <?=e($p['numero_parcela'])?></strong></div>
  <div class="col-md-3"><div class="small text-body-secondary">Fornecedor</div><strong><?=e($p['fornecedor'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Empenho</div><strong><?=e($p['numero_empenho'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">Valor líquido</div><strong><?=money($p['valor_liquido'])?></strong></div>
  <div class="col-md-2"><div class="small text-body-secondary">CMDF</div><strong><?=!empty($p['cmdf_grupo_id'])?'Grupo #'.e($p['cmdf_grupo_id']):'Ainda sem grupo'?></strong></div>
</div></div></div>

<?php if($statusAtual==='AGUARDANDO'):?>
<form method="post" action="/liquidacoes/<?=e($p['id'])?>" class="card card-success card-outline">
  <div class="card-header"><h3 class="card-title">Liquidar parcela</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Status *</label><select name="status" class="form-select" required><?php foreach(['AGUARDANDO'=>'Aguardando liquidação','LIQUIDADA'=>'Liquidada','CANCELADA'=>'Cancelada','ANULADA'=>'Anulada'] as $valor=>$rotulo):?><option value="<?=$valor?>" <?=$statusAtual===$valor?'selected':''?>><?=$rotulo?></option><?php endforeach;?></select><div class="form-text">Somente Liquidada deixa a parcela disponível para composição de grupo CMDF.</div></div>
      <div class="col-md-3"><label class="form-label">Data de liquidação</label><input type="date" name="data_liquidacao" value="<?=e($p['data_liquidacao']??'')?>" class="form-control"><div class="form-text">Obrigatória para Liquidada.</div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/liquidacoes" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar liquidação</button></div>
</form>
<?php else:?>
<div class="card card-success card-outline"><div class="card-header"><h3 class="card-title">Liquidação registrada</h3></div><div class="card-body"><p class="mb-1"><strong>Status:</strong> <?=e($statusAtual)?></p><p class="mb-0"><strong>Data:</strong> <?=e($p['data_liquidacao']??'—')?></p></div></div>
<?php
  $reversaoAction='/liquidacoes/'.$p['id'].'/desfazer';
  $reversaoTitulo='Desfazer Liquidação';
  $reversaoTexto='Volta a parcela para Aguardando liquidação. Depois você poderá liquidá-la novamente com os dados corretos.';
  $reversaoBotao='Desfazer liquidação';
  $reversaoBloqueio=!empty($p['cmdf_grupo_id'])?'A parcela já está na CMDF. Desfaça o status da CMDF até Fechada e remova a parcela do grupo antes de desfazer a Liquidação.':'';
  require BASE_PATH.'/app/views/components/reversao_form.php';
endif;?>
<?php unset($statusAtual,$valor,$rotulo);?>
