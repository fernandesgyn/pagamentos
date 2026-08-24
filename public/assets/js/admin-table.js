(() => {
  'use strict';

  const normalize = (value) => String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('pt-BR')
    .trim();

  const sortableValue = (cell) => {
    const raw = String(cell?.dataset.sortValue ?? cell?.textContent ?? '').trim();
    const dateBr = raw.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
    if (dateBr) return { type: 'number', value: Date.UTC(Number(dateBr[3]), Number(dateBr[2]) - 1, Number(dateBr[1]), Number(dateBr[4] || 0), Number(dateBr[5] || 0)) };
    const dateIso = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (dateIso) return { type: 'number', value: Date.UTC(Number(dateIso[1]), Number(dateIso[2]) - 1, Number(dateIso[3]), Number(dateIso[4] || 0), Number(dateIso[5] || 0)) };
    if (/R\$/.test(raw)) {
      const currency = Number(raw.replace(/[^0-9,.-]/g, '').replace(/\./g, '').replace(',', '.'));
      if (Number.isFinite(currency)) return { type: 'number', value: currency };
    }
    if (/^-?\d+(?:[.,]\d+)?$/.test(raw)) {
      const numeric = Number(raw.replace(',', '.'));
      if (Number.isFinite(numeric)) return { type: 'number', value: numeric };
    }
    return { type: 'text', value: raw };
  };

  const initAdminTable = (root) => {
    const table = root.querySelector('table');
    if (!table || !table.tBodies.length) return;

    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows).filter((row) => !row.hasAttribute('data-table-empty'));
    const originalOrder = new Map(rows.map((row, index) => [row, index]));
    const filters = Array.from(root.querySelectorAll('[data-table-filter]'));
    const pageSizeControl = root.querySelector('[data-table-page-size]');
    const resetButton = root.querySelector('[data-table-reset]');
    const status = root.querySelector('[data-table-status]');
    const pagination = root.querySelector('[data-table-pagination]');
    const headers = Array.from(table.tHead?.rows[0]?.cells || []);
    let currentPage = 1;
    let sortColumn = null;
    let sortDirection = 'asc';

    const columnText = (row, column) => normalize(row.cells[Number(column)]?.textContent ?? '');

    root.querySelectorAll('select[data-table-populate="true"]').forEach((select) => {
      const column = Number(select.dataset.tableFilterColumn);
      if (!Number.isInteger(column)) return;
      const existing = new Set(Array.from(select.options).map((option) => option.value));
      const values = [...new Set(rows.map((row) => row.cells[column]?.textContent.trim() ?? '').filter(Boolean))]
        .sort((a, b) => a.localeCompare(b, 'pt-BR', { numeric: true, sensitivity: 'base' }));
      values.forEach((value) => {
        if (existing.has(value)) return;
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        select.appendChild(option);
      });
    });

    const rowMatches = (row) => filters.every((filter) => {
      const raw = filter.value.trim();
      if (raw === '') return true;
      const needle = normalize(raw);
      const column = filter.dataset.tableFilterColumn ?? '*';
      const mode = filter.dataset.tableFilterMode ?? 'contains';
      const haystack = column === '*' ? normalize(row.textContent) : columnText(row, column);
      return mode === 'exact' ? haystack === needle : haystack.includes(needle);
    });

    const compareRows = (a, b) => {
      if (sortColumn === null) return (originalOrder.get(a) ?? 0) - (originalOrder.get(b) ?? 0);
      const av = sortableValue(a.cells[sortColumn]);
      const bv = sortableValue(b.cells[sortColumn]);
      let result = 0;
      if (av.type === 'number' && bv.type === 'number') result = av.value - bv.value;
      else result = String(av.value).localeCompare(String(bv.value), 'pt-BR', { numeric: true, sensitivity: 'base' });
      if (result === 0) result = (originalOrder.get(a) ?? 0) - (originalOrder.get(b) ?? 0);
      return sortDirection === 'desc' ? -result : result;
    };

    const updateSortHeaders = () => {
      headers.forEach((header, index) => {
        const icon = header.querySelector('[data-table-sort-icon]');
        if (!icon) return;
        const active = sortColumn === index;
        header.setAttribute('aria-sort', active ? (sortDirection === 'asc' ? 'ascending' : 'descending') : 'none');
        icon.className = active ? `fa-solid ${sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'}` : 'fa-solid fa-sort';
      });
    };

    headers.forEach((header, index) => {
      if (header.hasAttribute('data-table-nosort') || normalize(header.textContent) === 'acoes') return;
      const label = header.textContent.trim();
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'border-0 bg-transparent p-0 d-inline-flex align-items-center gap-1 fw-semibold text-body';
      button.setAttribute('data-table-sort', '');
      button.setAttribute('aria-label', `Ordenar por ${label}`);
      const icon = document.createElement('i');
      icon.className = 'fa-solid fa-sort';
      icon.setAttribute('data-table-sort-icon', '');
      icon.setAttribute('aria-hidden', 'true');
      const text = document.createElement('span');
      text.textContent = label;
      button.append(icon, text);
      header.replaceChildren(button);
      header.setAttribute('aria-sort', 'none');
      button.addEventListener('click', () => {
        if (sortColumn === index) sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        else { sortColumn = index; sortDirection = 'asc'; }
        currentPage = 1;
        updateSortHeaders();
        render();
      });
    });

    const makePageItem = (label, targetPage, { disabled = false, active = false, ariaLabel = '' } = {}) => {
      const item = document.createElement('li');
      item.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
      if (label === '…') {
        const span = document.createElement('span');
        span.className = 'page-link';
        span.textContent = label;
        item.appendChild(span);
        return item;
      }
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'page-link';
      button.textContent = label;
      if (ariaLabel) button.setAttribute('aria-label', ariaLabel);
      if (active) button.setAttribute('aria-current', 'page');
      button.disabled = disabled;
      button.addEventListener('click', () => { currentPage = targetPage; render(); });
      item.appendChild(button);
      return item;
    };

    const renderPagination = (totalPages) => {
      if (!pagination) return;
      pagination.replaceChildren();
      if (totalPages <= 1) return;
      pagination.appendChild(makePageItem('‹', Math.max(1, currentPage - 1), { disabled: currentPage === 1, ariaLabel: 'Página anterior' }));
      const candidates = new Set([1, totalPages]);
      for (let page = Math.max(2, currentPage - 2); page <= Math.min(totalPages - 1, currentPage + 2); page += 1) candidates.add(page);
      const pages = [...candidates].sort((a, b) => a - b);
      let previous = 0;
      pages.forEach((page) => {
        if (previous && page - previous > 1) pagination.appendChild(makePageItem('…', page, { disabled: true }));
        pagination.appendChild(makePageItem(String(page), page, { active: page === currentPage }));
        previous = page;
      });
      pagination.appendChild(makePageItem('›', Math.min(totalPages, currentPage + 1), { disabled: currentPage === totalPages, ariaLabel: 'Próxima página' }));
    };

    const render = () => {
      const pageSize = Math.max(1, Number(pageSizeControl?.value || root.dataset.pageSize || 10));
      const filteredRows = rows.filter(rowMatches).sort(compareRows);
      const filteredSet = new Set(filteredRows);
      [...filteredRows, ...rows.filter((row) => !filteredSet.has(row))].forEach((row) => tbody.appendChild(row));
      const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
      if (currentPage > totalPages) currentPage = totalPages;
      rows.forEach((row) => { row.hidden = true; });
      const start = (currentPage - 1) * pageSize;
      const end = Math.min(start + pageSize, filteredRows.length);
      filteredRows.slice(start, end).forEach((row) => { row.hidden = false; });
      let emptyRow = tbody.querySelector('[data-table-empty]');
      if (filteredRows.length === 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.setAttribute('data-table-empty', '');
          const cell = document.createElement('td');
          cell.colSpan = table.tHead?.rows[0]?.cells.length || 1;
          cell.className = 'text-center text-body-secondary py-4';
          cell.textContent = 'Nenhum registro encontrado para os filtros informados.';
          emptyRow.appendChild(cell);
          tbody.appendChild(emptyRow);
        }
        emptyRow.hidden = false;
      } else if (emptyRow) emptyRow.hidden = true;
      if (status) {
        status.textContent = filteredRows.length === 0
          ? `0 de ${rows.length} registros`
          : `Exibindo ${start + 1}–${end} de ${filteredRows.length} registros${filteredRows.length !== rows.length ? ` (${rows.length} no total)` : ''}`;
      }
      renderPagination(totalPages);
    };

    filters.forEach((filter) => filter.addEventListener(filter.tagName === 'SELECT' ? 'change' : 'input', () => { currentPage = 1; render(); }));
    pageSizeControl?.addEventListener('change', () => { currentPage = 1; render(); });
    resetButton?.addEventListener('click', () => { filters.forEach((filter) => { filter.value = ''; }); currentPage = 1; render(); });
    updateSortHeaders();
    render();
  };

  document.querySelectorAll('[data-admin-table]').forEach(initAdminTable);
})();
