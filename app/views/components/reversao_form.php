<?php
$rvAction=(string)($reversaoAction??'');
$rvTitulo=(string)($reversaoTitulo??'Desfazer última ação');
$rvTexto=(string)($reversaoTexto??'Use esta ação apenas para corrigir um lançamento feito por engano.');
$rvBotao=(string)($reversaoBotao??'Desfazer');
$rvBloqueio=trim((string)($reversaoBloqueio??''));
?>
<div class="card card-danger card-outline mt-3">
  <div class="card-header"><h3 class="card-title"><i class="fa-solid fa-rotate-left me-1"></i><?=e($rvTitulo)?></h3></div>
  <div class="card-body">
    <p class="mb-2"><?=e($rvTexto)?></p>
    <?php if($rvBloqueio!==''):?>
      <div class="alert alert-warning mb-0"><i class="fa-solid fa-lock me-1"></i><?=e($rvBloqueio)?></div>
    <?php else:?>
      <form method="post" action="<?=e($rvAction)?>" class="row g-2 align-items-end" onsubmit="return confirm('Confirma desfazer esta ação? A reversão ficará registrada na auditoria.')">
        <?=Csrf::field()?>
        <div class="col-md-9"><label class="form-label">Motivo da reversão *</label><input name="motivo" class="form-control" minlength="5" maxlength="255" required placeholder="Explique brevemente o motivo da correção"></div>
        <div class="col-md-3"><button type="submit" class="btn btn-outline-danger w-100"><i class="fa-solid fa-rotate-left me-1"></i><?=e($rvBotao)?></button></div>
      </form>
    <?php endif;?>
  </div>
</div>
<?php unset($reversaoAction,$reversaoTitulo,$reversaoTexto,$reversaoBotao,$reversaoBloqueio,$rvAction,$rvTitulo,$rvTexto,$rvBotao,$rvBloqueio);?>
