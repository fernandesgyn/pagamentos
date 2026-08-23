<?php
declare(strict_types=1);
final class FilaController{
    private Fila $fila;
    public function __construct(){Auth::requireLogin();$this->fila=new Fila();}
    public function inspecoes():void{Auth::requirePermission('inspecao.gerir');View::render('paginas/fila_inspecoes',['titulo'=>'Fila de Inspeção','itens'=>$this->fila->inspecoes()]);}
    public function programacao():void{Auth::requirePermission('parcela.gerir');View::render('paginas/fila_programacao',['titulo'=>'Programação para pagamento','itens'=>$this->fila->programacao()]);}
    public function liquidacoes():void{Auth::requirePermission('liquidacao.gerir');View::render('paginas/fila_liquidacoes',['titulo'=>'Fila de Liquidação','itens'=>$this->fila->liquidacoes()]);}
    public function cmdf():void{Auth::requirePermission('cmdf.gerir');View::render('paginas/fila_cmdf',['titulo'=>'Fila CMDF','itens'=>$this->fila->cmdf()]);}
}
