<?php
$tableId='cmdf-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento, fornecedor ou empenho','class'=>'col-12 col-lg-4'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Status CMDF','column'=>6,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Parcelas na CMDF</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Documento / Parcela</th><th>Fornecedor</th><th>Empenho</th><th>Tipo</th><th>Valor líquido</th><th>Data liquidação</th><th>Status CMDF</th><th>Conclusão</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($itens as $i):?><tr data-record-id="<?=e($i['parcela_id'])?>">
        <td><strong><?=e($i['tipo_documento'])?> <?=e($i['documento_numero'])?></strong><div class="small text-body-secondary">Parcela <?=e($i['numero_parcela'])?></div></td>
        <td><?=e($i['fornecedor'])?></td>
        <td><?=e($i['numero_empenho'])?></td>
        <td><?=e($i['tipo'])?></td>
        <td><?=money($i['valor_liquido'])?></td>
        <td><?=e($i['data_liquidacao']??'—')?></td>
        <td><span class="badge <?=$i['status']==='LIQUIDADA'?'text-bg-success':($i['status']==='AGUARDANDO'?'text-bg-warning':'text-bg-secondary')?>"><?=e($i['status'])?></span></td>
        <td><?=e($i['data_conclusao']??'—')?></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions justify-content-end"><a href="/cmdf/<?=e($i['parcela_id'])?>" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-building-columns" aria-hidden="true"></i><?=$i['status']==='AGUARDANDO'?'Processar':'Abrir'?></a></div></td>
      </tr><?php endforeach;?>
      <?php if(!$itens):?><tr data-table-empty><td colspan="9" class="text-center text-body-secondary py-4">Fila CMDF vazia.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php unset($tableId,$tablePageSize,$tableFilters);?>
