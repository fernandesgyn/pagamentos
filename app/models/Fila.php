<?php
declare(strict_types=1);

final class Fila
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function inspecoes():array
    {
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,d.data_emissao,d.data_envio_cooinsp,d.valor_liquido,d.data_lancamento,
            td.nome tipo_documento,f.razao_social fornecedor,s.nome status_inspecao,s.permite_avancar,s.encerra_inspecao,i.data_conclusao,
            COUNT(p.id) parcelas_total
          FROM inspecoes i
          JOIN documentos_pagamento d ON d.id=i.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN status_inspecao s ON s.id=i.status_id
          LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id
          GROUP BY d.id,d.numero,d.data_emissao,d.data_envio_cooinsp,d.valor_liquido,d.data_lancamento,td.nome,f.razao_social,s.nome,s.permite_avancar,s.encerra_inspecao,i.data_conclusao,s.ordem
          ORDER BY s.encerra_inspecao,s.ordem,d.data_lancamento DESC")->fetchAll();
    }

    public function programacao():array
    {
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,d.valor_liquido,td.nome tipo_documento,f.razao_social fornecedor,
            COALESCE(SUM(p.valor_liquido),0) valor_programado,(d.valor_liquido-COALESCE(SUM(p.valor_liquido),0)) saldo_programar,
            COUNT(p.id) parcelas_total,
            SUM(CASE WHEN p.id IS NOT NULL AND l.status='AGUARDANDO' AND gp.parcela_id IS NULL AND pg.parcela_id IS NULL THEN 1 ELSE 0 END) parcelas_reversiveis,
            CASE WHEN COUNT(p.id)=1 AND SUM(CASE WHEN p.id IS NOT NULL AND l.status='AGUARDANDO' AND gp.parcela_id IS NULL AND pg.parcela_id IS NULL THEN 1 ELSE 0 END)=1 THEN MAX(p.id) ELSE NULL END parcela_unica_reversivel_id
          FROM documentos_pagamento d
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN inspecoes i ON i.documento_id=d.id
          JOIN status_inspecao s ON s.id=i.status_id
          LEFT JOIN parcelas_pagamento p ON p.documento_id=d.id
          LEFT JOIN liquidacoes l ON l.parcela_id=p.id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
          LEFT JOIN pagamentos pg ON pg.parcela_id=p.id
          WHERE s.permite_avancar=1
          GROUP BY d.id,d.numero,d.valor_liquido,td.nome,f.razao_social
          ORDER BY CASE WHEN ROUND(d.valor_liquido-COALESCE(SUM(p.valor_liquido),0),2)=0 THEN 1 ELSE 0 END,d.data_lancamento DESC")->fetchAll();
    }

    public function liquidacoes():array
    {
        return $this->db->query("SELECT d.id documento_id,d.numero documento_numero,td.nome tipo_documento,f.razao_social fornecedor,
            p.id parcela_id,p.numero_parcela,p.valor_liquido,p.numero_empenho,p.data_vencimento,
            l.status,l.data_liquidacao,gp.grupo_id cmdf_grupo_id
          FROM liquidacoes l
          JOIN parcelas_pagamento p ON p.id=l.parcela_id
          JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          LEFT JOIN cmdf_grupo_parcelas gp ON gp.parcela_id=p.id
          WHERE ROUND((SELECT COALESCE(SUM(px.valor_liquido),0) FROM parcelas_pagamento px WHERE px.documento_id=d.id),2)=ROUND(d.valor_liquido,2)
          ORDER BY CASE l.status WHEN 'AGUARDANDO' THEN 0 WHEN 'LIQUIDADA' THEN 1 ELSE 2 END,d.data_lancamento DESC,p.numero_parcela")->fetchAll();
    }
}
