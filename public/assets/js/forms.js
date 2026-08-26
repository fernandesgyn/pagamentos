document.addEventListener('DOMContentLoaded', function () {
  function normalize(value) {
    return (value || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  }

  var comboboxSelector = '[data-supplier-combobox], [data-natureza-combobox]';
  var datasetCache = {};

  function getDataset(combo) {
    var id = combo.dataset.comboboxDataset || '';
    if (!id) return null;
    if (Object.prototype.hasOwnProperty.call(datasetCache, id)) return datasetCache[id];

    var node = document.getElementById(id);
    if (!node) {
      datasetCache[id] = [];
      return datasetCache[id];
    }

    try {
      var parsed = JSON.parse(node.textContent || '[]');
      datasetCache[id] = Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      datasetCache[id] = [];
    }
    return datasetCache[id];
  }

  function initSearchCombobox(combo) {
    if (!combo || combo.dataset.comboboxInitialized === '1') return;
    combo.dataset.comboboxInitialized = '1';

    var input = combo.querySelector('[data-combobox-input]');
    var value = combo.querySelector('[data-combobox-value]');
    var menu = combo.querySelector('[data-combobox-menu]');
    var empty = combo.querySelector('[data-combobox-empty]');
    if (!input || !value || !menu) return;

    var activeIndex = -1;
    var invalidMessage = combo.dataset.comboboxInvalid ||
      (combo.hasAttribute('data-supplier-combobox')
        ? 'Selecione um fornecedor na lista de resultados.'
        : 'Selecione uma opção na lista de resultados.');

    function allOptions() {
      return Array.from(menu.querySelectorAll('[data-combobox-option]'));
    }

    function closeMenu() {
      menu.classList.add('d-none');
      input.setAttribute('aria-expanded', 'false');
      activeIndex = -1;
      allOptions().forEach(function (option) { option.classList.remove('active'); });
    }

    combo._closeSearchMenu = closeMenu;

    function visibleOptions() {
      return allOptions().filter(function (option) { return !option.classList.contains('d-none'); });
    }

    function renderDatasetOptions(items, term) {
      allOptions().forEach(function (option) { option.remove(); });
      var shown = 0;
      var normalizedTerm = normalize(term);

      for (var i = 0; i < items.length && shown < 20; i += 1) {
        var item = items[i] || {};
        var haystack = normalize((item.label || '') + ' ' + (item.primary || '') + ' ' + (item.secondary || ''));
        if (normalizedTerm !== '' && !haystack.includes(normalizedTerm)) continue;

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action';
        button.setAttribute('data-combobox-option', '');
        button.dataset.value = (item.value || '').toString();
        button.dataset.label = (item.label || '').toString();

        var primary = document.createElement('span');
        primary.className = 'd-block fw-semibold';
        primary.textContent = (item.primary || item.label || '').toString();
        button.appendChild(primary);

        if (item.secondary) {
          var secondary = document.createElement('span');
          secondary.className = 'small text-body-secondary';
          secondary.textContent = item.secondary.toString();
          button.appendChild(secondary);
        }

        menu.insertBefore(button, empty || null);
        shown += 1;
      }
      return shown;
    }

    function openAndFilter() {
      var term = input.value.trim();
      var items = getDataset(combo);
      var shown = 0;

      if (items !== null) {
        shown = renderDatasetOptions(items, term);
      } else {
        var normalizedTerm = normalize(term);
        allOptions().forEach(function (option) {
          var haystack = normalize((option.dataset.label || '') + ' ' + option.textContent);
          var match = normalizedTerm === '' || haystack.includes(normalizedTerm);
          if (match && shown < 20) {
            option.classList.remove('d-none');
            shown += 1;
          } else {
            option.classList.add('d-none');
          }
        });
      }

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

    menu.addEventListener('mousedown', function (event) {
      var option = event.target.closest('[data-combobox-option]');
      if (!option || !menu.contains(option)) return;
      event.preventDefault();
      selectOption(option);
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
      row.querySelectorAll('[data-combobox-dataset]').forEach(function (combo) {
        combo.querySelectorAll('[data-combobox-option]').forEach(function (option) { option.remove(); });
      });
      row.querySelectorAll('[data-combobox-option]').forEach(function (option) {
        option.classList.remove('active', 'd-none');
      });
    }

    function prepareClone(row) {
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
      prepareClone(clone);
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
