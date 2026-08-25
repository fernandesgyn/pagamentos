<?php
$tableId='documentos-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Documento, fornecedor ou obrigação','class'=>'col-12 col-lg-4'],
 ['label'=>'Fornecedor','column'=>1,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
$pageRightActions=[[ 'href'=>'/documentos/novo','label'=>'Novo documento','icon'=>'fa-plus','class'=>'btn btn-sm btn-primary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Documentos lançados</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive">
    <table class="table table-hover table-striped mb-0 align-middle">
      <thead><tr><th>Documento</th><th>Fornecedor</th><th>Obrigação</th><th>Valores</th><th>Emissão</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
      <tbody>
      <?php foreach($documentos as $d):?><tr data-record-id="<?=e($d['id'])?>">
        <td><strong><?=e($d['tipo_documento'])?> <?=e($d['numero'])?></strong></td>
        <td><?=e($d['fornecedor'])?></td>
        <td><?=e($d['obrigacao_numero'])?>/<?=e($d['obrigacao_ano'])?></td>
        <td><strong><?=money($d['valor_liquido'])?></strong><div class="small text-body-secondary">Bruto: <?=money($d['valor_bruto'])?></div></td>
        <td><?=e($d['data_emissao'])?></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions">
          <a href="/documentos/<?=e($d['id'])?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Abrir</a>
          <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/documentos/<?=e($d['id'])?>/excluir" data-reversao-titulo="Excluir Documento" data-reversao-texto="O Documento só será excluído se não houver Programação e se sua Inspeção não estiver encerrada. Desfaça as etapas posteriores antes da exclusão." data-reversao-botao="Excluir documento"><i class="fa-solid fa-trash me-1"></i>Excluir</button>
        </div></td>
      </tr><?php endforeach;?>
      <?php if(!$documentos):?><tr data-table-empty><td colspan="6" class="text-center text-body-secondary py-4">Nenhum documento lançado.</td></tr><?php endif;?>
      </tbody>
    </table>
  </div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($tableId,$tablePageSize,$tableFilters);?>
