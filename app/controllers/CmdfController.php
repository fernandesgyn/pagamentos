<?php
declare(strict_types=1);
final class CmdfController{
    private CmdfEtapa $cmdf;
    public function __construct(){Auth::requirePermission('cmdf.gerir');$this->cmdf=new CmdfEtapa();}
    public function concluir(string $documentoId,string $parcelaId):void{
        try{$this->cmdf->concluir((int)$parcelaId,$_POST);$_SESSION['flash']=['success','CMDF concluída e parcela liberada para pagamento.'];}
        catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}
        redirect('/cmdf');
    }
}
