<?php
$_naturezas = is_array($naturezas ?? null) ? $naturezas : [];
?>
<div class="position-relative flex-grow-1" data-natureza-combobox data-combobox-invalid="Selecione uma natureza da despesa na lista de resultados.">
  <div class="input-group">
    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
    <input
      type="search"
      class="form-control"
      placeholder="Digite o código ou a descrição da natureza"
      autocomplete="off"
      required
      role="combobox"
      aria-autocomplete="list"
      aria-expanded="false"
      data-combobox-input>
  </div>
  <input type="hidden" name="naturezas_despesa_ids[]" value="" data-combobox-value>

  <div class="list-group position-absolute start-0 end-0 mt-1 shadow d-none" data-combobox-menu style="z-index:1055;max-height:20rem;overflow:auto">
    <?php foreach ($_naturezas as $_naturezaItem):?>
      <?php
        $_naturezaId = (int)($_naturezaItem['id'] ?? 0);
        $_naturezaCodigo = trim((string)($_naturezaItem['codigo'] ?? ''));
        $_naturezaDescricao = trim((string)($_naturezaItem['nome'] ?? ''));
        $_naturezaLabel = $_naturezaCodigo.' — '.$_naturezaDescricao;
      ?>
      <button
        type="button"
        class="list-group-item list-group-item-action"
        data-combobox-option
        data-natureza-option
        data-value="<?=e((string)$_naturezaId)?>"
        data-label="<?=e($_naturezaLabel)?>">
        <span class="d-block fw-semibold"><?=e($_naturezaCodigo)?></span>
        <span class="small text-body-secondary"><?=e($_naturezaDescricao)?></span>
      </button>
    <?php endforeach;?>
    <div class="list-group-item text-body-secondary d-none" data-combobox-empty>Nenhuma natureza da despesa encontrada.</div>
  </div>
  <div class="form-text">Pesquise pelo código ou por qualquer trecho da descrição e selecione um dos resultados.</div>
</div>
<?php unset($_naturezas,$_naturezaItem,$_naturezaId,$_naturezaCodigo,$_naturezaDescricao,$_naturezaLabel);?>