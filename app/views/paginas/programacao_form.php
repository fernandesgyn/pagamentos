<?php
$pageBackUrl='/programacao';
$pageRightActions=[[ 'href'=>'/documentos/'.$doc['id'],'label'=>'Ver documento','icon'=>'fa-file-lines','class'=>'btn btn-sm btn-outline-secondary' ]];
if($fechada&&Auth::can('liquidacao.gerir'))$pageRightActions[]=['href'=>'/liquidacoes','label'=>'Abrir fila de Liquidação','icon'=>'fa-check-double','class'=>'btn btn-sm btn-success'];
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

<?php if(!$doc['permite_avancar']):?><div class="alert alert-warning">Este documento não está liberado pela Inspeção para Programação.</div><?php endif;?>
<?php if($fechada):?>
<div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i><strong>Programação concluída.</strong> A soma das parcelas fechou o valor líquido do documento. Se precisar corrigir uma parcela, desfaça primeiro qualquer etapa posterior que já tenha sido executada.</div>
<?php else:?>
<div class="alert alert-info"><i class="fa-solid fa-circle-info me-1"></i>Cadastre as parcelas até o saldo chegar a R$ 0,00. Todos os campos marcados com * são obrigatórios.</div>
<?php endif;?>

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
      <div class="col-md-3"><label class="form-label">Origem do Recurso *</label><select name="origem_recurso_id" class="form-select" required><option value="">Selecione</option><?php foreach($origens as $r):?><option value="<?=e($r['id'])?>"><?=e($r['codigo'])?> — <?=e($r['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-2"><label class="form-label">Valor líquido *</label><input name="valor_liquido" class="form-control" placeholder="0,00" required><div class="form-text">Máximo: <?=money($saldo)?></div></div>
      <div class="col-md-3"><label class="form-label">Tipo *</label><select name="tipo" class="form-select" required><option value="">Selecione</option><?php foreach(['IMPOSTO','DARE','INSS','PIS','COFINS','IR','ISS'] as $tipo):?><option value="<?=$tipo?>"><?=$tipo?></option><?php endforeach;?></select></div>
      <div class="col-md-3"><label class="form-label">Data de vencimento *</label><input type="date" name="data_vencimento" class="form-control" required></div>
      <div class="col-md-3"><label class="form-label">IPOF *</label><input name="ipof" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" class="form-control" placeholder="10 dígitos" required></div>
      <div class="col-md-3"><label class="form-label">AP Benner *</label><input name="ap_benner" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" class="form-control" placeholder="10 dígitos" required></div>
      <div class="col-md-2"><label class="form-label">Sequencial *</label><input name="sequencial" inputmode="numeric" pattern="[0-9]{3}" minlength="3" maxlength="3" class="form-control" placeholder="000" required></div>
      <div class="col-md-2"><label class="form-label">Grupo de Despesa *</label><input name="grupo_despesa" inputmode="numeric" pattern="[0-9]{2}" minlength="2" maxlength="2" class="form-control" placeholder="00" required></div>
      <div class="col-md-8"><label class="form-label">Histórico da parcela *</label><input name="historico_parcela" maxlength="255" class="form-control" required></div>
      <div class="col-md-12"><label class="form-label">Justificativa ordem cronológica</label><input name="justificativa_ordem_cronologica" maxlength="150" class="form-control"><div class="form-text">Opcional · máximo 150 caracteres.</div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Adicionar parcela</button></div>
</form>
<?php endif;?>

<?php
$tableId='programacao-parcelas-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisar parcelas','column'=>'*','type'=>'search','placeholder'=>'Empenho, natureza, IPOF ou AP Benner','class'=>'col-12 col-lg-5'],
 ['label'=>'Liquidação','column'=>13,'type'=>'select','populate'=>true,'empty'=>'Todas','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Parcelas programadas</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Parcela</th><th>Empenho</th><th>Natureza</th><th>Exercício</th><th>Fonte</th><th>Origem</th><th>Valor</th><th>Tipo</th><th>Vencimento</th><th>IPOF</th><th>AP Benner</th><th>Seq.</th><th>Grupo</th><th>Liquidação</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($parcelas as $p):
        $podeDesfazer=((string)($p['status_liquidacao']??'AGUARDANDO')==='AGUARDANDO'&&empty($p['cmdf_grupo_id'])&&empty($p['status_pagamento']));
      ?><tr data-record-id="<?=e($p['id'])?>">
        <td><strong><?=e($p['numero_parcela'])?></strong></td><td><?=e($p['numero_empenho'])?></td><td><?=e($p['natureza_codigo'])?></td><td><?=e($p['exercicio_orcamentario'])?></td><td><?=e($p['fonte_codigo'])?></td><td><?=e($p['origem_codigo'])?></td><td><?=money($p['valor_liquido'])?></td><td><?=e($p['tipo'])?></td><td><?=e($p['data_vencimento'])?></td><td><?=e($p['ipof'])?></td><td><?=e($p['ap_benner'])?></td><td><?=e($p['sequencial'])?></td><td><?=e($p['grupo_despesa'])?></td><td><span class="badge <?=$p['status_liquidacao']==='LIQUIDADA'?'text-bg-success':'text-bg-warning'?>"><?=e($p['status_liquidacao']??'AGUARDANDO')?></span></td>
        <td class="text-end portal-actions-cell">
          <div class="portal-action-group portal-table-actions justify-content-end">
            <?php if($podeDesfazer):?>
              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/programacao/<?=e($doc['id'])?>/parcelas/<?=e($p['id'])?>/desfazer" data-reversao-titulo="Desfazer Programação da parcela <?=e($p['numero_parcela'])?>" data-reversao-texto="Esta parcela será removida da Programação, o saldo do documento será reaberto e as parcelas posteriores serão renumeradas." data-reversao-botao="Desfazer programação"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer programação</button>
            <?php elseif(!empty($p['status_pagamento'])):?><button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Desfaça o Pagamento primeiro"><i class="fa-solid fa-lock me-1"></i>Desfazer programação</button>
            <?php elseif(!empty($p['cmdf_grupo_id'])):?><a href="/cmdf/grupos/<?=e($p['cmdf_grupo_id'])?>" class="btn btn-sm btn-outline-secondary" title="Desfaça a CMDF e remova a parcela do grupo primeiro"><i class="fa-solid fa-layer-group me-1"></i>Abrir CMDF</a>
            <?php else:?><a href="/liquidacoes/<?=e($p['id'])?>" class="btn btn-sm btn-outline-secondary" title="Desfaça a Liquidação primeiro"><i class="fa-solid fa-check-double me-1"></i>Abrir Liquidação</a><?php endif;?>
          </div>
        </td>
      </tr><?php endforeach;?>
      <?php if(!$parcelas):?><tr data-table-empty><td colspan="15" class="text-center text-body-secondary py-4">Nenhuma parcela programada.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($programado,$saldo,$proxima,$parcela,$tipo,$podeDesfazer,$tableId,$tablePageSize,$tableFilters);?>
