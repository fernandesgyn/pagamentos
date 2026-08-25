<?php
declare(strict_types=1);

final class CadastroController
{
    private Cadastro $cadastro;
    public function __construct(){Auth::requirePermission('cadastro.gerir');$this->cadastro=new Cadastro();}

    public function fornecedores():void{$this->listar('Fornecedores','/fornecedores',$this->cadastro->fornecedores(),['razao_social'=>'Razão Social / Nome','documento'=>'CPF/CNPJ','tipo_pessoa'=>'Tipo','ativo'=>'Ativo']);}
    public function novoFornecedor():void{$this->form('Novo fornecedor','/fornecedores','/fornecedores',null,['razao_social'=>'Razão Social / Nome','documento'=>'CPF/CNPJ','tipo_pessoa'=>'Tipo de pessoa','ativo'=>'Ativo'],[],[],['tipo_pessoa'=>['PF'=>'Pessoa Física','PJ'=>'Pessoa Jurídica']]);}
    public function editarFornecedor(string $id):void{$this->formCadastro('Editar fornecedor','/fornecedores','/fornecedores/'.$id,$this->cadastro->fornecedor((int)$id),['razao_social'=>'Razão Social / Nome','documento'=>'CPF/CNPJ','tipo_pessoa'=>'Tipo de pessoa','ativo'=>'Ativo'],[],[],['tipo_pessoa'=>['PF'=>'Pessoa Física','PJ'=>'Pessoa Jurídica']]);}
    public function salvarFornecedor():void{$this->executar(fn()=>$this->cadastro->salvarFornecedor($_POST),'Fornecedor cadastrado.','/fornecedores','/fornecedores/novo');}
    public function atualizarFornecedor(string $id):void{$this->executar(fn()=>$this->cadastro->salvarFornecedor($_POST,(int)$id),'Fornecedor atualizado.','/fornecedores','/fornecedores/'.$id.'/editar');}
    public function excluirFornecedor(string $id):void{$this->executar(fn()=>$this->cadastro->excluirFornecedor((int)$id),'Fornecedor excluído.','/fornecedores','/fornecedores');}

    public function fontes():void{$this->listar('Fontes de recurso','/fontes-recurso',$this->cadastro->fontesRecurso(),['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function novaFonte():void{$this->form('Nova fonte de recurso','/fontes-recurso','/fontes-recurso',null,['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function editarFonte(string $id):void{$this->formCadastro('Editar fonte de recurso','/fontes-recurso','/fontes-recurso/'.$id,$this->cadastro->fonteRecurso((int)$id),['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function salvarFonte():void{$this->executar(fn()=>$this->cadastro->salvarFonteRecurso($_POST),'Fonte cadastrada.','/fontes-recurso','/fontes-recurso/nova');}
    public function atualizarFonte(string $id):void{$this->executar(fn()=>$this->cadastro->salvarFonteRecurso($_POST,(int)$id),'Fonte atualizada.','/fontes-recurso','/fontes-recurso/'.$id.'/editar');}
    public function excluirFonte(string $id):void{$this->executar(fn()=>$this->cadastro->excluirFonteRecurso((int)$id),'Fonte excluída.','/fontes-recurso','/fontes-recurso');}

    public function naturezas():void{$this->listar('Naturezas da despesa','/naturezas-despesa',$this->cadastro->naturezasDespesa(),['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function novaNatureza():void{$this->form('Nova natureza da despesa','/naturezas-despesa','/naturezas-despesa',null,['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function editarNatureza(string $id):void{$this->formCadastro('Editar natureza da despesa','/naturezas-despesa','/naturezas-despesa/'.$id,$this->cadastro->naturezaDespesa((int)$id),['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function salvarNatureza():void{$this->executar(fn()=>$this->cadastro->salvarNaturezaDespesa($_POST),'Natureza cadastrada.','/naturezas-despesa','/naturezas-despesa/nova');}
    public function atualizarNatureza(string $id):void{$this->executar(fn()=>$this->cadastro->salvarNaturezaDespesa($_POST,(int)$id),'Natureza atualizada.','/naturezas-despesa','/naturezas-despesa/'.$id.'/editar');}
    public function excluirNatureza(string $id):void{$this->executar(fn()=>$this->cadastro->excluirNaturezaDespesa((int)$id),'Natureza excluída.','/naturezas-despesa','/naturezas-despesa');}

    public function origensRecurso():void{$this->listar('Origens do Recurso','/origens-recurso',$this->cadastro->origensRecurso(),['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function novaOrigemRecurso():void{$this->form('Nova Origem do Recurso','/origens-recurso','/origens-recurso',null,['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function editarOrigemRecurso(string $id):void{$this->formCadastro('Editar Origem do Recurso','/origens-recurso','/origens-recurso/'.$id,$this->cadastro->origemRecurso((int)$id),['codigo'=>'Código','nome'=>'Descrição','ativo'=>'Ativo']);}
    public function salvarOrigemRecurso():void{$this->executar(fn()=>$this->cadastro->salvarOrigemRecurso($_POST),'Origem do Recurso cadastrada.','/origens-recurso','/origens-recurso/nova');}
    public function atualizarOrigemRecurso(string $id):void{$this->executar(fn()=>$this->cadastro->salvarOrigemRecurso($_POST,(int)$id),'Origem do Recurso atualizada.','/origens-recurso','/origens-recurso/'.$id.'/editar');}
    public function excluirOrigemRecurso(string $id):void{$this->executar(fn()=>$this->cadastro->excluirOrigemRecurso((int)$id),'Origem do Recurso excluída.','/origens-recurso','/origens-recurso');}

    public function tiposDocumento():void{$this->listar('Tipos de documento','/tipos-documento',$this->cadastro->tiposDocumento(),['nome'=>'Tipo','exige_numero'=>'Exige número','ativo'=>'Ativo']);}
    public function novoTipoDocumento():void{$this->form('Novo tipo de documento','/tipos-documento','/tipos-documento',null,['nome'=>'Nome','exige_numero'=>'Exige número','ativo'=>'Ativo'],['exige_numero','ativo']);}
    public function editarTipoDocumento(string $id):void{$this->formCadastro('Editar tipo de documento','/tipos-documento','/tipos-documento/'.$id,$this->cadastro->tipoDocumento((int)$id),['nome'=>'Nome','exige_numero'=>'Exige número','ativo'=>'Ativo'],['exige_numero','ativo']);}
    public function salvarTipoDocumento():void{$this->executar(fn()=>$this->cadastro->salvarTipoDocumento($_POST),'Tipo cadastrado.','/tipos-documento','/tipos-documento/novo');}
    public function atualizarTipoDocumento(string $id):void{$this->executar(fn()=>$this->cadastro->salvarTipoDocumento($_POST,(int)$id),'Tipo atualizado.','/tipos-documento','/tipos-documento/'.$id.'/editar');}
    public function excluirTipoDocumento(string $id):void{$this->executar(fn()=>$this->cadastro->excluirTipoDocumento((int)$id),'Tipo excluído.','/tipos-documento','/tipos-documento');}

    public function tiposObrigacao():void{$this->listar('Tipos de obrigação','/tipos-obrigacao',$this->cadastro->tiposObrigacao(),['nome'=>'Tipo','exige_numero_ano'=>'Exige nº/ano','ativo'=>'Ativo']);}
    public function novoTipoObrigacao():void{$this->form('Novo tipo de obrigação','/tipos-obrigacao','/tipos-obrigacao',null,['nome'=>'Nome','exige_numero_ano'=>'Exige nº/ano','ativo'=>'Ativo'],['exige_numero_ano','ativo']);}
    public function editarTipoObrigacao(string $id):void{$this->formCadastro('Editar tipo de obrigação','/tipos-obrigacao','/tipos-obrigacao/'.$id,$this->cadastro->tipoObrigacao((int)$id),['nome'=>'Nome','exige_numero_ano'=>'Exige nº/ano','ativo'=>'Ativo'],['exige_numero_ano','ativo']);}
    public function salvarTipoObrigacao():void{$this->executar(fn()=>$this->cadastro->salvarTipoObrigacao($_POST),'Tipo cadastrado.','/tipos-obrigacao','/tipos-obrigacao/novo');}
    public function atualizarTipoObrigacao(string $id):void{$this->executar(fn()=>$this->cadastro->salvarTipoObrigacao($_POST,(int)$id),'Tipo atualizado.','/tipos-obrigacao','/tipos-obrigacao/'.$id.'/editar');}
    public function excluirTipoObrigacao(string $id):void{$this->executar(fn()=>$this->cadastro->excluirTipoObrigacao((int)$id),'Tipo excluído.','/tipos-obrigacao','/tipos-obrigacao');}

    private function listar(string $titulo,string $baseUrl,array $registros,array $campos):void{View::render('paginas/cadastros/index',compact('titulo','baseUrl','registros','campos'));}
    private function form(string $titulo,string $baseUrl,string $action,?array $registro,array $campos,array $checkboxes=[],array $numericos=[],array $selects=[]):void{View::render('paginas/cadastros/form',compact('titulo','baseUrl','action','registro','campos','checkboxes','numericos','selects'));}
    private function formCadastro(string $titulo,string $baseUrl,string $action,?array $registro,array $campos,array $checkboxes=[],array $numericos=[],array $selects=[]):void{if(!$registro){http_response_code(404);echo 'Registro não encontrado';return;}$this->form($titulo,$baseUrl,$action,$registro,$campos,$checkboxes,$numericos,$selects);}
    private function executar(callable $acao,string $sucesso,string $destino,string $erroDestino):never{try{$acao();$_SESSION['flash']=['success',$sucesso];redirect($destino);}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];redirect($erroDestino);}}
}
