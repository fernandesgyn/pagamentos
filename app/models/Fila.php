<?php
declare(strict_types=1);
final class Fila{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function inspecoes():array{
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,d.data_documento,d.valor_bruto,d.data_lancamento,td.nome tipo_documento,f.nome fornecedor,o.numero obrigacao_numero,o.ano obrigacao_ano,s.nome status_inspecao,s.permite_avancar,i.data_conclusao FROM inspecoes i JOIN documentos_pagamento d ON d.id=i.documento_id JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id JOIN status_inspecao s ON s.id=i.status_id ORDER BY s.encerra_inspecao,s.ordem,d.data_lancamento")->fetchAll();
    }

    public function programacao():array{
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,d.valor_bruto,td.nome tipo_documento,f.nome fornecedor,s.nome status_inspecao,COALESCE(SUM(p.valor_total),0) valor_programado,(d.valor_bruto-COALESCE(SUM(p.valor_total),0)) saldo_programar FROM documentos_pagamento d JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id JOIN inspecoes i ON i.documento_id=d.id JOIN status_inspecao s ON s.id=i.status_id LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id WHERE s.permite_avancar=1 GROUP BY d.id,d.numero,d.valor_bruto,td.nome,f.nome,s.nome ORDER BY CASE WHEN ROUND(d.valor_bruto-COALESCE(SUM(p.valor_total),0),2)=0 THEN 1 ELSE 0 END,d.data_lancamento")->fetchAll();
    }

    public function liquidacoes():array{
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,td.nome tipo_documento,f.nome fornecedor,p.id parcela_id,p.numero_parcela,p.valor_total,e.numero empenho_numero,e.ano empenho_ano,l.status,l.data_liquidacao,(SELECT COALESCE(SUM(pc.valor),0) FROM parcela_componentes pc WHERE pc.parcela_id=p.id) soma_componentes FROM liquidacoes l JOIN parcelas_pagamento p ON p.id=l.parcela_id JOIN documentos_pagamento d ON d.id=p.documento_id JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id JOIN empenhos_pagamento e ON e.id=p.empenho_pagamento_id ORDER BY CASE l.status WHEN 'AGUARDANDO' THEN 0 WHEN 'CONCLUIDA' THEN 1 ELSE 2 END,d.data_lancamento,p.numero_parcela")->fetchAll();
    }

    public function cmdf():array{
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,td.nome tipo_documento,f.nome fornecedor,p.id parcela_id,p.numero_parcela,p.valor_total,e.numero empenho_numero,e.ano empenho_ano,c.status,c.data_conclusao,l.data_liquidacao FROM cmdf_etapas c JOIN parcelas_pagamento p ON p.id=c.parcela_id JOIN documentos_pagamento d ON d.id=p.documento_id JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id JOIN empenhos_pagamento e ON e.id=p.empenho_pagamento_id JOIN liquidacoes l ON l.parcela_id=p.id ORDER BY CASE c.status WHEN 'AGUARDANDO' THEN 0 WHEN 'DEVOLVIDA' THEN 1 ELSE 2 END,d.data_lancamento,p.numero_parcela")->fetchAll();
    }
}
