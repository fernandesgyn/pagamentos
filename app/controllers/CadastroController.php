<?php
declare(strict_types=1);
final class CadastroController{
    private Cadastro $cadastro;
    public function __construct(){Auth::requirePermission('cadastro.gerir');$this->cadastro=new Cadastro();}

    public function fornecedores():void{$this->lista('Fornecedores','Fornecedores','/fornecedores','Novo fornecedor','fornecedores-table',['nome'=>'Nome','documento'=>'CPF/CNPJ','ativo'=>'Ativo'],$this->cadastro->fornecedores());}
    public function novoFornecedor():void{$this->form('Novo fornecedor','/fornecedores','/fornecedores',['nome'=>'Nome','documento'=>'CPF/CNPJ','ativo'=>'Ativo'],null,['ativo']);}
    public function editarFornecedor(string $id):void{$this->formRegistro('Editar fornecedor','/fornecedores','/fornecedores/'.$id,$this->cadastro->fornecedor((int)$id),['nome'=>'Nome','documento'=>'CPF/CNPJ','ativo'=>'Ativo'],['ativo']);}
    public function salvarFornecedor():void{$this->executar(fn()=>$this->cadastro->salvarFornecedor($_POST),'Fornecedor cadastrado.','/fornecedores','/fornecedores/novo');}
    public function atualizarFornecedor(string $id):void{$this->executar(fn()=>$this->cadastro->salvarFornecedor($_POST,(int)$id),'Fornecedor atualizado.','/fornecedores','/fornecedores/'.$id.'/editar');}
    public function excluirFornecedor(string $id):void{$this->executar(fn()=>$this->cadastro->excluirFornecedor((int)$id),'Fornecedor excluído.','/fornecedores','/fornecedores');}

    public function empenhos():void{$this->lista('Empenhos de pagamento','Empenhos de pagamento','/empenhos-pagamento','Novo empenho','empenhos-table',['numero'=>'Número','ano'=>'Ano','natureza'=>'Natureza','exercicio'=>'Exercício','origem_recurso'=>'Origem recurso','fonte'=>'Fonte','cmdf'=>'CMDF','ativo'=>'Ativo'],$this->cadastro->empenhos());}
    public function novoEmpenho():void{$this->form('Novo empenho de pagamento','/empenhos-pagamento','/empenhos-pagamento',['numero'=>'Número','ano'=>'Ano','natureza'=>'Natureza','exercicio'=>'Exercício','origem_recurso'=>'Origem recurso','fonte'=>'Fonte','cmdf'=>'CMDF','ativo'=>'Ativo'],null,['ativo'],['ano','exercicio']);}
    public function editarEmpenho(string $id):void{$this->formRegistro('Editar empenho de pagamento','/empenhos-pagamento','/empenhos-pagamento/'.$id,$this->cadastro->empenho((int)$id),['numero'=>'Número','ano'=>'Ano','natureza'=>'Natureza','exercicio'=>'Exercício','origem_recurso'=>'Origem recurso','fonte'=>'Fonte','cmdf'=>'CMDF','ativo'=>'Ativo'],['ativo'],['ano','exercicio']);}
    public function salvarEmpenho():void{$this->executar(fn()=>$this->cadastro->salvarEmpenho($_POST),'Empenho cadastrado.','/empenhos-pagamento','/empenhos-pagamento/novo');}
    public function atualizarEmpenho(string $id):void{$this->executar(fn()=>$this->cadastro->salvarEmpenho($_POST,(int)$id),'Empenho atualizado.','/empenhos-pagamento','/empenhos-pagamento/'.$id.'/editar');}
    public function excluirEmpenho(string $id):void{$this->executar(fn()=>$this->cadastro->excluirEmpenho((int)$id),'Empenho excluído.','/empenhos-pagamento','/empenhos-pagamento');}

    public function tiposDocumento():void{$this->lista('Tipos de documento','Tipos de documento para pagamento','/tipos-documento','Novo tipo','tipos-documento-table',['nome'=>'Nome','exige_numero'=>'Exige número','ativo'=>'Ativo'],$this->cadastro->tiposDocumento());}
    public function novoTipoDocumento():void{$this->form('Novo tipo de documento','/tipos-documento','/tipos-documento',['nome'=>'Nome','exige_numero'=>'Exige número','ativo'=>'Ativo'],null,['exige_numero','ativo']);}
    public function editarTipoDocumento(string $id):void{$this->formRegistro('Editar tipo de documento','/tipos-documento','/tipos-documento/'.$id,$this->cadastro->tipoDocumento((int)$id),['nome'=>'Nome','exige_numero'=>'Exige número','ativo'=>'Ativo'],['exige_numero','ativo']);}
    public function salvarTipoDocumento():void{$this->executar(fn()=>$this->cadastro->salvarTipoDocumento($_POST),'Tipo de documento cadastrado.','/tipos-documento','/tipos-documento/novo');}
    public function atualizarTipoDocumento(string $id):void{$this->executar(fn()=>$this->cadastro->salvarTipoDocumento($_POST,(int)$id),'Tipo de documento atualizado.','/tipos-documento','/tipos-documento/'.$id.'/editar');}
    public function excluirTipoDocumento(string $id):void{$this->executar(fn()=>$this->cadastro->excluirTipoDocumento((int)$id),'Tipo de documento excluído.','/tipos-documento','/tipos-documento');}

    public function tiposObrigacao():void{$this->lista('Tipos de obrigação','Tipos de obrigação','/tipos-obrigacao','Novo tipo','tipos-obrigacao-table',['nome'=>'Nome','exige_numero_ano'=>'Exige nº/ano','ativo'=>'Ativo'],$this->cadastro->tiposObrigacao());}
    public function novoTipoObrigacao():void{$this->form('Novo tipo de obrigação','/tipos-obrigacao','/tipos-obrigacao',['nome'=>'Nome','exige_numero_ano'=>'Exige nº/ano','ativo'=>'Ativo'],null,['exige_numero_ano','ativo']);}
    public function editarTipoObrigacao(string $id):void{$this->formRegistro('Editar tipo de obrigação','/tipos-obrigacao','/tipos-obrigacao/'.$id,$this->cadastro->tipoObrigacao((int)$id),['nome'=>'Nome','exige_numero_ano'=>'Exige nº/ano','ativo'=>'Ativo'],['exige_numero_ano','ativo']);}
    public function salvarTipoObrigacao():void{$this->executar(fn()=>$this->cadastro->salvarTipoObrigacao($_POST),'Tipo de obrigação cadastrado.','/tipos-obrigacao','/tipos-obrigacao/novo');}
    public function atualizarTipoObrigacao(string $id):void{$this->executar(fn()=>$this->cadastro->salvarTipoObrigacao($_POST,(int)$id),'Tipo de obrigação atualizado.','/tipos-obrigacao','/tipos-obrigacao/'.$id.'/editar');}
    public function excluirTipoObrigacao(string $id):void{$this->executar(fn()=>$this->cadastro->excluirTipoObrigacao((int)$id),'Tipo de obrigação excluído.','/tipos-obrigacao','/tipos-obrigacao');}

    private function lista(string $titulo,string $tituloCard,string $baseUrl,string $novoLabel,string $tableId,array $campos,array $registros):void{View::render('paginas/cadastros/index',compact('titulo','tituloCard','baseUrl','novoLabel','tableId','campos','registros'));}
    private function form(string $titulo,string $baseUrl,string $action,array $campos,?array $registro=null,array $checkboxes=[],array $numericos=[]):void{View::render('paginas/cadastros/form',compact('titulo','baseUrl','action','campos','registro','checkboxes','numericos'));}
    private function formRegistro(string $titulo,string $baseUrl,string $action,?array $registro,array $campos,array $checkboxes=[],array $numericos=[]):void{if(!$registro){http_response_code(404);echo 'Registro não encontrado';return;}$this->form($titulo,$baseUrl,$action,$campos,$registro,$checkboxes,$numericos);}
    private function executar(callable $acao,string $sucesso,string $destino,string $erroDestino):never{try{$acao();$_SESSION['flash']=['success',$sucesso];redirect($destino);}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];redirect($erroDestino);}}
}
