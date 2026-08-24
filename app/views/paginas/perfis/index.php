<?php
$tableId='perfis-table';$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Nome ou descrição','class'=>'col-12 col-lg-4'],
 ['label'=>'Ativo','column'=>4,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
$pageRightActions=[[ 'href'=>'/perfis/novo','label'=>'Novo perfil','icon'=>'fa-plus','class'=>'btn btn-sm btn-primary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Perfis cadastrados</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Perfil</th><th>Descrição</th><th>Usuários</th><th>Permissões</th><th>Ativo</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
    <?php foreach($perfis as $p):?><?php $protegido=(int)$p['id']===1;?><tr data-record-id="<?=e($p['id'])?>"><td><strong><?=e($p['nome'])?></strong><?php if($protegido):?> <span class="badge text-bg-secondary">Protegido</span><?php endif;?></td><td><?=e($p['descricao']??'-')?></td><td><?=e($p['usuarios_total'])?></td><td><?=e($p['permissoes_total'])?></td><td><?=$p['ativo']?'Sim':'Não'?></td><td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions justify-content-end"><a class="btn btn-sm btn-outline-primary" href="/perfis/<?=e($p['id'])?>/editar"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><?=$protegido?'Visualizar':'Editar'?></a><?php if(!$protegido):?><form method="post" action="/perfis/<?=e($p['id'])?>/excluir" onsubmit="return confirm('Excluir este perfil?')"><?=Csrf::field()?><button class="btn btn-sm btn-outline-danger" type="submit" <?=((int)$p['usuarios_total']>0?'disabled title="Há usuários vinculados"':'')?>><i class="fa-solid fa-trash" aria-hidden="true"></i>Excluir</button></form><?php endif;?></div></td></tr><?php endforeach;?>
    <?php if(!$perfis):?><tr data-table-empty><td colspan="6" class="text-center text-body-secondary py-4">Nenhum perfil cadastrado.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php unset($tableId,$tablePageSize,$tableFilters,$protegido);?>
