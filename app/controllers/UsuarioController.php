<?php
declare(strict_types=1);
final class UsuarioController{
    private Usuario $usuarios;
    public function __construct(){Auth::requirePermission('usuario.gerir');$this->usuarios=new Usuario();}
    public function index():void{View::render('paginas/usuarios/index',['titulo'=>'Usuários','usuarios'=>$this->usuarios->todos()]);}
    public function novo():void{View::render('paginas/usuarios/form',['titulo'=>'Novo usuário','usuario'=>null,'perfis'=>$this->usuarios->perfis(true),'action'=>'/usuarios']);}
    public function editar(string $id):void{$usuario=$this->usuarios->buscar((int)$id);if(!$usuario){http_response_code(404);echo 'Usuário não encontrado';return;}View::render('paginas/usuarios/form',['titulo'=>'Editar usuário','usuario'=>$usuario,'perfis'=>$this->usuarios->perfis(false),'action'=>'/usuarios/'.$id]);}
    public function salvar():void{$this->executar(fn()=>$this->usuarios->salvar($_POST),'Usuário cadastrado.','/usuarios','/usuarios/novo');}
    public function atualizar(string $id):void{$this->executar(fn()=>$this->usuarios->salvar($_POST,(int)$id),'Usuário atualizado.','/usuarios','/usuarios/'.$id.'/editar');}
    public function excluir(string $id):void{$atual=(int)(Auth::user()['id']??0);$this->executar(fn()=>$this->usuarios->excluir((int)$id,$atual),'Usuário excluído.','/usuarios','/usuarios');}
    private function executar(callable $acao,string $sucesso,string $destino,string $erroDestino):never{try{$acao();$_SESSION['flash']=['success',$sucesso];redirect($destino);}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];redirect($erroDestino);}}
}
