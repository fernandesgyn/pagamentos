<?php
declare(strict_types=1);
final class CadastroController{
    private Cadastro $cadastro;
    public function __construct(){Auth::requirePermission('cadastro.gerir');$this->cadastro=new Cadastro();}
    public function index():void{View::render('paginas/cadastros',['titulo'=>'Cadastros auxiliares','fornecedores'=>$this->cadastro->fornecedores(),'empenhos'=>$this->cadastro->empenhos(),'tiposDocumento'=>$this->cadastro->tiposDocumento(),'tiposObrigacao'=>$this->cadastro->tiposObrigacao()]);}
    public function fornecedor():void{$this->executar(fn()=>$this->cadastro->criarFornecedor($_POST),'Fornecedor cadastrado.');}
    public function empenho():void{$this->executar(fn()=>$this->cadastro->criarEmpenho($_POST),'Empenho de pagamento cadastrado.');}
    public function tipoDocumento():void{$this->executar(fn()=>$this->cadastro->criarTipoDocumento($_POST),'Tipo de documento cadastrado.');}
    public function tipoObrigacao():void{$this->executar(fn()=>$this->cadastro->criarTipoObrigacao($_POST),'Tipo de obrigação cadastrado.');}
    private function executar(callable $acao,string $sucesso):never{try{$acao();$_SESSION['flash']=['success',$sucesso];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/cadastros');}
}
