<?php
declare(strict_types=1);

final class Fila
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function inspecoes():array
    {
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,d.data_emissao,d.data_envio_cooinsp,d.valor_bruto,d.valor_liquido,d.data_lancamento,
            td.nome tipo_documento,f.razao_social fornecedor,o.numero obrigacao_numero,o.ano obrigacao_ano,s.nome status_inspecao,s.permite_avancar,i.data_conclusao
          FROM inspecoes i JOIN documentos_pagamento d ON d.id=i.documento_id JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id JOIN status_inspecao s ON s.id=i.status_id
          ORDER BY s.encerra_inspecao,s.ordem,d.data_lancamento DESC")->fetchAll();
    }

    public function programacao():array
    {
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,d.valor_liquido,td.nome tipo_documento,f.razao_social fornecedor,s.nome status_inspecao,
            COALESCE(SUM(p.valor_liquido),0) valor_programado,(d.valor_liquido-COALESCE(SUM(p.valor_liquido),0)) saldo_programar
          FROM documentos_pagamento d JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN inspecoes i ON i.documento_id=d.id JOIN status_inspecao s ON s.id=i.status_id LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id
          WHERE s.permite_avancar=1 GROUP BY d.id,d.numero,d.valor_liquido,td.nome,f.razao_social,s.nome
          ORDER BY CASE WHEN ROUND(d.valor_liquido-COALESCE(SUM(p.valor_liquido),0),2)=0 THEN 1 ELSE 0 END,d.data_lancamento DESC")->fetchAll();
    }

    public function liquidacoes():array
    {
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,td.nome tipo_documento,f.razao_social fornecedor,
            p.id parcela_id,p.numero_parcela,p.valor_liquido,p.numero_empenho,p.tipo,p.data_vencimento,p.ipof,p.ap_benner,p.sequencial,p.grupo_despesa,
            nd.codigo natureza_codigo,fr.codigo fonte_codigo,ori.codigo origem_codigo,l.status,l.data_liquidacao,g.id cmdf_grupo_id,g.status status_cmdf
          FROM liquidacoes l JOIN parcelas_pagamento p ON p.id=l.parcela_id JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id JOIN obrigacoes o ON o.id=d.obrigacao_id JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN naturezas_despesa nd ON nd.id=p.natureza_despesa_id JOIN fontes_recurso fr ON fr.id=p.fonte_recurso_id JOIN origens_recurso ori ON ori.id=p.origem_recurso_id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id LEFT JOIN cmdf_grupos g ON g.id=gp.grupo_id
          WHERE ROUND((SELECT COALESCE(SUM(px.valor_liquido),0) FROM parcelas_pagamento px WHERE px.documento_id=d.id),2)=ROUND(d.valor_liquido,2)
          ORDER BY CASE l.status WHEN 'AGUARDANDO' THEN 0 WHEN 'LIQUIDADA' THEN 1 ELSE 2 END,d.data_lancamento DESC,p.numero_parcela")->fetchAll();
    }
}
