<?php
$grupoFiltro=(int)($_GET['cmdf_grupo_id']??0);
$parcelaNumeroFiltro=(int)($_GET['parcela_numero']??0);
if($grupoFiltro>0)$pagamentos=array_values(array_filter($pagamentos,static fn(array $p):bool=>(int)$p['cmdf_grupo_id']===$grupoFiltro));
if($parcelaNumeroFiltro>0)$pagamentos=array_values(array_filter($pagamentos,static fn(array $p):bool=>(int)$p['numero_parcela']===$parcelaNumeroFiltro));
$tableId='pagamentos-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento, fornecedor ou empenho','class'=>'col-12 col-lg-4'],
 ['label'=>'Status','column'=>4,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<?php if($grupoFiltro>0):?><div class="alert alert-info py-2">Exibindo pagamentos do grupo CMDF <strong>#<?=e($grupoFiltro)?></strong>. <a href="/pagamentos">Limpar filtro</a></div><?php endif;?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Fila de Pagamento</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Documento / Parcela</th><th>Fornecedor</th><th>Empenho</th><th>Valor líquido</th><th>Status</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($pagamentos as $p):?><tr data-record-id="<?=e($p['parcela_id'])?>">
        <td><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?></strong><div class="small text-body-secondary">Parcela <?=e($p['numero_parcela'])?></div></td>
        <td><?=e($p['fornecedor'])?></td>
        <td><?=e($p['numero_empenho'])?></td>
        <td class="money"><?=money($p['valor_liquido'])?></td>
        <td><span class="badge <?=$p['status']==='PAGO'?'text-bg-success':'text-bg-warning'?>"><?=e($p['status'])?></span><?php if($p['status']==='PAGO'):?><div class="small text-body-secondary mt-1"><?=e($p['data_pagamento'])?> · <?=money($p['valor_liquido_pago'])?></div><?php endif;?></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions">
          <a href="/documentos/<?=e($p['documento_id'])?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir</a>
          <?php if($p['status']!=='PAGO'):?>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#pagamentoModal" data-pagamento-modal data-pagamento-action="/documentos/<?=e($p['documento_id'])?>/parcelas/<?=e($p['parcela_id'])?>/pagar" data-pagamento-titulo="Pagar <?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?> · Parcela <?=e($p['numero_parcela'])?>" data-pagamento-texto="Empenho <?=e($p['numero_empenho'])?>" data-pagamento-valor="<?=e((string)$p['valor_liquido'])?>"><i class="fa-solid fa-money-check-dollar me-1"></i>Pagar</button>
          <?php else:?>
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/documentos/<?=e($p['documento_id'])?>/parcelas/<?=e($p['parcela_id'])?>/pagamento/desfazer" data-reversao-titulo="Desfazer Pagamento" data-reversao-texto="O pagamento voltará para Aguardando e os dados do pagamento atual serão limpos. A reversão ficará registrada na auditoria." data-reversao-botao="Desfazer pagamento"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer</button>
          <?php endif;?>
        </div></td>
      </tr><?php endforeach;?>
      <?php if(!$pagamentos):?><tr data-table-empty><td colspan="6" class="text-center text-body-secondary py-4">Nenhuma parcela disponível para Pagamento.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php require BASE_PATH.'/app/views/components/pagamento_modal.php';?>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($grupoFiltro,$parcelaNumeroFiltro,$tableId,$tablePageSize,$tableFilters);?>
