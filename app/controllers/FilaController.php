<?php
declare(strict_types=1);

final class FilaController
{
    private Fila $fila;
    private FluxoPagamento $fluxo;
    private CmdfGrupo $cmdf;
    private FluxoReversao $reversao;

    public function __construct()
    {
        Auth::requireLogin();
        $this->fila = new Fila();
        $this->fluxo = new FluxoPagamento();
        $this->cmdf = new CmdfGrupo();
        $this->reversao = new FluxoReversao();
    }

    public function inspecoes(): void
    {
        Auth::requirePermission('inspecao.gerir');
        View::render('paginas/fila_inspecoes',['titulo'=>'Inspeção','itens'=>$this->fila->inspecoes()]);
    }

    public function inspecao(string $documentoId): void
    {
        Auth::requirePermission('inspecao.gerir');
        $doc = $this->fluxo->documento((int)$documentoId);
        if (!$doc) { http_response_code(404); echo 'Documento não encontrado'; return; }
        View::render('paginas/inspecao_form',['titulo'=>'Inspeção do documento '.$doc['numero'],'doc'=>$doc,'status'=>$this->fluxo->statusInspecao()]);
    }

    public function salvarInspecao(string $documentoId): void
    {
        Auth::requirePermission('inspecao.gerir');
        try {
            $atual = $this->fluxo->documento((int)$documentoId);
            if (!$atual) throw new RuntimeException('Documento não encontrado.');
            if (trim((string)($_POST['data_conclusao'] ?? '')) === '') {
                throw new InvalidArgumentException('Informe a Data de conclusão da Inspeção.');
            }
            if (!empty($atual['data_conclusao'])) {
                throw new RuntimeException('A Inspeção já está encerrada. Use “Reabrir Inspeção” para voltar ao fluxo anterior de forma auditada.');
            }
            $this->fluxo->atualizarInspecao((int)$documentoId,$_POST);
            $doc = $this->fluxo->documento((int)$documentoId);
            $_SESSION['flash']=['success',$doc && (bool)$doc['permite_avancar']
                ? 'Inspeção concluída. O documento está disponível na Programação.'
                : 'Inspeção atualizada.'];
            redirect('/inspecoes');
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/inspecoes/'.$documentoId);
        }
    }

    public function desfazerInspecao(string $documentoId): void
    {
        Auth::requirePermission('inspecao.gerir');
        try {
            $this->reversao->reabrirInspecao((int)$documentoId,$_POST['motivo'] ?? null);
            $_SESSION['flash']=['success','Conclusão da Inspeção desfeita. O documento voltou para Inspeção andamento.'];
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
        }
        redirect('/inspecoes/'.$documentoId);
    }

    public function programacao(): void
    {
        Auth::requirePermission('parcela.gerir');
        View::render('paginas/fila_programacao',['titulo'=>'Programação das parcelas','itens'=>$this->fila->programacao()]);
    }

    public function programar(string $documentoId): void
    {
        Auth::requirePermission('parcela.gerir');
        $doc = $this->fluxo->documento((int)$documentoId);
        if (!$doc) { http_response_code(404); echo 'Documento não encontrado'; return; }
        View::render('paginas/programacao_form',[
            'titulo'=>'Programação do documento '.$doc['numero'],
            'doc'=>$doc,
            'parcelas'=>$this->fluxo->parcelas((int)$documentoId),
            'fontes'=>$this->fluxo->fontesDaObrigacao((int)$doc['obrigacao_id']),
            'naturezas'=>$this->fluxo->naturezasDaObrigacao((int)$doc['obrigacao_id']),
            'origens'=>$this->fluxo->origensRecurso(),
            'fechada'=>$this->fluxo->documentoProgramacaoFechada((int)$documentoId),
        ]);
    }

    public function adicionarParcela(string $documentoId): void
    {
        Auth::requirePermission('parcela.gerir');
        try {
            $this->fluxo->adicionarParcela((int)$documentoId,$_POST);
            $_SESSION['flash']=['success',$this->fluxo->documentoProgramacaoFechada((int)$documentoId)
                ? 'Parcela adicionada e Programação fechada. As parcelas estão disponíveis para Liquidação.'
                : 'Parcela adicionada. A Programação permanece aberta até fechar o valor líquido do documento.'];
            redirect('/programacao');
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/programacao/'.$documentoId);
        }
    }

    public function desfazerProgramacao(string $documentoId,string $parcelaId): void
    {
        Auth::requirePermission('parcela.gerir');
        try {
            $this->reversao->desfazerProgramacao((int)$documentoId,(int)$parcelaId,$_POST['motivo'] ?? null);
            $_SESSION['flash']=['success','Programação da parcela desfeita. O saldo do documento foi reaberto para correção.'];
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
        }
        redirect('/programacao/'.$documentoId);
    }

    public function liquidacoes(): void
    {
        Auth::requirePermission('liquidacao.gerir');
        View::render('paginas/fila_liquidacoes',['titulo'=>'Liquidação','itens'=>$this->fila->liquidacoes()]);
    }

    public function liquidacao(string $parcelaId): void
    {
        Auth::requirePermission('liquidacao.gerir');
        $p = $this->fluxo->parcela((int)$parcelaId);
        if (!$p) { http_response_code(404); echo 'Parcela não encontrada'; return; }
        View::render('paginas/liquidacao_form',['titulo'=>'Liquidação da parcela '.$p['numero_parcela'],'p'=>$p]);
    }

    public function salvarLiquidacao(string $parcelaId): void
    {
        Auth::requirePermission('liquidacao.gerir');
        try {
            $atual = $this->fluxo->parcela((int)$parcelaId);
            if (!$atual) throw new RuntimeException('Parcela não encontrada.');
            if ((string)($atual['status_liquidacao'] ?? 'AGUARDANDO') !== 'AGUARDANDO') {
                throw new RuntimeException('A Liquidação já foi movimentada. Use “Desfazer Liquidação” antes de registrar uma nova situação.');
            }
            $this->fluxo->atualizarLiquidacao((int)$parcelaId,$_POST);
            $_SESSION['flash']=['success','Liquidação atualizada.'];
            redirect('/liquidacoes');
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/liquidacoes/'.$parcelaId);
        }
    }

    public function desfazerLiquidacao(string $parcelaId): void
    {
        Auth::requirePermission('liquidacao.gerir');
        try {
            $this->reversao->desfazerLiquidacao((int)$parcelaId,$_POST['motivo'] ?? null);
            $_SESSION['flash']=['success','Liquidação desfeita. A parcela voltou para Aguardando liquidação.'];
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
        }
        redirect('/liquidacoes/'.$parcelaId);
    }

    public function cmdf(): void
    {
        Auth::requirePermission('cmdf.gerir');
        View::render('paginas/fila_cmdf',['titulo'=>'CMDF','grupos'=>$this->cmdf->grupos(),'disponiveis'=>$this->cmdf->parcelasDisponiveis(),'sugestoes'=>$this->cmdf->sugestoes()]);
    }

    public function sugerirGruposCmdf(): void
    {
        Auth::requirePermission('cmdf.grupo.ajustar');
        try {
            $total = $this->cmdf->criarGruposSugeridos();
            $_SESSION['flash']=['success',$total > 0 ? $total.' grupo(s) CMDF criado(s) automaticamente.' : 'Não há novas parcelas compatíveis para agrupamento automático.'];
        } catch (Throwable $e) { $_SESSION['flash']=['danger',$e->getMessage()]; }
        redirect('/cmdf');
    }

    public function criarGrupoCmdf(): void
    {
        Auth::requirePermission('cmdf.grupo.ajustar');
        try {
            $this->cmdf->criarGrupoManual($_POST['parcelas_ids'] ?? []);
            $_SESSION['flash']=['success','Grupo CMDF criado.'];
            redirect('/cmdf');
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/cmdf');
        }
    }

    public function grupoCmdf(string $grupoId): void
    {
        Auth::requirePermission('cmdf.gerir');
        $grupo = $this->cmdf->grupo((int)$grupoId);
        if (!$grupo) { http_response_code(404); echo 'Grupo CMDF não encontrado'; return; }
        View::render('paginas/cmdf_grupo_form',[
            'titulo'=>'Grupo CMDF #'.$grupo['id'],
            'grupo'=>$grupo,
            'parcelas'=>$this->cmdf->parcelasDoGrupo((int)$grupoId),
            'candidatas'=>$grupo['status']==='FECHADA' ? $this->cmdf->candidatasCompativeis((int)$grupoId) : [],
        ]);
    }

    public function adicionarParcelasGrupoCmdf(string $grupoId): void
    {
        Auth::requirePermission('cmdf.grupo.ajustar');
        try {
            $this->cmdf->adicionarParcelas((int)$grupoId,$_POST['parcelas_ids'] ?? []);
            $_SESSION['flash']=['success','Parcela(s) adicionada(s) ao grupo CMDF.'];
        } catch (Throwable $e) { $_SESSION['flash']=['danger',$e->getMessage()]; }
        redirect('/cmdf/grupos/'.$grupoId);
    }

    public function removerParcelaGrupoCmdf(string $grupoId,string $parcelaId): void
    {
        Auth::requirePermission('cmdf.grupo.ajustar');
        try {
            $this->cmdf->removerParcela((int)$grupoId,(int)$parcelaId);
            $_SESSION['flash']=['success','Parcela removida do grupo CMDF.'];
        } catch (Throwable $e) { $_SESSION['flash']=['danger',$e->getMessage()]; }
        redirect('/cmdf/grupos/'.$grupoId);
    }

    public function salvarStatusGrupoCmdf(string $grupoId): void
    {
        Auth::requirePermission('cmdf.gerir');
        try {
            $this->cmdf->atualizarStatus((int)$grupoId,(string)($_POST['status'] ?? ''));
            $_SESSION['flash']=['success','Status do grupo CMDF atualizado.'];
            redirect('/cmdf');
        } catch (Throwable $e) {
            $_SESSION['flash']=['danger',$e->getMessage()];
            redirect('/cmdf/grupos/'.$grupoId);
        }
    }

    public function desfazerCmdf(string $grupoId): void
    {
        Auth::requirePermission('cmdf.gerir');
        try {
            $novo = $this->reversao->desfazerCmdf((int)$grupoId,$_POST['motivo'] ?? null);
            $_SESSION['flash']=['success','Última ação da CMDF desfeita. O grupo voltou para '.$novo.'.'];
        } catch (Throwable $e) { $_SESSION['flash']=['danger',$e->getMessage()]; }
        redirect('/cmdf/grupos/'.$grupoId);
    }
}
