<?php
declare(strict_types=1);
final class PagamentoController{
    private PagamentoFinal $pagamento;
    public function __construct(){Auth::requirePermission('pagamento.gerir');$this->pagamento=new PagamentoFinal();}
    public function pagar(string $documentoId,string $parcelaId):void{
        try{$this->pagamento->registrar((int)$parcelaId,$_POST);$_SESSION['flash']=['success','Pagamento registrado.'];}
        catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/documentos/'.$documentoId);
    }
}
