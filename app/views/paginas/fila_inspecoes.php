<?php
$tableId='inspecoes-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento ou fornecedor','class'=>'col-12 col-lg-4'],
 ['label'=>'Status','column'=>4,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Fila de Inspeção</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Documento</th><th>Fornecedor</th><th>Valor líquido</th><th>Envio COOINSP</th><th>Status</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($itens as $i):?><tr data-record-id="<?=e($i['documento_id'])?>">
        <td><strong><?=e($i['tipo_documento'])?> <?=e($i['documento_numero'])?></strong><div class="small text-body-secondary">Emissão: <?=e($i['data_emissao'])?></div></td>
        <td><?=e($i['fornecedor'])?></td>
        <td><?=money($i['valor_liquido'])?></td>
        <td><?=e($i['data_envio_cooinsp']??'—')?></td>
        <td><span class="badge <?=$i['permite_avancar']?'text-bg-success':'text-bg-warning'?>"><?=e($i['status_inspecao'])?></span></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions">
          <a href="/inspecoes/<?=e($i['documento_id'])?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1" aria-hidden="true"></i>Inspecionar</a>
          <?php if((int)$i['encerra_inspecao']===1):?>
            <?php if((int)$i['parcelas_total']===0):?>
              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/inspecoes/<?=e($i['documento_id'])?>/desfazer" data-reversao-titulo="Desfazer conclusão da Inspeção" data-reversao-texto="O documento voltará para Inspeção andamento. A reversão ficará registrada na auditoria." data-reversao-botao="Desfazer conclusão"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer</button>
            <?php else:?>
              <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Desfaça a Programação antes de reabrir a Inspeção"><i class="fa-solid fa-lock me-1"></i>Desfazer</button>
            <?php endif;?>
          <?php endif;?>
        </div></td>
      </tr><?php endforeach;?>
      <?php if(!$itens):?><tr data-table-empty><td colspan="6" class="text-center text-body-secondary py-4">Fila vazia.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($tableId,$tablePageSize,$tableFilters);?>
