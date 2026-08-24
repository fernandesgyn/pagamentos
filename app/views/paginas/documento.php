<?php
$pageBackUrl='/documentos';
$pageRightActions=[];
if(Auth::can('inspecao.gerir'))$pageRightActions[]=['href'=>'/inspecoes/'.$doc['id'],'label'=>'Inspeção','icon'=>'fa-magnifying-glass','class'=>'btn btn-sm btn-outline-primary'];
if(Auth::can('parcela.gerir')&&($doc['permite_avancar']??false))$pageRightActions[]=['href'=>'/programacao/'.$doc['id'],'label'=>'Programação','icon'=>'fa-list-check','class'=>'btn btn-sm btn-primary'];
require BASE_PATH.'/app/views/components/page_actions.php';
?>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Documento</div><div class="fw-bold fs-5"><?=e($doc['tipo_documento'])?> <?=e($doc['numero'])?></div><div>Emissão: <?=e($doc['data_emissao'])?></div></div></div></div>
  <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Fornecedor</div><div class="fw-bold"><?=e($doc['fornecedor'])?></div><div><?=e($doc['fornecedor_documento'])?> · <?=e($doc['fornecedor_tipo'])?></div></div></div></div>
  <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Obrigação</div><div class="fw-bold"><?=e($doc['obrigacao_numero'])?>/<?=e($doc['obrigacao_ano'])?></div><div>SEI: <?=e($doc['nr_sei_contratacao']??'—')?></div></div></div></div>
  <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="small text-body-secondary">Inspeção</div><div class="fw-bold"><?=e($doc['status_inspecao']??'Aguardando inspeção')?></div><span class="badge <?=$doc['permite_avancar']?'text-bg-success':'text-bg-warning'?>"><?=$doc['permite_avancar']?'Liberada para programação':'Ainda não liberada'?></span></div></div></div>
</div>

<div class="card mb-3">
  <div class="card-header"><h3 class="card-title">Dados financeiros e datas</h3></div>
  <div class="card-body"><div class="row g-3">
    <div class="col-md-3"><div class="small text-body-secondary">Valor bruto</div><strong><?=money($doc['valor_bruto'])?></strong></div>
    <div class="col-md-3"><div class="small text-body-secondary">Valor líquido</div><strong><?=money($doc['valor_liquido'])?></strong></div>
    <div class="col-md-2"><div class="small text-body-secondary">Data do atesto</div><strong><?=e($doc['data_atesto']??'—')?></strong></div>
    <div class="col-md-2"><div class="small text-body-secondary">Envio à COOINSP</div><strong><?=e($doc['data_envio_cooinsp']??'—')?></strong></div>
    <div class="col-md-2"><div class="small text-body-secondary">Lançamento</div><strong><?=e($doc['data_lancamento'])?></strong></div>
  </div></div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Parcelas</h3><span class="badge <?=$programacaoFechada?'text-bg-success':'text-bg-secondary'?>"><?=$programacaoFechada?'Programação fechada':'Programação incompleta'?></span></div>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>Parcela</th><th>Empenho</th><th>Natureza</th><th>Fonte</th><th>Recurso</th><th>Tipo</th><th>Valor líquido</th><th>Liquidação</th><th>CMDF</th><th>Pagamento</th><th class="text-end">Ações</th></tr></thead>
    <tbody>
      <?php foreach($parcelas as $p):?><tr>
        <td><strong><?=e($p['numero_parcela'])?></strong></td>
        <td><?=e($p['numero_empenho'])?></td>
        <td><?=e($p['natureza_codigo'])?></td>
        <td><?=e($p['fonte_codigo'])?></td>
        <td><?=e($p['tipo_recurso_codigo'])?></td>
        <td><?=e($p['tipo'])?></td>
        <td class="money"><?=money($p['valor_liquido'])?></td>
        <td><span class="badge <?=($p['status_liquidacao']??'')==='LIQUIDADA'?'text-bg-success':'text-bg-secondary'?>"><?=e($p['status_liquidacao']??'AGUARDANDO')?></span></td>
        <td><span class="badge <?=($p['status_cmdf']??'')==='LIQUIDADA'?'text-bg-success':'text-bg-secondary'?>"><?=e($p['status_cmdf']??'—')?></span></td>
        <td><span class="badge <?=($p['status_pagamento']??'')==='PAGO'?'text-bg-success':'text-bg-secondary'?>"><?=e($p['status_pagamento']??'—')?></span></td>
        <td class="text-end"><div class="portal-action-group portal-table-actions justify-content-end">
          <?php if(Auth::can('liquidacao.gerir')):?><a href="/liquidacoes/<?=e($p['id'])?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check-double"></i>Liquidação</a><?php endif;?>
          <?php if(Auth::can('cmdf.gerir')&&($p['status_liquidacao']??'')==='LIQUIDADA'):?><a href="/cmdf/<?=e($p['id'])?>" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-building-columns"></i>CMDF</a><?php endif;?>
        </div></td>
      </tr><?php endforeach;?>
      <?php if(!$parcelas):?><tr><td colspan="11" class="text-center text-body-secondary py-4">Nenhuma parcela cadastrada.</td></tr><?php endif;?>
    </tbody>
  </table></div></div>
</div>
