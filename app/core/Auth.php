<?php
declare(strict_types=1);
final class Auth{
    public static function user():?array{return $_SESSION['user']??null;}
    public static function check():bool{return isset($_SESSION['user']['id']);}
    public static function attempt(string $login,string $senha):bool{
        $st=Database::connection()->prepare("SELECT u.id,u.nome,u.login,u.senha_hash,u.ativo,p.id perfil_id,p.nome perfil FROM usuarios u JOIN perfis p ON p.id=u.perfil_id WHERE u.login=? LIMIT 1");
        $st->execute([$login]);$u=$st->fetch();
        if(!$u||(int)$u['ativo']!==1||!password_verify($senha,$u['senha_hash']))return false;
        unset($u['senha_hash']);$_SESSION['user']=$u;session_regenerate_id(true);return true;
    }
    public static function logout():void{unset($_SESSION['user']);session_regenerate_id(true);}
    public static function can(string $chave):bool{
        $u=self::user();if(!$u)return false;if(($u['perfil']??'')==='Administrador')return true;
        $st=Database::connection()->prepare("SELECT 1 FROM perfil_permissoes pp JOIN permissoes p ON p.id=pp.permissao_id WHERE pp.perfil_id=? AND p.chave=? LIMIT 1");$st->execute([(int)$u['perfil_id'],$chave]);return (bool)$st->fetchColumn();
    }
    public static function requireLogin():void{if(!self::check())redirect('/login');}
    public static function requirePermission(string $chave):void{self::requireLogin();if(!self::can($chave)){http_response_code(403);exit('Acesso negado.');}}
}
