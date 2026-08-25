<?php
$tableId='cmdf-grupos-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Grupo, fonte, origem, sequencial ou grupo de despesa','class'=>'col-12 col-lg-4'],
 ['label'=>'Status','column'=>6,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="alert alert-info">
  <strong>Regra de agrupamento CMDF:</strong> a parcela precisa estar Liquidada e seu documento deve possuir Data do atesto. No mesmo grupo, as parcelas devem ter a mesma Fonte de recurso, Exercício orçamentário, Sequencial, Grupo de Despesa e Origem do Recurso.
</div>

<?php if(Auth::can('cmdf.grupo.ajustar')):?>
<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Agrupamento inteligente</h3>
    <form method="post" action="/cmdf/grupos/sugerir" class="m-0"><?=Csrf::field()?><button class="btn btn-sm btn-primary"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Criar grupos sugeridos</button></form>
  </div>
  <div class="card-body">
    <?php if($sugestoes):?>
      <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Fonte</th><th>Exercício</th><th>Sequencial</th><th>Grupo Despesa</th><th>Origem</th><th>Parcelas</th><th>Valor</th></tr></thead><tbody>
      <?php foreach($sugestoes as $s):?><tr><td><?=e($s['fonte_codigo'])?></td><td><?=e($s['exercicio_orcamentario'])?></td><td><?=e($s['sequencial'])?></td><td><?=e($s['grupo_despesa'])?></td><td><?=e($s['origem_codigo'])?></td><td><?=e($s['parcelas_total'])?></td><td><?=money($s['valor_total'])?></td></tr><?php endforeach;?>
      </tbody></table></div>
    <?php else:?><div class="text-body-secondary">Não há novas sugestões. Todas as parcelas elegíveis já estão agrupadas ou não há parcelas aptas.</div><?php endif;?>
  </div>
</div>
<?php endif;?>

<div class="card mb-3" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Grupos CMDF</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Grupo</th><th>Fonte</th><th>Exercício</th><th>Sequencial</th><th>Grupo Despesa</th><th>Origem</th><th>Status</th><th>Parcelas</th><th>Valor total</th><th>Criação</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($grupos as $g):?><?php $badge=$g['status']==='ATENDIDA'?'text-bg-success':($g['status']==='LIBERADA'?'text-bg-primary':'text-bg-secondary');?>
      <tr data-record-id="<?=e($g['id'])?>"><td><strong>#<?=e($g['id'])?></strong><div class="small text-body-secondary"><?=$g['gerado_automaticamente']?'Automático':'Manual'?></div></td><td><?=e($g['fonte_codigo'])?></td><td><?=e($g['exercicio_orcamentario'])?></td><td><?=e($g['sequencial'])?></td><td><?=e($g['grupo_despesa'])?></td><td><?=e($g['origem_codigo'])?></td><td><span class="badge <?=$badge?>"><?=e($g['status'])?></span></td><td><?=e($g['parcelas_total'])?></td><td><?=money($g['valor_total'])?></td><td><?=e($g['criado_em'])?></td><td class="text-end"><a href="/cmdf/grupos/<?=e($g['id'])?>" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-layer-group me-1"></i>Abrir</a></td></tr>
      <?php endforeach;?>
      <?php if(!$grupos):?><tr data-table-empty><td colspan="11" class="text-center text-body-secondary py-4">Nenhum grupo CMDF criado.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>

<?php if(Auth::can('cmdf.grupo.ajustar')):?>
<form method="post" action="/cmdf/grupos" class="card">
  <div class="card-header"><h3 class="card-title">Criar grupo manualmente</h3></div>
  <div class="card-body p-0">
    <?=Csrf::field()?>
    <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
      <thead><tr><th style="width:42px"></th><th>Documento / Parcela</th><th>Fornecedor</th><th>Atesto</th><th>Fonte</th><th>Exercício</th><th>Sequencial</th><th>Grupo Despesa</th><th>Origem</th><th>Valor</th></tr></thead>
      <tbody>
        <?php foreach($disponiveis as $p):?><tr><td><input class="form-check-input" type="checkbox" name="parcelas_ids[]" value="<?=e($p['parcela_id'])?>"></td><td><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?></strong><div class="small">Parcela <?=e($p['numero_parcela'])?></div></td><td><?=e($p['fornecedor'])?></td><td><?=e($p['data_atesto'])?></td><td><?=e($p['fonte_codigo'])?></td><td><?=e($p['exercicio_orcamentario'])?></td><td><?=e($p['sequencial'])?></td><td><?=e($p['grupo_despesa'])?></td><td><?=e($p['origem_codigo'])?></td><td><?=money($p['valor_liquido'])?></td></tr><?php endforeach;?>
        <?php if(!$disponiveis):?><tr><td colspan="10" class="text-center text-body-secondary py-4">Nenhuma parcela liquidada, atestada e sem grupo.</td></tr><?php endif;?>
      </tbody>
    </table></div>
  </div>
  <?php if($disponiveis):?><div class="card-footer d-flex justify-content-end"><button class="btn btn-dark"><i class="fa-solid fa-object-group me-1"></i>Criar grupo com selecionadas</button></div><?php endif;?>
</form>
<?php endif;?>
<?php unset($tableId,$tablePageSize,$tableFilters,$badge);?>
