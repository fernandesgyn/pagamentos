document.addEventListener('DOMContentLoaded', function () {
  function normalize(value) {
    return (value || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  }

  var comboboxSelector = '[data-supplier-combobox], [data-natureza-combobox]';

  function initSearchCombobox(combo) {
    if (!combo || combo.dataset.comboboxInitialized === '1') return;
    combo.dataset.comboboxInitialized = '1';

    var input = combo.querySelector('[data-combobox-input]');
    var value = combo.querySelector('[data-combobox-value]');
    var menu = combo.querySelector('[data-combobox-menu]');
    var options = Array.from(combo.querySelectorAll('[data-combobox-option]'));
    var empty = combo.querySelector('[data-combobox-empty]');
    if (!input || !value || !menu) return;

    var activeIndex = -1;
    var invalidMessage = combo.dataset.comboboxInvalid ||
      (combo.hasAttribute('data-supplier-combobox')
        ? 'Selecione um fornecedor na lista de resultados.'
        : 'Selecione uma opção na lista de resultados.');

    function closeMenu() {
      menu.classList.add('d-none');
      input.setAttribute('aria-expanded', 'false');
      activeIndex = -1;
      options.forEach(function (option) { option.classList.remove('active'); });
    }

    combo._closeSearchMenu = closeMenu;

    function visibleOptions() {
      return options.filter(function (option) { return !option.classList.contains('d-none'); });
    }

    function openAndFilter() {
      var term = normalize(input.value.trim());
      var shown = 0;
      options.forEach(function (option) {
        var haystack = normalize((option.dataset.label || '') + ' ' + option.textContent);
        var match = term === '' || haystack.includes(term);
        if (match && shown < 20) {
          option.classList.remove('d-none');
          shown += 1;
        } else {
          option.classList.add('d-none');
        }
      });
      if (empty) empty.classList.toggle('d-none', shown !== 0);
      menu.classList.remove('d-none');
      input.setAttribute('aria-expanded', 'true');
      activeIndex = -1;
    }

    function selectOption(option) {
      value.value = option.dataset.value || '';
      input.value = option.dataset.label || option.textContent.trim();
      input.setCustomValidity('');
      value.dispatchEvent(new Event('change', { bubbles: true }));
      closeMenu();
    }

    input.addEventListener('focus', openAndFilter);
    input.addEventListener('input', function () {
      value.value = '';
      value.dispatchEvent(new Event('change', { bubbles: true }));
      input.setCustomValidity(invalidMessage);
      openAndFilter();
    });

    options.forEach(function (option) {
      option.addEventListener('mousedown', function (event) {
        event.preventDefault();
        selectOption(option);
      });
    });

    input.addEventListener('keydown', function (event) {
      var visible = visibleOptions();
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        if (menu.classList.contains('d-none')) openAndFilter();
        visible = visibleOptions();
        if (!visible.length) return;
        activeIndex += event.key === 'ArrowDown' ? 1 : -1;
        if (activeIndex < 0) activeIndex = visible.length - 1;
        if (activeIndex >= visible.length) activeIndex = 0;
        visible.forEach(function (option) { option.classList.remove('active'); });
        visible[activeIndex].classList.add('active');
        visible[activeIndex].scrollIntoView({ block: 'nearest' });
      } else if (event.key === 'Enter' && !menu.classList.contains('d-none')) {
        if (activeIndex >= 0 && visible[activeIndex]) {
          event.preventDefault();
          selectOption(visible[activeIndex]);
        }
      } else if (event.key === 'Escape') {
        closeMenu();
      }
    });
  }

  document.querySelectorAll(comboboxSelector).forEach(initSearchCombobox);

  document.addEventListener('mousedown', function (event) {
    document.querySelectorAll(comboboxSelector).forEach(function (combo) {
      if (!combo.contains(event.target) && typeof combo._closeSearchMenu === 'function') {
        combo._closeSearchMenu();
      }
    });
  });

  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var firstInvalid = null;
      form.querySelectorAll(comboboxSelector).forEach(function (combo) {
        var input = combo.querySelector('[data-combobox-input]');
        var value = combo.querySelector('[data-combobox-value]');
        if (!input || !value) return;
        var invalidMessage = combo.dataset.comboboxInvalid ||
          (combo.hasAttribute('data-supplier-combobox')
            ? 'Selecione um fornecedor na lista de resultados.'
            : 'Selecione uma opção na lista de resultados.');
        if (value.value === '') {
          input.setCustomValidity(invalidMessage);
          if (!firstInvalid) firstInvalid = input;
        } else {
          input.setCustomValidity('');
        }
      });
      if (firstInvalid) {
        event.preventDefault();
        firstInvalid.reportValidity();
      }
    });
  });

  document.querySelectorAll('[data-repeat-select]').forEach(function (group) {
    var add = group.querySelector('[data-repeat-add]');
    var rows = group.querySelector('[data-repeat-rows]');
    if (!add || !rows) return;

    function resetRow(row) {
      row.querySelectorAll('select').forEach(function (select) { select.value = ''; });
      row.querySelectorAll('[data-combobox-input]').forEach(function (input) {
        input.value = '';
        input.setCustomValidity('');
        input.setAttribute('aria-expanded', 'false');
      });
      row.querySelectorAll('[data-combobox-value]').forEach(function (value) { value.value = ''; });
      row.querySelectorAll('[data-combobox-menu]').forEach(function (menu) { menu.classList.add('d-none'); });
      row.querySelectorAll('[data-combobox-option]').forEach(function (option) {
        option.classList.remove('active', 'd-none');
      });
      row.querySelectorAll(comboboxSelector).forEach(function (combo) {
        combo.removeAttribute('data-combobox-initialized');
      });
    }

    function wireRemove(row) {
      var remove = row.querySelector('[data-repeat-remove]');
      if (!remove) return;
      remove.addEventListener('click', function () {
        var allRows = rows.querySelectorAll('[data-repeat-row]');
        if (allRows.length > 1) {
          row.remove();
        } else {
          resetRow(row);
          row.querySelectorAll(comboboxSelector).forEach(initSearchCombobox);
        }
      });
    }

    rows.querySelectorAll('[data-repeat-row]').forEach(function (row) {
      wireRemove(row);
      row.querySelectorAll(comboboxSelector).forEach(initSearchCombobox);
    });

    add.addEventListener('click', function () {
      var first = rows.querySelector('[data-repeat-row]');
      if (!first) return;
      var clone = first.cloneNode(true);
      resetRow(clone);
      rows.appendChild(clone);
      wireRemove(clone);
      clone.querySelectorAll(comboboxSelector).forEach(initSearchCombobox);
      var input = clone.querySelector('[data-combobox-input]');
      if (input) input.focus();
    });
  });

  var supplier = document.querySelector('[data-supplier-value]');
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
      obligation.options[0].textContent = supplierId === '' ? 'Selecione primeiro o fornecedor' : 'Selecione a obrigação';
    }
    supplier.addEventListener('change', filterObligations);
    filterObligations();
  }

  var reversaoModal = document.getElementById('reversaoModal');
  if (reversaoModal) {
    var reversaoForm = reversaoModal.querySelector('[data-reversao-form]');
    var reversaoTitulo = reversaoModal.querySelector('[data-reversao-titulo]');
    var reversaoTexto = reversaoModal.querySelector('[data-reversao-texto]');
    var reversaoConfirmar = reversaoModal.querySelector('[data-reversao-confirmar]');
    var reversaoMotivo = reversaoModal.querySelector('textarea[name="motivo"]');

    document.querySelectorAll('[data-reversao-modal]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!reversaoForm) return;
        reversaoForm.action = button.dataset.reversaoAction || '';
        if (reversaoTitulo) reversaoTitulo.textContent = button.dataset.reversaoTitulo || 'Desfazer ação';
        if (reversaoTexto) reversaoTexto.textContent = button.dataset.reversaoTexto || 'Esta reversão será registrada na auditoria.';
        if (reversaoConfirmar) reversaoConfirmar.lastChild.textContent = button.dataset.reversaoBotao || 'Confirmar reversão';
        if (reversaoMotivo) reversaoMotivo.value = '';
      });
    });
  }

  var pagamentoModal = document.getElementById('pagamentoModal');
  if (pagamentoModal) {
    var pagamentoForm = pagamentoModal.querySelector('[data-pagamento-form]');
    var pagamentoTitulo = pagamentoModal.querySelector('[data-pagamento-titulo]');
    var pagamentoTexto = pagamentoModal.querySelector('[data-pagamento-texto]');
    var pagamentoValor = pagamentoModal.querySelector('input[name="valor_liquido_pago"]');
    var pagamentoHistorico = pagamentoModal.querySelector('input[name="historico_pagamento"]');

    document.querySelectorAll('[data-pagamento-modal]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!pagamentoForm) return;
        pagamentoForm.action = button.dataset.pagamentoAction || '';
        if (pagamentoTitulo) pagamentoTitulo.textContent = button.dataset.pagamentoTitulo || 'Registrar pagamento';
        if (pagamentoTexto) pagamentoTexto.textContent = button.dataset.pagamentoTexto || '';
        if (pagamentoValor) pagamentoValor.value = button.dataset.pagamentoValor || '';
        if (pagamentoHistorico) pagamentoHistorico.value = '';
      });
    });
  }
});
