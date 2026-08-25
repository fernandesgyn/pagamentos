<?php
$tableId='liquidacoes-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento, fornecedor, empenho, IPOF ou AP Benner','class'=>'col-12 col-lg-4'],
 ['label'=>'Status','column'=>11,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Parcelas para Liquidação</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Documento / Parcela</th><th>Fornecedor</th><th>Empenho</th><th>Fonte</th><th>Origem</th><th>Exercício</th><th>Seq.</th><th>Grupo Desp.</th><th>IPOF</th><th>AP Benner</th><th>Valor</th><th>Status</th><th>CMDF</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($itens as $i):?><tr data-record-id="<?=e($i['parcela_id'])?>">
        <td><strong><?=e($i['tipo_documento'])?> <?=e($i['documento_numero'])?></strong><div class="small text-body-secondary">Parcela <?=e($i['numero_parcela'])?></div></td>
        <td><?=e($i['fornecedor'])?></td><td><?=e($i['numero_empenho'])?></td><td><?=e($i['fonte_codigo'])?></td><td><?=e($i['origem_codigo'])?></td><td><?=e($i['exercicio_orcamentario'])?></td><td><?=e($i['sequencial'])?></td><td><?=e($i['grupo_despesa'])?></td><td><?=e($i['ipof'])?></td><td><?=e($i['ap_benner'])?></td><td><?=money($i['valor_liquido'])?></td>
        <td><span class="badge <?=$i['status']==='LIQUIDADA'?'text-bg-success':($i['status']==='AGUARDANDO'?'text-bg-warning':'text-bg-secondary')?>"><?=e($i['status'])?></span></td>
        <td><?=!empty($i['cmdf_grupo_id'])?'Grupo #'.e($i['cmdf_grupo_id']).' · '.e($i['status_cmdf']):'—'?></td>
        <td class="text-end portal-actions-cell"><a href="/liquidacoes/<?=e($i['parcela_id'])?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check-double me-1"></i><?=$i['status']==='AGUARDANDO'?'Liquidar':'Abrir'?></a></td>
      </tr><?php endforeach;?>
      <?php if(!$itens):?><tr data-table-empty><td colspan="14" class="text-center text-body-secondary py-4">Fila vazia.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php unset($tableId,$tablePageSize,$tableFilters);?>
