<?php
declare(strict_types=1);
final class HomeController{
  private FluxoPagamento $fluxo;
  public function __construct(){ $this->fluxo=new FluxoPagamento(); }
  public function index():void{View::render('paginas/dashboard',['titulo'=>'Painel','dados'=>$this->fluxo->dashboard()]);}
  public function obrigacoes():void{View::render('paginas/obrigacoes',['titulo'=>'Obrigações','obrigacoes'=>$this->fluxo->obrigacoes(),'fornecedores'=>$this->fluxo->fornecedores(),'tipos'=>$this->fluxo->tiposObrigacao()]);}
  public function salvarObrigacao():void{try{$this->fluxo->criarObrigacao($_POST);$_SESSION['flash']=['success','Obrigação cadastrada.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/obrigacoes');}
  public function documentos():void{View::render('paginas/documentos',['titulo'=>'Documentos para pagamento','documentos'=>$this->fluxo->documentos(),'obrigacoes'=>$this->fluxo->obrigacoes(),'tipos'=>$this->fluxo->tiposDocumento()]);}
  public function salvarDocumento():void{try{$id=$this->fluxo->criarDocumento($_POST);$_SESSION['flash']=['success','Documento lançado e enviado à fila de inspeção.'];redirect('/documentos/'.$id);}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];redirect('/documentos');}}
  public function documento(string $id):void{$doc=$this->fluxo->documento((int)$id);if(!$doc){http_response_code(404);echo 'Documento não encontrado';return;}View::render('paginas/documento',['titulo'=>'Documento '.$doc['numero'],'doc'=>$doc,'status'=>$this->fluxo->statusInspecao(),'parcelas'=>$this->fluxo->parcelas((int)$id),'empenhos'=>$this->fluxo->empenhos(),'componentes'=>$this->fluxo->componentes(),'fechado'=>$this->fluxo->documentoFechado((int)$id)]);}
  public function inspecao(string $id):void{try{$this->fluxo->atualizarInspecao((int)$id,$_POST);$_SESSION['flash']=['success','Inspeção atualizada.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/documentos/'.$id);}
  public function parcela(string $id):void{try{$this->fluxo->adicionarParcela((int)$id,$_POST);$_SESSION['flash']=['success','Parcela adicionada.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/documentos/'.$id);}
  public function componente(string $documentoId,string $parcelaId):void{try{$this->fluxo->adicionarComponente((int)$parcelaId,$_POST);$_SESSION['flash']=['success','Componente adicionado.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/documentos/'.$documentoId);}
  public function liquidar(string $documentoId,string $parcelaId):void{try{$this->fluxo->concluirLiquidacao((int)$parcelaId,(string)($_POST['data_liquidacao']??date('Y-m-d')));$_SESSION['flash']=['success','Liquidação concluída e CMDF liberada.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/documentos/'.$documentoId);}
  public function cmdf(string $documentoId,string $parcelaId):void{try{$this->fluxo->concluirCmdf((int)$parcelaId,$_POST);$_SESSION['flash']=['success','CMDF concluída.'];}catch(Throwable $e){$_SESSION['flash']=['danger',$e->getMessage()];}redirect('/documentos/'.$documentoId);}
}
