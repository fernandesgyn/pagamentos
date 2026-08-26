<?php
$pageBackUrl='/inspecoes';
$pageRightActions=[[ 'href'=>'/documentos/'.$doc['id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
if((bool)($doc['permite_avancar']??false)&&Auth::can('parcela.gerir')){
    $pageRightActions[]=['href'=>'/programacao/'.$doc['id'],'label'=>'Programar parcelas','icon'=>'fa-list-check','class'=>'btn btn-sm btn-primary'];
}
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

<?php if((bool)($doc['permite_avancar']??false)):?>
<div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div><i class="fa-solid fa-circle-check me-1"></i>Este documento está liberado pela Inspeção para criação das parcelas na Programação.</div>
  <?php if(Auth::can('parcela.gerir')):?><a href="/programacao/<?=e($doc['id'])?>" class="btn btn-sm btn-success"><i class="fa-solid fa-arrow-right me-1"></i>Ir para Programação</a><?php else:?><span class="small">O usuário responsável pela Programação já pode prosseguir.</span><?php endif;?>
</div>
<?php endif;?>

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
        <div class="form-text">“Finalizada” e “Liberada liquidação de imposto” liberam o documento para Programação.</div>
      </div>
      <div class="col-md-3"><label class="form-label">Data de conclusão *</label><input type="date" name="data_conclusao" value="<?=e($doc['data_conclusao']??'')?>" class="form-control" required><div class="form-text">Obrigatória ao salvar a inspeção; o encerramento continua definido pelo Status.</div></div>
      <div class="col-12"><label class="form-label">Observação</label><textarea name="observacao" class="form-control" maxlength="500" rows="3" placeholder="Registro para o histórico da inspeção"></textarea></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/inspecoes" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar inspeção</button></div>
</form>

<?php if(!empty($doc['data_conclusao'])):
  $reversaoAction='/inspecoes/'.$doc['id'].'/desfazer';
  $reversaoTitulo='Reabrir Inspeção';
  $reversaoTexto='Volta a inspeção para “Inspeção andamento”. Se já houver parcelas programadas, desfaça primeiro a Programação.';
  $reversaoBotao='Reabrir inspeção';
  require BASE_PATH.'/app/views/components/reversao_form.php';
endif;?>
