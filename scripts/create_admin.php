<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
$options=getopt('', ['login::','nome::','email::','senha:']);
$senha=(string)($options['senha']??'');
if(strlen($senha)<8){fwrite(STDERR,"Uso: php scripts/create_admin.php --senha=SenhaCom8OuMais [--login=admin] [--nome=Administrador] [--email=]\n");exit(1);}
$login=trim((string)($options['login']??'admin'));
$nome=trim((string)($options['nome']??'Administrador do Sistema'));
$email=trim((string)($options['email']??''))?:null;
$db=Database::connection();
$perfil=(int)$db->query("SELECT id FROM perfis WHERE nome='Administrador' LIMIT 1")->fetchColumn();
if(!$perfil){fwrite(STDERR,"Perfil Administrador não encontrado. Execute database/schema.sql primeiro.\n");exit(1);}
$st=$db->prepare("SELECT id FROM usuarios WHERE login=? LIMIT 1");$st->execute([$login]);$id=$st->fetchColumn();
$hash=password_hash($senha,PASSWORD_DEFAULT);
if($id){$q=$db->prepare("UPDATE usuarios SET perfil_id=?,nome=?,email=?,senha_hash=?,ativo=1,atualizado_em=NOW() WHERE id=?");$q->execute([$perfil,$nome,$email,$hash,$id]);echo "Administrador atualizado: {$login}\n";}
else{$q=$db->prepare("INSERT INTO usuarios(perfil_id,nome,login,email,senha_hash,ativo) VALUES(?,?,?,?,?,1)");$q->execute([$perfil,$nome,$login,$email,$hash]);echo "Administrador criado: {$login}\n";}
