<?php
$pageBackUrl='/obrigacoes';
require BASE_PATH.'/app/views/components/page_actions.php';
$fornecedorSelecionado=(int)($_GET['fornecedor_id']??0);
?>
<?php if(!$fontes||!$naturezas):?>
<div class="alert alert-warning">
  Antes de cadastrar uma obrigação, cadastre ao menos uma <a href="/fontes-recurso" class="alert-link">Fonte de recurso</a> e uma <a href="/naturezas-despesa" class="alert-link">Natureza da despesa</a>.
</div>
<?php endif;?>
<form method="post" action="/obrigacoes" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados da obrigação</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Tipo *</label>
        <select name="tipo_obrigacao_id" class="form-select" required>
          <option value="">Selecione</option>
          <?php foreach($tipos as $t):?><option value="<?=e($t['id'])?>"><?=e($t['nome'])?></option><?php endforeach;?>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label">Pesquisar fornecedor</label>
        <input type="search" class="form-control mb-2" placeholder="Digite Razão Social/Nome ou CPF/CNPJ" data-select-search="#fornecedor_id">
        <label class="form-label">Fornecedor *</label>
        <select id="fornecedor_id" name="fornecedor_id" class="form-select" required>
          <option value="">Selecione</option>
          <?php foreach($fornecedores as $f):?>
            <option value="<?=e($f['id'])?>" <?=((int)$f['id']===$fornecedorSelecionado)?'selected':''?>><?=e($f['razao_social'])?> — <?=e($f['documento'])?> (<?=e($f['tipo_pessoa'])?>)</option>
          <?php endforeach;?>
        </select>
      </div>

      <div class="col-md-4"><label class="form-label">Número *</label><input name="numero" class="form-control" required></div>
      <div class="col-md-2"><label class="form-label">Ano *</label><input name="ano" type="number" min="2000" max="2100" value="<?=date('Y')?>" class="form-control" required></div>
      <div class="col-md-3"><label class="form-label">Valor Total da Obrigação *</label><input name="valor_total" class="form-control" placeholder="0,00" required></div>
      <div class="col-md-3"><label class="form-label">Nr. SEI da Contratação</label><input name="nr_sei_contratacao" class="form-control" maxlength="50"></div>
      <div class="col-md-3"><label class="form-label">Data início</label><input type="date" name="data_inicio" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Data fim</label><input type="date" name="data_fim" class="form-control"></div>

      <div class="col-12" data-repeat-select>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label class="form-label mb-0">Fontes de recurso *</label>
          <button type="button" class="btn btn-sm btn-outline-primary" data-repeat-add><i class="fa-solid fa-plus me-1"></i>Adicionar fonte</button>
        </div>
        <div data-repeat-rows>
          <div class="input-group mb-2" data-repeat-row>
            <select name="fontes_recurso_ids[]" class="form-select" required>
              <option value="">Selecione</option>
              <?php foreach($fontes as $f):?><option value="<?=e($f['id'])?>"><?=e($f['codigo'])?> — <?=e($f['nome'])?></option><?php endforeach;?>
            </select>
            <button type="button" class="btn btn-outline-danger" data-repeat-remove title="Remover"><i class="fa-solid fa-minus"></i></button>
          </div>
        </div>
      </div>

      <div class="col-12" data-repeat-select>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label class="form-label mb-0">Naturezas da despesa *</label>
          <button type="button" class="btn btn-sm btn-outline-primary" data-repeat-add><i class="fa-solid fa-plus me-1"></i>Adicionar natureza</button>
        </div>
        <div data-repeat-rows>
          <div class="input-group mb-2" data-repeat-row>
            <select name="naturezas_despesa_ids[]" class="form-select" required>
              <option value="">Selecione</option>
              <?php foreach($naturezas as $n):?><option value="<?=e($n['id'])?>"><?=e($n['codigo'])?> — <?=e($n['nome'])?></option><?php endforeach;?>
            </select>
            <button type="button" class="btn btn-outline-danger" data-repeat-remove title="Remover"><i class="fa-solid fa-minus"></i></button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2">
    <a href="/obrigacoes" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary" <?=(!$fontes||!$naturezas)?'disabled':''?>><i class="fa-solid fa-floppy-disk me-1"></i>Salvar</button>
  </div>
</form>
<?php unset($fornecedorSelecionado);?>
