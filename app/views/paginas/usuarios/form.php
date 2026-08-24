<?php
$pageBackUrl='/usuarios';require BASE_PATH.'/app/views/components/page_actions.php';
$usuario=$usuario??null;$perfilSelecionado=(int)($usuario['perfil_id']??0);
?>
<form method="post" action="<?=e($action)?>" class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Dados do usuário</h3></div>
  <div class="card-body">
    <?=Csrf::field()?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nome *</label><input class="form-control" name="nome" value="<?=e($usuario['nome']??'')?>" required></div>
      <div class="col-md-3"><label class="form-label">Login *</label><input class="form-control" name="login" value="<?=e($usuario['login']??'')?>" required></div>
      <div class="col-md-3"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" value="<?=e($usuario['email']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Perfil *</label><select class="form-select" name="perfil_id" required><option value="">Selecione</option><?php foreach($perfis as $p):?><option value="<?=e($p['id'])?>" <?=((int)$p['id']===$perfilSelecionado)?'selected':''?> <?=(!$p['ativo']&&$perfilSelecionado!==(int)$p['id'])?'disabled':''?>><?=e($p['nome'])?><?=$p['ativo']?'':' (inativo)'?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">Senha <?=$usuario?'(deixe em branco para manter)':'*'?></label><input class="form-control" type="password" name="senha" <?=$usuario?'':'required minlength="8"'?>></div>
      <div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="ativo" id="usuario-ativo" <?=(!$usuario||$usuario['ativo'])?'checked':''?>><label class="form-check-label" for="usuario-ativo">Ativo</label></div></div>
      <div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="trocar_senha" id="trocar-senha" <?=($usuario['trocar_senha']??false)?'checked':''?>><label class="form-check-label" for="trocar-senha">Trocar senha</label></div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end gap-2"><a href="/usuarios" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Salvar</button></div>
</form>
