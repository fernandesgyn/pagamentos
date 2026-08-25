<div class="modal fade" id="reversaoModal" tabindex="-1" aria-labelledby="reversaoModalTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="" class="modal-content" data-reversao-form>
      <?=Csrf::field()?>
      <div class="modal-header">
        <h5 class="modal-title" id="reversaoModalTitulo" data-reversao-titulo>Confirmar correção</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2" data-reversao-texto>Esta ação será registrada na auditoria.</div>
        <label class="form-label" for="reversaoMotivo">Motivo da correção *</label>
        <textarea id="reversaoMotivo" name="motivo" class="form-control" rows="3" minlength="5" maxlength="255" required placeholder="Explique brevemente o motivo da correção"></textarea>
        <div class="form-text">A ação só será executada se não houver dependências posteriores que impeçam a correção.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-danger" data-reversao-confirmar><i class="fa-solid fa-check me-1"></i>Confirmar</button>
      </div>
    </form>
  </div>
</div>
