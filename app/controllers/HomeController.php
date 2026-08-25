<?php
declare(strict_types=1);

final class HomeController
{
    private FluxoPagamento $fluxo;
    public function __construct(){Auth::requireLogin();$this->fluxo=new FluxoPagamento();}

    public function index():void{
        Auth::requirePermission('dashboard.ver');
        View::render('paginas/dashboard',['titulo'=>'Painel','dados'=>$this->fluxo->dashboard()]);
    }

    public function obrigacoes():void{
        Auth::requirePermission('obrigacao.gerir');
        View::render('paginas/obrigacoes',['titulo'=>'Obrigações','obrigacoes'=>$this->fluxo->obrigacoes()]);
    }
    public function novaObrigacao():void{
        Auth::requirePermission('obrigacao.gerir');
        View::render('paginas/obrigacao_form',[
            'titulo'=>'Nova obrigação',
            'fornecedores'=>$this->fluxo->fornecedores(),
            'tipos'=>$this->fluxo->tiposObrigacao(),
            'fontes'=>$this->fluxo->fontesRecurso(),
            'naturezas'=>$this->fluxo->naturezasDespesa(),
        ]);
    }
    public function salvarObrigacao():void{
        Auth::requirePermission('obrigacao.gerir');
        try{
            $id=$this->fluxo->criarObrigacao($_POST);
            $_SESSION['flash']=['success','Obrigação cadastrada.'];
            redirect('/documentos/novo?obrigacao_id='.$id);
        }catch(Throwable $e){
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/obrigacoes/nova');
        }
    }
    public function excluirObrigacao(string $id):void{
        Auth::requirePermission('obrigacao.gerir');
        try{
            (new FluxoReversao())->excluirObrigacao((int)$id,$_POST['motivo']??null);
            $_SESSION['flash']=['success','Obrigação excluída.'];
        }catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/obrigacoes');
    }

    public function documentos():void{
        Auth::requirePermission('documento.gerir');
        View::render('paginas/documentos',['titulo'=>'Documentos para pagamento','documentos'=>$this->fluxo->documentos()]);
    }
    public function novoDocumento():void{
        Auth::requirePermission('documento.gerir');
        View::render('paginas/documento_form',[
            'titulo'=>'Novo documento',
            'fornecedores'=>$this->fluxo->fornecedores(),
            'obrigacoes'=>$this->fluxo->obrigacoes(),
            'tipos'=>$this->fluxo->tiposDocumento(),
            'obrigacaoSelecionada'=>(int)($_GET['obrigacao_id']??0),
        ]);
    }
    public function salvarDocumento():void{
        Auth::requirePermission('documento.gerir');
        try{
            $id=$this->fluxo->criarDocumento($_POST);
            $_SESSION['flash']=['success','Documento lançado e enviado à fila de inspeção.'];
            redirect('/documentos/'.$id);
        }catch(Throwable $e){
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/documentos/novo');
        }
    }
    public function excluirDocumento(string $id):void{
        Auth::requirePermission('documento.gerir');
        try{
            (new FluxoReversao())->excluirDocumento((int)$id,$_POST['motivo']??null);
            $_SESSION['flash']=['success','Documento excluído.'];
        }catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/documentos');
    }
    public function documento(string $id):void{
        Auth::requirePermission('dashboard.ver');
        $doc=$this->fluxo->documento((int)$id);
        if(!$doc){http_response_code(404);echo 'Documento não encontrado';return;}
        View::render('paginas/documento',[
            'titulo'=>'Documento '.$doc['numero'],
            'doc'=>$doc,
            'parcelas'=>$this->fluxo->parcelas((int)$id),
            'programacaoFechada'=>$this->fluxo->documentoProgramacaoFechada((int)$id),
        ]);
    }
}
