<?php
$pageBackUrl='/programacao';
$pageRightActions=[[ 'href'=>'/documentos/'.$doc['id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
$programado=0.0;foreach($parcelas as $parcela)$programado+=(float)$parcela['valor_liquido'];
$saldo=round((float)$doc['valor_liquido']-$programado,2);
$proxima=empty($parcelas)?1:(max(array_map(static fn($p)=>(int)$p['numero_parcela'],$parcelas))+1);
?>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Documento</div><strong><?=e($doc['tipo_documento'])?> <?=e($doc['numero'])?></strong></div></div></div>
  <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Fornecedor</div><strong><?=e($doc['fornecedor'])?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Valor líquido</div><strong><?=money($doc['valor_liquido'])?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Programado</div><strong><?=money($programado)?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Saldo</div><strong><?=money($saldo)?></strong></div></div></div>
</div>

<?php if(!$doc['permite_avancar']):?><div class="alert alert-warning">Este documento não está liberado pela Inspeção para programação.</div><?php endif;?>
<?php if($fechada):?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>A programação está fechada. Cada parcela pode seguir individualmente para Liquidação.</div><?php endif;?>

<?php if(!$fechada&&$doc['permite_avancar']):?>
<form method="post" action="/programacao/<?=e($doc['id'])?>/parcelas" class="card card-primary card-outline mb-3">
  <div class="card-header"><h3 class="card-title">Adicionar parcela <?=e($proxima)?></h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Nr. empenho *</label><input name="numero_empenho" class="form-control" maxlength="80" required></div>
      <div class="col-md-4"><label class="form-label">Natureza da despesa *</label><select name="natureza_despesa_id" class="form-select" required><option value="">Selecione</option><?php foreach($naturezas as $n):?><option value="<?=e($n['id'])?>"><?=e($n['codigo'])?> — <?=e($n['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-2"><label class="form-label">Exercício orçamentário *</label><input type="number" name="exercicio_orcamentario" min="2000" max="2100" value="<?=date('Y')?>" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Fonte de recurso *</label><select name="fonte_recurso_id" class="form-select" required><option value="">Selecione</option><?php foreach($fontes as $f):?><option value="<?=e($f['id'])?>"><?=e($f['codigo'])?> — <?=e($f['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-3"><label class="form-label">Tipo do recurso *</label><select name="tipo_recurso_id" class="form-select" required><option value="">Selecione</option><?php foreach($tiposRecurso as $r):?><option value="<?=e($r['id'])?>"><?=e($r['codigo'])?> — <?=e($r['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-2"><label class="form-label">Valor líquido *</label><input name="valor_liquido" class="form-control" placeholder="0,00" required><div class="form-text">Máximo restante: <?=money($saldo)?></div></div>
      <div class="col-md-3"><label class="form-label">Tipo *</label><select name="tipo" class="form-select" required><option value="">Selecione</option><?php foreach(['IMPOSTO','DARE','INSS','PIS','COFINS','IR','ISS'] as $tipo):?><option value="<?=$tipo?>"><?=$tipo?></option><?php endforeach;?></select></div>
      <div class="col-md-3"><label class="form-label">Data de vencimento</label><input type="date" name="data_vencimento" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Histórico da parcela</label><input name="historico_parcela" maxlength="255" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Justificativa ordem cronológica</label><input name="justificativa_ordem_cronologica" maxlength="150" class="form-control"></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Adicionar parcela</button></div>
</form>
<?php endif;?>

<div class="card">
  <div class="card-header"><h3 class="card-title">Parcelas programadas</h3></div>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>Parcela</th><th>Empenho</th><th>Natureza</th><th>Exercício</th><th>Fonte</th><th>Recurso</th><th>Tipo</th><th>Valor líquido</th><th>Vencimento</th><th>Liquidação</th><th class="text-end">Ação</th></tr></thead>
    <tbody>
      <?php foreach($parcelas as $p):?><tr><td><strong><?=e($p['numero_parcela'])?></strong></td><td><?=e($p['numero_empenho'])?></td><td><?=e($p['natureza_codigo'])?></td><td><?=e($p['exercicio_orcamentario'])?></td><td><?=e($p['fonte_codigo'])?></td><td><?=e($p['tipo_recurso_codigo'])?></td><td><?=e($p['tipo'])?></td><td><?=money($p['valor_liquido'])?></td><td><?=e($p['data_vencimento']??'—')?></td><td><?=e($p['status_liquidacao']??'AGUARDANDO')?></td><td class="text-end"><?php if($fechada&&Auth::can('liquidacao.gerir')):?><a href="/liquidacoes/<?=e($p['id'])?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check-double me-1"></i>Liquidar</a><?php endif;?></td></tr><?php endforeach;?>
      <?php if(!$parcelas):?><tr><td colspan="11" class="text-center text-body-secondary py-4">Nenhuma parcela programada.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
</div>
<?php unset($programado,$saldo,$proxima,$parcela,$tipo);?>
