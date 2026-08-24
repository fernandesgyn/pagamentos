<?php
declare(strict_types=1);
final class PerfilController{
    private Perfil $perfis;
    public function __construct(){Auth::requirePermission('perfil.gerir');$this->perfis=new Perfil();}
    public function index():void{View::render('paginas/perfis/index',['titulo'=>'Perfis e permissões','perfis'=>$this->perfis->todos()]);}
    public function novo():void{View::render('paginas/perfis/form',['titulo'=>'Novo perfil','perfil'=>null,'permissoes'=>$this->perfis->permissoes(),'selecionadas'=>[],'action'=>'/perfis','protegido'=>false]);}
    public function editar(string $id):void{$perfil=$this->perfis->buscar((int)$id);if(!$perfil){http_response_code(404);echo 'Perfil não encontrado';return;}View::render('paginas/perfis/form',['titulo'=>((int)$id===1?'Visualizar perfil':'Editar perfil'),'perfil'=>$perfil,'permissoes'=>$this->perfis->permissoes(),'selecionadas'=>$this->perfis->selecionadas((int)$id),'action'=>'/perfis/'.$id,'protegido'=>(int)$id===1]);}
    public function salvar():void{$this->executar(fn()=>$this->perfis->salvar($_POST),'Perfil cadastrado.','/perfis','/perfis/novo');}
    public function atualizar(string $id):void{$this->executar(fn()=>$this->perfis->salvar($_POST,(int)$id),'Perfil atualizado.','/perfis','/perfis/'.$id.'/editar');}
    public function excluir(string $id):void{$this->executar(fn()=>$this->perfis->excluir((int)$id),'Perfil excluído.','/perfis','/perfis');}
    private function executar(callable $acao,string $sucesso,string $destino,string $erroDestino):never{try{$acao();$_SESSION['flash']=['success',$sucesso];redirect($destino);}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];redirect($erroDestino);}}
}
