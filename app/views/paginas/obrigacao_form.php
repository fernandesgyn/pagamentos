<?php
$pageBackUrl='/obrigacoes';
require BASE_PATH.'/app/views/components/page_actions.php';
$fornecedorSelecionado=(int)($_GET['fornecedor_id']??0);
?>
<form method="post" action="/obrigacoes" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados da obrigação</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Tipo *</label><select name="tipo_obrigacao_id" class="form-select" required><option value="">Selecione</option><?php foreach($tipos as $t):?><option value="<?=e($t['id'])?>"><?=e($t['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-6"><label class="form-label">Fornecedor *</label><select name="fornecedor_id" class="form-select" required><option value="">Selecione</option><?php foreach($fornecedores as $f):?><option value="<?=e($f['id'])?>" <?=((int)$f['id']===$fornecedorSelecionado)?'selected':''?>><?=e($f['nome'])?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">Número *</label><input name="numero" class="form-control" required></div>
      <div class="col-md-2"><label class="form-label">Ano *</label><input name="ano" type="number" min="2000" max="2100" value="<?=date('Y')?>" class="form-control" required></div>
      <div class="col-md-3"><label class="form-label">Valor global</label><input name="valor_global" class="form-control" placeholder="0,00"></div>
      <div class="col-md-3"><label class="form-label">SEI</label><input name="sei" class="form-control"></div>
      <div class="col-12"><label class="form-label">Objeto</label><textarea name="objeto" class="form-control" rows="4"></textarea></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/obrigacoes" class="btn btn-outline-secondary">Cancelar</a><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Salvar</button></div>
</form>
<?php unset($fornecedorSelecionado);?>
