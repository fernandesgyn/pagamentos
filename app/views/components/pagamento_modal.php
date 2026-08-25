<div class="modal fade" id="pagamentoModal" tabindex="-1" aria-labelledby="pagamentoModalTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="" data-pagamento-form>
        <div class="modal-header">
          <h5 class="modal-title" id="pagamentoModalTitulo" data-pagamento-titulo>Registrar pagamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <?=Csrf::field()?>
          <p class="text-body-secondary" data-pagamento-texto></p>
          <div class="mb-3"><label class="form-label">Data do pagamento *</label><input type="date" name="data_pagamento" value="<?=date('Y-m-d')?>" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Valor líquido pago *</label><input name="valor_liquido_pago" class="form-control" required></div>
          <div><label class="form-label">Histórico do pagamento</label><input name="historico_pagamento" maxlength="255" class="form-control" placeholder="Informe o histórico do pagamento"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success"><i class="fa-solid fa-money-check-dollar me-1"></i>Confirmar pagamento</button>
        </div>
      </form>
    </div>
  </div>
</div>
