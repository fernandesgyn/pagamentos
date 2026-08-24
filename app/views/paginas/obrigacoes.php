<?php
$tableId='obrigacoes-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Tipo, número, fornecedor, objeto ou SEI','class'=>'col-12 col-lg-4'],
 ['label'=>'Tipo','column'=>0,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Fornecedor','column'=>2,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
$pageRightActions=[[ 'href'=>'/obrigacoes/nova','label'=>'Nova obrigação','icon'=>'fa-plus','class'=>'btn btn-sm btn-primary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Obrigações cadastradas</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Tipo</th><th>Nº/Ano</th><th>Fornecedor</th><th>Valor</th><th>Objeto</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($obrigacoes as $o):?><tr data-record-id="<?=e($o['id'])?>">
        <td><?=e($o['tipo'])?></td><td><strong><?=e($o['numero'])?>/<?=e($o['ano'])?></strong></td><td><?=e($o['fornecedor'])?></td><td class="money"><?=money($o['valor_global'])?></td><td title="<?=e((string)$o['objeto'])?>"><?=e(mb_strimwidth((string)$o['objeto'],0,60,'…'))?></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions justify-content-end"><a href="/documentos/novo?obrigacao_id=<?=e($o['id'])?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-receipt" aria-hidden="true"></i>Novo documento</a></div></td>
      </tr><?php endforeach;?>
      <?php if(!$obrigacoes):?><tr data-table-empty><td colspan="6" class="text-center text-body-secondary py-4">Nenhuma obrigação cadastrada.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php unset($tableId,$tablePageSize,$tableFilters);?>
