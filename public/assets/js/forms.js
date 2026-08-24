document.addEventListener('DOMContentLoaded', function () {
  function normalize(value) {
    return (value || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  }

  document.querySelectorAll('[data-select-search]').forEach(function (input) {
    var target = document.querySelector(input.getAttribute('data-select-search'));
    if (!target) return;

    input.addEventListener('input', function () {
      var term = normalize(input.value);
      Array.from(target.options).forEach(function (option, index) {
        if (index === 0) return;
        var match = normalize(option.textContent).includes(term);
        option.hidden = !match;
        option.disabled = !match;
      });
      if (target.selectedOptions[0] && target.selectedOptions[0].disabled) target.value = '';
    });
  });

  document.querySelectorAll('[data-repeat-select]').forEach(function (group) {
    var add = group.querySelector('[data-repeat-add]');
    var rows = group.querySelector('[data-repeat-rows]');
    if (!add || !rows) return;

    function wireRemove(row) {
      var remove = row.querySelector('[data-repeat-remove]');
      if (!remove) return;
      remove.addEventListener('click', function () {
        var allRows = rows.querySelectorAll('[data-repeat-row]');
        if (allRows.length > 1) row.remove();
        else row.querySelector('select').value = '';
      });
    }

    rows.querySelectorAll('[data-repeat-row]').forEach(wireRemove);
    add.addEventListener('click', function () {
      var first = rows.querySelector('[data-repeat-row]');
      if (!first) return;
      var clone = first.cloneNode(true);
      var select = clone.querySelector('select');
      if (select) select.value = '';
      rows.appendChild(clone);
      wireRemove(clone);
    });
  });

  var supplier = document.querySelector('[data-supplier-select]');
  var obligation = document.querySelector('[data-obligation-select]');
  if (supplier && obligation) {
    function filterObligations() {
      var supplierId = supplier.value;
      Array.from(obligation.options).forEach(function (option, index) {
        if (index === 0) return;
        var match = supplierId !== '' && option.dataset.fornecedorId === supplierId;
        option.hidden = !match;
        option.disabled = !match;
      });
      if (obligation.selectedOptions[0] && obligation.selectedOptions[0].disabled) obligation.value = '';
      obligation.disabled = supplierId === '';
    }
    supplier.addEventListener('change', filterObligations);
    filterObligations();
  }
});
