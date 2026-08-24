<?php
$pageBackUrl=$baseUrl;
require BASE_PATH.'/app/views/components/page_actions.php';
$registro=$registro??[];
$checkboxes=$checkboxes??[];
$numericos=$numericos??[];
$selects=$selects??[];
$obrigatorios=['razao_social','documento','tipo_pessoa','codigo','nome'];
$checkboxes=array_values(array_unique([...$checkboxes,'ativo']));
?>
<form method="post" action="<?=e($action)?>" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados do registro</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <?php foreach($campos as $campo=>$label):?>
        <?php if(in_array($campo,$checkboxes,true)):?>
          <div class="col-md-4"><div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="<?=e($campo)?>" id="campo-<?=e($campo)?>" <?=(!array_key_exists($campo,$registro)||(int)$registro[$campo]===1)?'checked':''?>>
            <label class="form-check-label" for="campo-<?=e($campo)?>"><?=e($label)?></label>
          </div></div>
        <?php elseif(isset($selects[$campo])):?>
          <div class="col-md-6">
            <label class="form-label"><?=e($label)?> *</label>
            <select class="form-select" name="<?=e($campo)?>" required>
              <option value="">Selecione</option>
              <?php foreach($selects[$campo] as $valor=>$texto):?><option value="<?=e((string)$valor)?>" <?=((string)($registro[$campo]??'')===(string)$valor)?'selected':''?>><?=e((string)$texto)?></option><?php endforeach;?>
            </select>
          </div>
        <?php else:?>
          <?php $required=in_array($campo,$obrigatorios,true);?>
          <div class="col-md-6">
            <label class="form-label"><?=e($label)?><?=$required?' *':''?></label>
            <input class="form-control" type="<?=in_array($campo,$numericos,true)?'number':'text'?>" name="<?=e($campo)?>" value="<?=e((string)($registro[$campo]??''))?>" <?=$required?'required':''?>>
          </div>
        <?php endif;?>
      <?php endforeach;?>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2">
    <a href="<?=e($baseUrl)?>" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Salvar</button>
  </div>
</form>
<?php unset($registro,$checkboxes,$numericos,$selects,$obrigatorios,$required,$valor,$texto);?>
