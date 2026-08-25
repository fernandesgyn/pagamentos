<?php
$grupoFiltro=(int)($_GET['cmdf_grupo_id']??0);
$parcelaNumeroFiltro=(int)($_GET['parcela_numero']??0);
$tableId='pagamentos-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento, fornecedor, empenho, IPOF ou AP Benner','initial'=>$parcelaNumeroFiltro>0?'Parcela '.$parcelaNumeroFiltro:'','class'=>'col-12 col-lg-4'],
 ['label'=>'Status','column'=>8,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Grupo CMDF','column'=>9,'type'=>'select','populate'=>true,'empty'=>'Todos','initial'=>$grupoFiltro>0?'#'.$grupoFiltro:'','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Fila de Pagamento</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Documento / Parcela</th><th>Fornecedor</th><th>Empenho</th><th>IPOF</th><th>AP Benner</th><th>Seq.</th><th>Grupo Desp.</th><th>Valor líquido</th><th>Status</th><th>Grupo CMDF</th><th style="min-width:360px">Pagamento</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($pagamentos as $p):?><tr data-record-id="<?=e($p['parcela_id'])?>">
        <td><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?></strong><div class="small text-body-secondary">Parcela <?=e($p['numero_parcela'])?></div></td>
        <td><?=e($p['fornecedor'])?></td><td><?=e($p['numero_empenho'])?></td><td><?=e($p['ipof'])?></td><td><?=e($p['ap_benner'])?></td><td><?=e($p['sequencial'])?></td><td><?=e($p['grupo_despesa'])?></td><td class="money"><?=money($p['valor_liquido'])?></td>
        <td><span class="badge <?=$p['status']==='PAGO'?'text-bg-success':'text-bg-warning'?>"><?=e($p['status'])?></span></td><td><a href="/cmdf/grupos/<?=e($p['cmdf_grupo_id'])?>">#<?=e($p['cmdf_grupo_id'])?></a></td>
        <td>
          <?php if($p['status']!=='PAGO'):?>
            <form method="post" action="/documentos/<?=e($p['documento_id'])?>/parcelas/<?=e($p['parcela_id'])?>/pagar" class="row g-1">
              <?=Csrf::field()?>
              <div class="col-md-4"><input type="date" name="data_pagamento" value="<?=date('Y-m-d')?>" class="form-control form-control-sm" required></div>
              <div class="col-md-4"><input name="valor_liquido_pago" class="form-control form-control-sm" placeholder="Valor líquido" value="<?=e((string)$p['valor_liquido'])?>"></div>
              <div class="col-md-4"><button class="btn btn-success btn-sm w-100"><i class="fa-solid fa-money-check-dollar me-1"></i>Pagar</button></div>
              <div class="col-12 mt-1"><input name="historico_pagamento" class="form-control form-control-sm" placeholder="Histórico do pagamento"></div>
            </form>
          <?php else:?>
            <div><strong><?=e($p['data_pagamento'])?></strong> · <?=money($p['valor_liquido_pago'])?></div><div class="small text-body-secondary"><?=e($p['historico_pagamento']??'')?></div>
          <?php endif;?>
        </td>
        <td class="text-end portal-actions-cell" style="min-width:220px">
          <div class="portal-action-group portal-table-actions justify-content-end">
            <a href="/documentos/<?=e($p['documento_id'])?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir</a>
            <?php if($p['status']==='PAGO'):?>
              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/documentos/<?=e($p['documento_id'])?>/parcelas/<?=e($p['parcela_id'])?>/pagamento/desfazer" data-reversao-titulo="Desfazer Pagamento" data-reversao-texto="O pagamento voltará para Aguardando e os dados do pagamento atual serão limpos. A reversão ficará registrada na auditoria." data-reversao-botao="Desfazer pagamento"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer pagamento</button>
            <?php endif;?>
          </div>
        </td>
      </tr><?php endforeach;?>
      <?php if(!$pagamentos):?><tr data-table-empty><td colspan="12" class="text-center text-body-secondary py-4">Nenhuma parcela liberada por grupo CMDF Atendida.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($grupoFiltro,$parcelaNumeroFiltro,$tableId,$tablePageSize,$tableFilters);?>
