<?php
declare(strict_types=1);
final class UsuarioController{
    private Usuario $usuarios;
    public function __construct(){Auth::requirePermission('usuario.gerir');$this->usuarios=new Usuario();}
    public function index():void{View::render('paginas/usuarios',['titulo'=>'Usuários e perfis','usuarios'=>$this->usuarios->todos(),'perfis'=>$this->usuarios->perfis()]);}
    public function salvar():void{try{$this->usuarios->criar($_POST);$_SESSION['flash']=['success','Usuário cadastrado.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/usuarios');}
    public function alternarStatus(string $id):void{
        try{
            $atual=(int)(Auth::user()['id']??0);
            $ativo=$this->usuarios->alternarAtivo((int)$id,$atual);
            $_SESSION['flash']=['success',$ativo?'Usuário ativado.':'Usuário desativado.'];
        }catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/usuarios');
    }
}
