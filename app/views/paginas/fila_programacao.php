<?php
$tableId='programacao-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento ou fornecedor','class'=>'col-12 col-lg-4'],
 ['label'=>'Situação','column'=>4,'type'=>'select','populate'=>true,'empty'=>'Todas','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Documentos liberados para Programação</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Documento</th><th>Fornecedor</th><th>Valor líquido</th><th>Programação</th><th>Situação</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($itens as $i):?><?php $fechado=round((float)$i['saldo_programar'],2)===0.0;?><tr data-record-id="<?=e($i['documento_id'])?>">
        <td><strong><?=e($i['tipo_documento'])?> <?=e($i['documento_numero'])?></strong><div class="small text-body-secondary"><?=e($i['parcelas_total'])?> parcela(s)</div></td>
        <td><?=e($i['fornecedor'])?></td>
        <td><?=money($i['valor_liquido'])?></td>
        <td><strong><?=money($i['valor_programado'])?></strong><div class="small text-body-secondary">Saldo: <?=money($i['saldo_programar'])?></div></td>
        <td><span class="badge <?=$fechado?'text-bg-success':'text-bg-warning'?>"><?=$fechado?'Fechada':'A programar'?></span></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions">
          <a href="/programacao/<?=e($i['documento_id'])?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i><?=$fechado?'Visualizar':'Programar'?></a>
          <?php if((int)$i['parcelas_total']===1 && !empty($i['parcela_unica_reversivel_id'])):?>
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/programacao/<?=e($i['documento_id'])?>/parcelas/<?=e($i['parcela_unica_reversivel_id'])?>/desfazer" data-reversao-titulo="Desfazer Programação" data-reversao-texto="A única parcela programada será removida e o saldo integral do documento será reaberto para correção." data-reversao-botao="Desfazer programação"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer</button>
          <?php elseif((int)$i['parcelas_total']>0):?>
            <a href="/programacao/<?=e($i['documento_id'])?>" class="btn btn-sm btn-outline-danger" title="Escolha a parcela que precisa ser corrigida"><i class="fa-solid fa-pen-to-square me-1"></i>Corrigir parcelas</a>
          <?php endif;?>
        </div></td>
      </tr><?php endforeach;?>
      <?php if(!$itens):?><tr data-table-empty><td colspan="6" class="text-center text-body-secondary py-4">Nenhum documento liberado para programação.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($tableId,$tablePageSize,$tableFilters,$fechado);?>
