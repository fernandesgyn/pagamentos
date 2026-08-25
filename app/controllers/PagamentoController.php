<?php
declare(strict_types=1);

final class PagamentoController
{
    private PagamentoFinal $pagamento;
    private FluxoReversao $reversao;

    public function __construct()
    {
        Auth::requirePermission('pagamento.gerir');
        $this->pagamento = new PagamentoFinal();
        $this->reversao = new FluxoReversao();
    }

    public function index(): void
    {
        View::render('paginas/pagamentos',['titulo'=>'Pagamentos','pagamentos'=>$this->pagamento->listar()]);
    }

    public function pagar(string $documentoId,string $parcelaId): void
    {
        try {
            $this->pagamento->registrar((int)$parcelaId,$_POST);
            $_SESSION['flash']=['success','Pagamento registrado.'];
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
        }
        redirect('/pagamentos');
    }

    public function desfazer(string $documentoId,string $parcelaId): void
    {
        try {
            $this->reversao->desfazerPagamento((int)$parcelaId,$_POST['motivo'] ?? null);
            $_SESSION['flash']=['success','Pagamento desfeito. A parcela voltou para Aguardando pagamento.'];
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
        }
        redirect('/pagamentos');
    }
}
