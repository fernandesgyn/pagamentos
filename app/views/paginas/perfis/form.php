<?php
$pageBackUrl='/perfis';require BASE_PATH.'/app/views/components/page_actions.php';
$perfil=$perfil??null;$selecionadasMap=array_fill_keys(array_map('intval',$selecionadas??[]),true);$grupos=[];foreach($permissoes as $permissao){$grupos[(string)$permissao['modulo']][]=$permissao;}
?>
<form method="post" action="<?=e($action)?>" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados do perfil</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <?php if($protegido):?><div class="alert alert-info"><i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>O perfil Administrador possui acesso total e é protegido contra alterações.</div><?php endif;?>
    <div class="row g-3 mb-4">
      <div class="col-md-4"><label class="form-label">Nome *</label><input class="form-control" name="nome" maxlength="100" value="<?=e($perfil['nome']??'')?>" required <?=$protegido?'disabled':''?>></div>
      <div class="col-md-6"><label class="form-label">Descrição</label><input class="form-control" name="descricao" maxlength="255" value="<?=e($perfil['descricao']??'')?>" <?=$protegido?'disabled':''?>></div>
      <div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="ativo" id="perfil-ativo" <?=(!$perfil||$perfil['ativo'])?'checked':''?> <?=$protegido?'disabled':''?>><label class="form-check-label" for="perfil-ativo">Ativo</label></div></div>
    </div>
    <h4 class="fs-5 mb-2">Permissões</h4>
    <p class="text-body-secondary">Marque as ações que os usuários deste perfil poderão executar.</p>
    <div class="accordion" id="permissoes-accordion">
      <?php $grupoIndex=0;foreach($grupos as $modulo=>$itens):$grupoIndex++;$grupoId='permissao-grupo-'.$grupoIndex;?>
        <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button <?=$grupoIndex>1?'collapsed':''?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?=e($grupoId)?>"><?=e(ucfirst($modulo))?></button></h2>
          <div id="<?=e($grupoId)?>" class="accordion-collapse collapse <?=$grupoIndex===1?'show':''?>" data-bs-parent="#permissoes-accordion"><div class="accordion-body"><div class="row g-3">
            <?php foreach($itens as $permissao):$pid=(int)$permissao['id'];$inputId='permissao-'.$pid;?><div class="col-12 col-md-6 col-xl-3"><div class="border rounded p-3 h-100"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissoes[]" value="<?=$pid?>" id="<?=e($inputId)?>" <?=isset($selecionadasMap[$pid])?'checked':''?> <?=$protegido?'disabled':''?>><label class="form-check-label fw-semibold" for="<?=e($inputId)?>"><?=e($permissao['acao'])?></label></div><div class="small text-body-secondary mt-1"><?=e($permissao['descricao'])?></div></div></div><?php endforeach;?>
          </div></div></div>
        </div>
      <?php endforeach;?>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/perfis" class="btn btn-outline-secondary">Cancelar</a><?php if(!$protegido):?><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Salvar</button><?php endif;?></div>
</form>
