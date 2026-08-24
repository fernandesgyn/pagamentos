<?php
declare(strict_types=1);

final class PagamentoFinal
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function listar():array{
        return $this->db->query("SELECT pg.*,p.numero_parcela,p.valor_liquido,p.numero_empenho,p.tipo,
            d.id documento_id,d.numero documento_numero,td.nome tipo_documento,f.razao_social fornecedor,c.data_conclusao data_cmdf
          FROM pagamentos pg
          JOIN parcelas_pagamento p ON p.id=pg.parcela_id
          JOIN documentos_pagamento d ON d.id=p.documento_id
          JOIN tipos_documento_pagamento td ON td.id=d.tipo_documento_id
          JOIN obrigacoes o ON o.id=d.obrigacao_id
          JOIN fornecedores f ON f.id=o.fornecedor_id
          JOIN cmdf_etapas c ON c.parcela_id=p.id
          ORDER BY CASE pg.status WHEN 'AGUARDANDO' THEN 0 ELSE 1 END,d.data_lancamento DESC,p.numero_parcela")->fetchAll();
    }

    public function registrar(int $parcelaId,array $d):void{
        $st=$this->db->prepare("SELECT c.status,p.valor_liquido FROM parcelas_pagamento p JOIN cmdf_etapas c ON c.parcela_id=p.id WHERE p.id=?");
        $st->execute([$parcelaId]);$row=$st->fetch();
        if(!$row||$row['status']!=='LIQUIDADA')throw new RuntimeException('A CMDF precisa estar no status Liquidada antes do pagamento.');
        $data=(string)($d['data_pagamento']??'');
        if($data==='')throw new InvalidArgumentException('Informe a data do pagamento.');
        $valor=$this->decimal($d['valor_liquido_pago']??null);
        if($valor===null)$valor=(float)$row['valor_liquido'];
        if($valor<0||round($valor,2)>round((float)$row['valor_liquido'],2))throw new InvalidArgumentException('Valor líquido pago inválido.');

        $q=$this->db->prepare("UPDATE pagamentos SET status='PAGO',data_pagamento=?,valor_liquido_pago=?,historico_pagamento=?,benner_ap=?,usuario_id=?,atualizado_em=NOW() WHERE parcela_id=?");
        $q->execute([$data,$valor,trim((string)($d['historico_pagamento']??''))?:null,trim((string)($d['benner_ap']??''))?:null,(int)(Auth::user()['id']??0)?:null,$parcelaId]);
    }

    private function decimal(mixed $v):?float{
        if($v===null||$v==='')return null;
        $s=str_replace(['R$',' '],'',(string)$v);
        if(str_contains($s,','))$s=str_replace(['.',','],['','.'],$s);
        return is_numeric($s)?round((float)$s,2):null;
    }
}
