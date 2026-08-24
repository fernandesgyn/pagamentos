<?php
declare(strict_types=1);

final class FilaController
{
    private Fila $fila;
    private FluxoPagamento $fluxo;
    public function __construct(){Auth::requireLogin();$this->fila=new Fila();$this->fluxo=new FluxoPagamento();}

    public function inspecoes():void{
        Auth::requirePermission('inspecao.gerir');
        View::render('paginas/fila_inspecoes',['titulo'=>'Inspeção','itens'=>$this->fila->inspecoes()]);
    }
    public function inspecao(string $documentoId):void{
        Auth::requirePermission('inspecao.gerir');
        $doc=$this->fluxo->documento((int)$documentoId);
        if(!$doc){http_response_code(404);echo 'Documento não encontrado';return;}
        View::render('paginas/inspecao_form',['titulo'=>'Inspeção do documento '.$doc['numero'],'doc'=>$doc,'status'=>$this->fluxo->statusInspecao()]);
    }
    public function salvarInspecao(string $documentoId):void{
        Auth::requirePermission('inspecao.gerir');
        try{$this->fluxo->atualizarInspecao((int)$documentoId,$_POST);$_SESSION['flash']=['success','Inspeção atualizada.'];}
        catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/inspecoes/'.$documentoId);
    }

    public function programacao():void{
        Auth::requirePermission('parcela.gerir');
        View::render('paginas/fila_programacao',['titulo'=>'Programação das parcelas','itens'=>$this->fila->programacao()]);
    }
    public function programar(string $documentoId):void{
        Auth::requirePermission('parcela.gerir');
        $doc=$this->fluxo->documento((int)$documentoId);
        if(!$doc){http_response_code(404);echo 'Documento não encontrado';return;}
        View::render('paginas/programacao_form',[
            'titulo'=>'Programação do documento '.$doc['numero'],
            'doc'=>$doc,
            'parcelas'=>$this->fluxo->parcelas((int)$documentoId),
            'fontes'=>$this->fluxo->fontesDaObrigacao((int)$doc['obrigacao_id']),
            'naturezas'=>$this->fluxo->naturezasDaObrigacao((int)$doc['obrigacao_id']),
            'tiposRecurso'=>$this->fluxo->tiposRecurso(),
            'fechada'=>$this->fluxo->documentoProgramacaoFechada((int)$documentoId),
        ]);
    }
    public function adicionarParcela(string $documentoId):void{
        Auth::requirePermission('parcela.gerir');
        try{$this->fluxo->adicionarParcela((int)$documentoId,$_POST);$_SESSION['flash']=['success','Parcela adicionada.'];}
        catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/programacao/'.$documentoId);
    }

    public function liquidacoes():void{
        Auth::requirePermission('liquidacao.gerir');
        View::render('paginas/fila_liquidacoes',['titulo'=>'Liquidação','itens'=>$this->fila->liquidacoes()]);
    }
    public function liquidacao(string $parcelaId):void{
        Auth::requirePermission('liquidacao.gerir');
        $p=$this->fluxo->parcela((int)$parcelaId);
        if(!$p){http_response_code(404);echo 'Parcela não encontrada';return;}
        View::render('paginas/liquidacao_form',['titulo'=>'Liquidação da parcela '.$p['numero_parcela'],'p'=>$p]);
    }
    public function salvarLiquidacao(string $parcelaId):void{
        Auth::requirePermission('liquidacao.gerir');
        try{$this->fluxo->atualizarLiquidacao((int)$parcelaId,$_POST);$_SESSION['flash']=['success','Liquidação atualizada.'];}
        catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/liquidacoes/'.$parcelaId);
    }

    public function cmdf():void{
        Auth::requirePermission('cmdf.gerir');
        View::render('paginas/fila_cmdf',['titulo'=>'CMDF','itens'=>$this->fila->cmdf()]);
    }
    public function cmdfParcela(string $parcelaId):void{
        Auth::requirePermission('cmdf.gerir');
        $p=$this->fluxo->parcela((int)$parcelaId);
        if(!$p){http_response_code(404);echo 'Parcela não encontrada';return;}
        View::render('paginas/cmdf_form',['titulo'=>'CMDF da parcela '.$p['numero_parcela'],'p'=>$p]);
    }
    public function salvarCmdf(string $parcelaId):void{
        Auth::requirePermission('cmdf.gerir');
        try{$this->fluxo->atualizarCmdf((int)$parcelaId,$_POST);$_SESSION['flash']=['success','CMDF atualizada.'];}
        catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/cmdf/'.$parcelaId);
    }
}
