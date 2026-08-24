<?php
$tableId='usuarios-table';$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Nome, login ou e-mail','class'=>'col-12 col-lg-4'],
 ['label'=>'Perfil','column'=>3,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
 ['label'=>'Ativo','column'=>4,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
$pageRightActions=[[ 'href'=>'/usuarios/novo','label'=>'Novo usuário','icon'=>'fa-plus','class'=>'btn btn-sm btn-primary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Usuários cadastrados</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Nome</th><th>Login</th><th>E-mail</th><th>Perfil</th><th>Ativo</th><th>Trocar senha</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
    <?php foreach($usuarios as $u):?><tr data-record-id="<?=e($u['id'])?>"><td><?=e($u['nome'])?></td><td><?=e($u['login'])?></td><td><?=e($u['email'])?></td><td><?=e($u['perfil'])?></td><td><?=$u['ativo']?'Sim':'Não'?></td><td><?=$u['trocar_senha']?'Sim':'Não'?></td><td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions justify-content-end"><a class="btn btn-sm btn-outline-primary" href="/usuarios/<?=e($u['id'])?>/editar"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>Editar</a><form method="post" action="/usuarios/<?=e($u['id'])?>/excluir" onsubmit="return confirm('Excluir usuário?')"><?=Csrf::field()?><button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash" aria-hidden="true"></i>Excluir</button></form></div></td></tr><?php endforeach;?>
    <?php if(!$usuarios):?><tr data-table-empty><td colspan="7" class="text-center text-body-secondary py-4">Nenhum usuário cadastrado.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php unset($tableId,$tablePageSize,$tableFilters);?>
