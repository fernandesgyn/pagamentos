<?php
$tableId='cadastro-'.preg_replace('/[^a-zA-Z0-9_-]+/','-',trim((string)$baseUrl,'/'));
$tablePageSize=10;
$tableFilters=[['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Pesquisar em todos os campos','class'=>'col-12 col-lg-4']];
$novoPorRota=[
  '/fornecedores'=>['/fornecedores/novo','Novo fornecedor'],
  '/fontes-recurso'=>['/fontes-recurso/nova','Nova fonte de recurso'],
  '/naturezas-despesa'=>['/naturezas-despesa/nova','Nova natureza da despesa'],
  '/origens-recurso'=>['/origens-recurso/nova','Nova origem do recurso'],
  '/tipos-documento'=>['/tipos-documento/novo','Novo tipo de documento'],
  '/tipos-obrigacao'=>['/tipos-obrigacao/novo','Novo tipo de obrigação'],
];
[$novoUrl,$novoLabel]=$novoPorRota[$baseUrl]??[$baseUrl.'/novo','Novo registro'];
$pageRightActions=[[ 'href'=>$novoUrl,'label'=>$novoLabel,'icon'=>'fa-plus','class'=>'btn btn-sm btn-primary' ]];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="card" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title"><?=e($titulo)?></h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><?php foreach($campos as $campo=>$label):?><th><?=e($label)?></th><?php endforeach;?><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
    <?php foreach($registros as $r):?><tr data-record-id="<?=e($r['id'])?>">
      <?php foreach($campos as $campo=>$label):?>
        <?php
          $valor=$r[$campo]??'';
          if(in_array($campo,['ativo','exige_numero','exige_numero_ano'],true))$valor=((int)$valor===1?'Sim':'Não');
          if($campo==='tipo_pessoa')$valor=$valor==='PF'?'Pessoa Física':($valor==='PJ'?'Pessoa Jurídica':$valor);
        ?>
        <td><?=e((string)$valor)?></td>
      <?php endforeach;?>
      <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions justify-content-end">
        <a class="btn btn-sm btn-outline-primary" href="<?=e($baseUrl)?>/<?=e($r['id'])?>/editar"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>Editar</a>
        <?php if((bool)($r['em_uso']??false)):?>
          <button class="btn btn-sm btn-outline-danger" type="button" disabled aria-disabled="true" title="Registro em uso. A exclusão não é permitida." data-delete-disabled><i class="fa-solid fa-trash" aria-hidden="true"></i>Excluir</button>
        <?php else:?>
          <form method="post" action="<?=e($baseUrl)?>/<?=e($r['id'])?>/excluir" onsubmit="return confirm('Excluir este registro?')"><?=Csrf::field()?><button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash" aria-hidden="true"></i>Excluir</button></form>
        <?php endif;?>
      </div></td>
    </tr><?php endforeach;?>
    <?php if(!$registros):?><tr data-table-empty><td colspan="<?=count($campos)+1?>" class="text-center text-body-secondary py-4">Nenhum registro cadastrado.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>
<?php unset($tableId,$tablePageSize,$tableFilters,$novoPorRota,$novoUrl,$novoLabel,$valor);?>
