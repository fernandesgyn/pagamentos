<?php
declare(strict_types=1);
final class AuthController{
    public function loginForm():void{
        if(Auth::check())redirect('/');
        require BASE_PATH.'/app/views/paginas/login.php';
    }
    public function login():void{
        $login=trim((string)($_POST['login']??''));
        $senha=(string)($_POST['senha']??'');
        if(Auth::attempt($login,$senha))redirect('/');
        $_SESSION['login_error']='Usuário ou senha inválidos.';
        redirect('/login');
    }
    public function logout():void{Auth::logout();redirect('/login');}
}
