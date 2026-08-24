<?php
$_fornecedores = is_array($fornecedores ?? null) ? $fornecedores : [];
$_fornecedorSelecionado = (int)($fornecedorSelecionado ?? 0);
$_fornecedorComboboxId = (string)($fornecedorComboboxId ?? 'fornecedor');
$_fornecedorSelecionadoRegistro = null;
foreach ($_fornecedores as $_fornecedorItem) {
    if ((int)($_fornecedorItem['id'] ?? 0) === $_fornecedorSelecionado) {
        $_fornecedorSelecionadoRegistro = $_fornecedorItem;
        break;
    }
}
$_fornecedorTexto = $_fornecedorSelecionadoRegistro
    ? trim((string)($_fornecedorSelecionadoRegistro['razao_social'] ?? '')).' — '.trim((string)($_fornecedorSelecionadoRegistro['documento'] ?? ''))
    : '';
?>
<div class="position-relative" data-supplier-combobox>
  <label class="form-label" for="<?=e($_fornecedorComboboxId)?>-search">Fornecedor *</label>
  <div class="input-group">
    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
    <input
      type="search"
      id="<?=e($_fornecedorComboboxId)?>-search"
      class="form-control"
      value="<?=e($_fornecedorTexto)?>"
      placeholder="Digite o nome, razão social ou CPF/CNPJ"
      autocomplete="off"
      required
      role="combobox"
      aria-autocomplete="list"
      aria-expanded="false"
      data-combobox-input>
  </div>
  <input type="hidden" name="fornecedor_id" value="<?=e((string)$_fornecedorSelecionado)?>" data-combobox-value data-supplier-value>

  <div class="list-group position-absolute start-0 end-0 mt-1 shadow d-none" data-combobox-menu style="z-index:1055;max-height:20rem;overflow:auto">
    <?php foreach ($_fornecedores as $_fornecedorItem):?>
      <?php
        $_fornecedorId = (int)($_fornecedorItem['id'] ?? 0);
        $_fornecedorRazao = trim((string)($_fornecedorItem['razao_social'] ?? ''));
        $_fornecedorDocumento = trim((string)($_fornecedorItem['documento'] ?? ''));
        $_fornecedorTipo = (string)($_fornecedorItem['tipo_pessoa'] ?? '');
        $_fornecedorLabel = $_fornecedorRazao.' — '.$_fornecedorDocumento;
      ?>
      <button
        type="button"
        class="list-group-item list-group-item-action"
        data-combobox-option
        data-value="<?=e((string)$_fornecedorId)?>"
        data-label="<?=e($_fornecedorLabel)?>">
        <span class="d-block fw-semibold"><?=e($_fornecedorRazao)?></span>
        <span class="small text-body-secondary"><?=e($_fornecedorDocumento)?> · <?=e($_fornecedorTipo==='PF'?'Pessoa Física':'Pessoa Jurídica')?></span>
      </button>
    <?php endforeach;?>
    <div class="list-group-item text-body-secondary d-none" data-combobox-empty>Nenhum fornecedor encontrado.</div>
  </div>
  <div class="form-text">Digite parte do nome, razão social, CPF ou CNPJ e selecione um dos resultados.</div>
</div>
<?php unset($_fornecedores,$_fornecedorSelecionado,$_fornecedorComboboxId,$_fornecedorSelecionadoRegistro,$_fornecedorTexto,$_fornecedorItem,$_fornecedorId,$_fornecedorRazao,$_fornecedorDocumento,$_fornecedorTipo,$_fornecedorLabel);?>