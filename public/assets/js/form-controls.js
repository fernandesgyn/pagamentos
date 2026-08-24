(() => {
  'use strict';
  const normalize=(v)=>String(v??'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLocaleLowerCase('pt-BR').trim();
  document.querySelectorAll('[data-fornecedor-search]').forEach((root)=>{
    const input=root.querySelector('[data-fornecedor-input]'), hidden=root.querySelector('[data-fornecedor-id]');
    const list=input?.getAttribute('list')?document.getElementById(input.getAttribute('list')):null;
    if(!input||!hidden||!list)return;
    const options=Array.from(list.querySelectorAll('option'));
    const emit=()=>hidden.dispatchEvent(new CustomEvent('fornecedor-change',{bubbles:true,detail:{id:hidden.value}}));
    const sync=()=>{const exact=options.find(o=>normalize(o.value)===normalize(input.value));hidden.value=exact?.dataset.id||'';emit();};
    if(hidden.value){const selected=options.find(o=>String(o.dataset.id)===String(hidden.value));if(selected)input.value=selected.value;}
    input.addEventListener('change',sync);
    input.addEventListener('input',()=>{const exact=options.find(o=>normalize(o.value)===normalize(input.value));if(exact)sync();else{hidden.value='';emit();}});
  });
  const filterObligations=(providerId)=>document.querySelectorAll('[data-obrigacao-select]').forEach((select)=>{
    const previous=select.value;let allowedPrevious=false;
    Array.from(select.options).forEach((option,index)=>{if(index===0)return;const allowed=providerId&&String(option.dataset.fornecedorId)===String(providerId);option.hidden=!allowed;option.disabled=!allowed;if(allowed&&option.value===previous)allowedPrevious=true;});
    select.disabled=!providerId;if(!allowedPrevious)select.value='';
  });
  document.addEventListener('fornecedor-change',(e)=>filterObligations(e.detail?.id||''));
  document.querySelectorAll('[data-obrigacao-select]').forEach((select)=>{const initial=Array.from(select.options).find(o=>o.selected&&o.dataset.fornecedorId);if(initial)filterObligations(initial.dataset.fornecedorId);});
  document.querySelectorAll('[data-repeatable-select]').forEach((root)=>{
    const refresh=()=>{const rows=Array.from(root.querySelectorAll('[data-repeatable-row]'));rows.forEach((row,index)=>{let remove=row.querySelector('[data-repeatable-remove]');if(index===0){remove?.remove();return;}if(!remove){remove=document.createElement('button');remove.type='button';remove.className='btn btn-outline-danger';remove.setAttribute('data-repeatable-remove','');remove.innerHTML='<i class="fa-solid fa-minus"></i>';remove.title='Remover';row.appendChild(remove);}});};
    root.addEventListener('click',(event)=>{const add=event.target.closest('[data-repeatable-add]'),remove=event.target.closest('[data-repeatable-remove]');if(add){const first=root.querySelector('[data-repeatable-row]');if(!first)return;const clone=first.cloneNode(true);clone.querySelector('select').value='';clone.querySelector('[data-repeatable-add]')?.remove();root.appendChild(clone);refresh();}if(remove){remove.closest('[data-repeatable-row]')?.remove();refresh();}});refresh();
  });
})();
