<?php
$pageBackUrl='/cmdf';
$pageRightActions=[];
require BASE_PATH.'/app/views/components/page_actions.php';
$status=(string)$grupo['status'];
$badge=$status==='ATENDIDA'?'text-bg-success':($status==='LIBERADA'?'text-bg-primary':'text-bg-secondary');
$valorTotal=0.0;foreach($parcelas as $p)$valorTotal+=(float)$p['valor_liquido'];
?>
<div class="row g-3 mb-3">
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Grupo</div><strong class="fs-5">#<?=e($grupo['id'])?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Status</div><span class="badge <?=$badge?> fs-6"><?=e($status)?></span></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Fonte</div><strong><?=e($grupo['fonte_codigo'])?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Exercício</div><strong><?=e($grupo['exercicio_orcamentario'])?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Sequencial / Grupo</div><strong><?=e($grupo['sequencial'])?> / <?=e($grupo['grupo_despesa'])?></strong></div></div></div>
  <div class="col-md-2"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Origem</div><strong><?=e($grupo['origem_codigo'])?></strong></div></div></div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Parcelas do grupo</h3><strong><?=money($valorTotal)?></strong></div>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>Documento / Parcela</th><th>Fornecedor</th><th>Atesto</th><th>Empenho</th><th>IPOF</th><th>AP Benner</th><th>Valor</th><th>Liquidação</th><?php if($status==='FECHADA'&&Auth::can('cmdf.grupo.ajustar')):?><th class="text-end">Ação</th><?php endif;?></tr></thead>
    <tbody>
      <?php foreach($parcelas as $p):?><tr><td><strong><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?></strong><div class="small">Parcela <?=e($p['numero_parcela'])?></div></td><td><?=e($p['fornecedor'])?></td><td><?=e($p['data_atesto'])?></td><td><?=e($p['numero_empenho'])?></td><td><?=e($p['ipof'])?></td><td><?=e($p['ap_benner'])?></td><td><?=money($p['valor_liquido'])?></td><td><?=e($p['data_liquidacao'])?></td>
      <?php if($status==='FECHADA'&&Auth::can('cmdf.grupo.ajustar')):?><td class="text-end"><form method="post" action="/cmdf/grupos/<?=e($grupo['id'])?>/parcelas/<?=e($p['parcela_id'])?>/remover" class="d-inline"><?=Csrf::field()?><button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover esta parcela do grupo CMDF?')"><i class="fa-solid fa-trash me-1"></i>Remover</button></form></td><?php endif;?></tr><?php endforeach;?>
      <?php if(!$parcelas):?><tr><td colspan="9" class="text-center text-body-secondary py-4">Grupo sem parcelas.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
</div>

<?php if($status==='FECHADA'&&Auth::can('cmdf.grupo.ajustar')):?>
<form method="post" action="/cmdf/grupos/<?=e($grupo['id'])?>/parcelas" class="card mb-3">
  <div class="card-header"><h3 class="card-title">Adicionar parcelas compatíveis</h3></div>
  <div class="card-body p-0"><?=Csrf::field()?>
    <div class="table-responsive"><table class="table table-sm mb-0 align-middle"><thead><tr><th style="width:42px"></th><th>Documento / Parcela</th><th>Atesto</th><th>Fornecedor</th><th>Valor</th></tr></thead><tbody>
      <?php foreach($candidatas as $p):?><tr><td><input class="form-check-input" type="checkbox" name="parcelas_ids[]" value="<?=e($p['parcela_id'])?>"></td><td><?=e($p['tipo_documento'])?> <?=e($p['documento_numero'])?> · Parcela <?=e($p['numero_parcela'])?></td><td><?=e($p['data_atesto'])?></td><td><?=e($p['fornecedor'])?></td><td><?=money($p['valor_liquido'])?></td></tr><?php endforeach;?>
      <?php if(!$candidatas):?><tr><td colspan="5" class="text-center text-body-secondary py-3">Não há outras parcelas compatíveis disponíveis.</td></tr><?php endif;?>
    </tbody></table></div>
  </div>
  <?php if($candidatas):?><div class="card-footer d-flex justify-content-end"><button class="btn btn-outline-dark"><i class="fa-solid fa-plus me-1"></i>Adicionar selecionadas</button></div><?php endif;?>
</form>
<?php endif;?>

<div class="card card-dark card-outline">
  <div class="card-header"><h3 class="card-title">Status conjunto da CMDF</h3></div>
  <div class="card-body">
    <?php if($status==='FECHADA'):?><p class="mb-0">O grupo está <strong>Fechada</strong>. A composição ainda pode ser ajustada por usuário com permissão. Ao liberar, a composição fica bloqueada.</p>
    <?php elseif($status==='LIBERADA'):?><p class="mb-0">O grupo está <strong>Liberada</strong>. A próxima ação é marcar como Atendida. Somente Atendida libera as parcelas para Pagamento.</p>
    <?php else:?><div class="alert alert-success mb-0"><strong>CMDF Atendida.</strong> Todas as parcelas deste grupo foram liberadas individualmente para a fase de Pagamento.</div><?php endif;?>
  </div>
  <?php if($status!=='ATENDIDA'):?><div class="card-footer d-flex justify-content-end"><form method="post" action="/cmdf/grupos/<?=e($grupo['id'])?>/status" class="m-0"><?=Csrf::field()?><input type="hidden" name="status" value="<?=$status==='FECHADA'?'LIBERADA':'ATENDIDA'?>"><button class="btn <?=$status==='FECHADA'?'btn-primary':'btn-success'?>" onclick="return confirm('Confirmar alteração do status conjunto da CMDF?')"><i class="fa-solid fa-arrow-right me-1"></i><?=$status==='FECHADA'?'Liberar grupo':'Marcar como Atendida'?></button></form></div><?php endif;?>
</div>
<?php unset($status,$badge,$valorTotal);?>
