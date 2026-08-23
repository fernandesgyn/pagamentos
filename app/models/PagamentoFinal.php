<?php
declare(strict_types=1);
final class PagamentoFinal{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }
    public function registrar(int $parcelaId,array $d):void{
        $st=$this->db->prepare("SELECT c.status,p.valor_total FROM parcelas_pagamento p JOIN cmdf_etapas c ON c.parcela_id=p.id WHERE p.id=?");
        $st->execute([$parcelaId]);$row=$st->fetch();
        if(!$row||$row['status']!=='CONCLUIDA')throw new RuntimeException('A CMDF precisa estar concluída antes do pagamento.');
        $data=(string)($d['data_pagamento']??'');if($data==='')throw new InvalidArgumentException('Informe a data do pagamento.');
        $valor=$this->decimal($d['valor_liquido_pago']??null);
        if($valor!==null&&$valor<0)throw new InvalidArgumentException('Valor líquido inválido.');
        $q=$this->db->prepare("UPDATE pagamentos SET status='PAGO',data_pagamento=?,valor_liquido_pago=?,historico_pagamento=?,benner_ap=?,atualizado_em=NOW() WHERE parcela_id=?");
        $q->execute([$data,$valor,trim((string)($d['historico_pagamento']??''))?:null,trim((string)($d['benner_ap']??''))?:null,$parcelaId]);
    }
    private function decimal(mixed $v):?float{
        if($v===null||$v==='')return null;$s=str_replace(['R$',' '],'',(string)$v);
        if(str_contains($s,','))$s=str_replace(['.',','],['','.'],$s);
        return is_numeric($s)?round((float)$s,2):null;
    }
}
