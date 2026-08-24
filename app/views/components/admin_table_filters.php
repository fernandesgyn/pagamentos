<?php
$_tableFilters = $tableFilters ?? [];
$_tablePageSize = (int) ($tablePageSize ?? 10);
?>
<div class="card-body border-bottom">
<div class="row g-3 align-items-end">
<?php foreach ($_tableFilters as $_tableFilterIndex => $_tableFilter):
    $_tableFilterType = (string) ($_tableFilter['type'] ?? 'search');
    $_tableFilterColumn = (string) ($_tableFilter['column'] ?? '*');
    $_tableFilterMode = (string) ($_tableFilter['mode'] ?? ($_tableFilterType === 'select' ? 'exact' : 'contains'));
    $_tableFilterId = 'filtro-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) $tableId) . '-' . $_tableFilterIndex;
    $_tableFilterClass = (string) ($_tableFilter['class'] ?? 'col-12 col-md-6 col-lg-3');
?>
<div class="<?=e($_tableFilterClass)?>">
<label class="form-label" for="<?=e($_tableFilterId)?>"><?=e($_tableFilter['label'] ?? 'Pesquisar')?></label>
<?php if ($_tableFilterType === 'select'): ?>
<select class="form-select form-select-sm" id="<?=e($_tableFilterId)?>" data-table-filter data-table-filter-column="<?=e($_tableFilterColumn)?>" data-table-filter-mode="<?=e($_tableFilterMode)?>" <?=!empty($_tableFilter['populate']) ? 'data-table-populate="true"' : ''?>>
<option value=""><?=e($_tableFilter['empty'] ?? 'Todos')?></option>
<?php foreach (($_tableFilter['options'] ?? []) as $_tableOptionValue => $_tableOptionLabel): ?>
<option value="<?=e($_tableOptionValue)?>"><?=e($_tableOptionLabel)?></option>
<?php endforeach; ?>
</select>
<?php else: ?>
<div class="input-group input-group-sm">
<span class="input-group-text"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
<input class="form-control" type="search" id="<?=e($_tableFilterId)?>" data-table-filter data-table-filter-column="<?=e($_tableFilterColumn)?>" data-table-filter-mode="<?=e($_tableFilterMode)?>" placeholder="<?=e($_tableFilter['placeholder'] ?? 'Digite para pesquisar')?>" autocomplete="off">
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
<div class="col-6 col-md-3 col-lg-2">
<label class="form-label" for="<?=e((string)$tableId)?>-page-size">Itens por página</label>
<select class="form-select form-select-sm" id="<?=e((string)$tableId)?>-page-size" data-table-page-size>
<?php foreach ([10, 25, 50, 100] as $_tableSize): ?><option value="<?=$_tableSize?>" <?=($_tablePageSize === $_tableSize) ? 'selected' : ''?>><?=$_tableSize?></option><?php endforeach; ?>
</select>
</div>
<div class="col-6 col-md-auto">
<button class="btn btn-outline-secondary btn-sm w-100" type="button" data-table-reset><i class="fa-solid fa-rotate-left me-1" aria-hidden="true"></i>Limpar filtros</button>
</div>
</div>
</div>
<?php unset($_tableFilters, $_tablePageSize, $_tableFilterIndex, $_tableFilter, $_tableFilterType, $_tableFilterColumn, $_tableFilterMode, $_tableFilterId, $_tableFilterClass, $_tableOptionValue, $_tableOptionLabel, $_tableSize); ?>
