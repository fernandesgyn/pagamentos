<?php
$tableId='cmdf-grupos-table';
$tablePageSize=10;
$tableFilters=[
 ['label'=>'Pesquisa geral','column'=>'*','type'=>'search','placeholder'=>'Grupo, recurso ou chave CMDF','class'=>'col-12 col-lg-4'],
 ['label'=>'Status','column'=>5,'type'=>'select','populate'=>true,'empty'=>'Todos','class'=>'col-12 col-md-4 col-lg-2'],
];
?>
<div class="alert alert-info">
  <strong>Regra de agrupamento CMDF:</strong> a parcela precisa estar Liquidada e seu documento deve possuir Data do atesto. No mesmo grupo, as parcelas devem ter a mesma Fonte de recurso, Exercício orçamentário, Sequencial, Grupo de Despesa e Origem do Recurso.
</div>

<?php if(Auth::can('cmdf.grupo.ajustar')):?>
<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Agrupamento inteligente</h3>
    <form method="post" action="/cmdf/grupos/sugerir" class="m-0"><?=Csrf::field()?><button class="btn btn-sm btn-primary"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Criar grupos sugeridos</button></form>
  </div>
  <div class="card-body">
    <?php if($sugestoes):?>
      <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Chave CMDF</th><th>Parcelas</th><th>Valor</th></tr></thead><tbody>
      <?php foreach($sugestoes as $s):?><tr><td><strong>Fonte <?=e($s['fonte_codigo'])?> · Origem <?=e($s['origem_codigo'])?></strong><div class="small text-body-secondary">Exercício <?=e($s['exercicio_orcamentario'])?> · Seq. <?=e($s['sequencial'])?> · Grupo <?=e($s['grupo_despesa'])?></div></td><td><?=e($s['parcelas_total'])?></td><td><?=money($s['valor_total'])?></td></tr><?php endforeach;?>
      </tbody></table></div>
    <?php else:?><div class="text-body-secondary">Não há novas sugestões. Todas as parcelas elegíveis já estão agrupadas ou não há parcelas aptas.</div><?php endif;?>
  </div>
</div>
<?php endif;?>

<div class="card mb-3" data-admin-table data-page-size="<?=$tablePageSize?>">
  <div class="card-header"><h3 class="card-title">Grupos CMDF</h3></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_filters.php';?>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">
    <thead><tr><th>Grupo</th><th>Recursos</th><th>Exercício</th><th>Chave</th><th>Composição</th><th>Status</th><th class="text-end portal-actions-cell" data-table-nosort>Ações</th></tr></thead>
    <tbody>
      <?php foreach($grupos as $g):?><?php $badge=$g['status']==='ATENDIDA'?'text-bg-success':($g['status']==='LIBERADA'?'text-bg-primary':'text-bg-secondary');?>
      <tr data-record-id="<?=e($g['id'])?>">
        <td><strong>#<?=e($g['id'])?></strong><div class="small text-body-secondary"><?=$g['gerado_automaticamente']?'Automático':'Manual'?></div></td>
        <td><strong>Fonte <?=e($g['fonte_codigo'])?></strong><div class="small text-body-secondary">Origem <?=e($g['origem_codigo'])?></div></td>
        <td><?=e($g['exercicio_orcamentario'])?></td>
        <td>Seq. <?=e($g['sequencial'])?><div class="small text-body-secondary">Grupo <?=e($g['grupo_despesa'])?></div></td>
        <td><strong><?=e($g['parcelas_total'])?> parcela(s)</strong><div class="small text-body-secondary"><?=money($g['valor_total'])?></div></td>
        <td><span class="badge <?=$badge?>"><?=e($g['status'])?></span></td>
        <td class="text-end portal-actions-cell"><div class="portal-action-group portal-table-actions">
          <a href="/cmdf/grupos/<?=e($g['id'])?>" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-eye me-1"></i>Ver</a>
          <?php if($g['status']==='FECHADA'):?>
            <?php if(Auth::can('cmdf.grupo.ajustar')):?><a href="/cmdf/grupos/<?=e($g['id'])?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-list-check me-1"></i>Parcelas</a><?php endif;?>
            <form method="post" action="/cmdf/grupos/<?=e($g['id'])?>/status" class="d-inline m-0" onsubmit="return confirm('Liberar este grupo CMDF? A composição ficará bloqueada até que a liberação seja desfeita.')"><?=Csrf::field()?><input type="hidden" name="status" value="LIBERADA"><button class="btn btn-sm btn-primary"><i class="fa-solid fa-lock me-1"></i>Liberar</button></form>
          <?php elseif($g['status']==='LIBERADA'):?>
            <form method="post" action="/cmdf/grupos/<?=e($g['id'])?>/status" class="d-inline m-0" onsubmit="return confirm('Marcar este grupo CMDF como Atendida e liberar suas parcelas para Pagamento?')"><?=Csrf::field()?><input type="hidden" name="status" value="ATENDIDA"><button class="btn btn-sm btn-success"><i class="fa-solid fa-check me-1"></i>Atender</button></form>
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/cmdf/grupos/<?=e($g['id'])?>/desfazer" data-reversao-titulo="Desfazer liberação da CMDF" data-reversao-texto="O grupo voltará para Fechada e sua composição poderá ser alterada novamente." data-reversao-botao="Voltar para Fechada"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer</button>
          <?php else:?>
            <a href="/pagamentos?cmdf_grupo_id=<?=e($g['id'])?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-money-check-dollar me-1"></i>Pagamentos</a>
            <?php if((int)$g['pagamentos_movimentados']===0):?>
              <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reversaoModal" data-reversao-modal data-reversao-action="/cmdf/grupos/<?=e($g['id'])?>/desfazer" data-reversao-titulo="Desfazer atendimento da CMDF" data-reversao-texto="O grupo voltará para Liberada e os pagamentos ainda não movimentados serão retirados da fila." data-reversao-botao="Voltar para Liberada"><i class="fa-solid fa-rotate-left me-1"></i>Desfazer</button>
            <?php else:?>
              <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Há etapa posterior movimentada. Desfaça-a antes de voltar a CMDF"><i class="fa-solid fa-lock me-1"></i>Desfazer</button>
            <?php endif;?>
          <?php endif;?>
        </div></td>
      </tr>
      <?php endforeach;?>
      <?php if(!$grupos):?><tr data-table-empty><td colspan="7" class="text-center text-body-secondary py-4">Nenhum grupo CMDF criado.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
  <?php require BASE_PATH.'/app/views/components/admin_table_footer.php';?>
</div>

<?php if(Auth::can('cmdf.grupo.ajustar')):?>
<form method="post" action="/cmdf/grupos" class="card">
  <div class="card-header"><h3 class="card-title">Criar grupo manualmente</h3></div>
  <div class="card-body p-0">
    <?=Csrf::field()?>
    <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
      <thead><tr><th style="width:42px"></th><th>Documento / Parcela</th><th>Fornecedor</th><th>Atesto</th><th>Chave CMDF</th><th>Valor</th></tr></thead>
      <tbody>
        <?php foreach($disponiveis as $p):?><tr><td><input class="form-check-input" type="checkbox" name="parcelas_ids[]" value="<?=e($p['parcela_id'])?>"></td><td><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?></strong><div class="small">Parcela <?=e($p['numero_parcela'])?></div></td><td><?=e($p['fornecedor'])?></td><td><?=e($p['data_atesto'])?></td><td><strong>Fonte <?=e($p['fonte_codigo'])?> · Origem <?=e($p['origem_codigo'])?></strong><div class="small text-body-secondary">Exercício <?=e($p['exercicio_orcamentario'])?> · Seq. <?=e($p['sequencial'])?> · Grupo <?=e($p['grupo_despesa'])?></div></td><td><?=money($p['valor_liquido'])?></td></tr><?php endforeach;?>
        <?php if(!$disponiveis):?><tr><td colspan="6" class="text-center text-body-secondary py-4">Nenhuma parcela liquidada, atestada e sem grupo.</td></tr><?php endif;?>
      </tbody>
    </table></div>
  </div>
  <?php if($disponiveis):?><div class="card-footer d-flex justify-content-end"><button class="btn btn-dark"><i class="fa-solid fa-object-group me-1"></i>Criar grupo com selecionadas</button></div><?php endif;?>
</form>
<?php endif;?>
<?php require BASE_PATH.'/app/views/components/reversao_modal.php';?>
<?php unset($tableId,$tablePageSize,$tableFilters,$badge);?>
