<?php
declare(strict_types=1);
final class CmdfEtapa{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }
    public function concluir(int $parcelaId,array $d):void{
        $st=$this->db->prepare("SELECT status FROM liquidacoes WHERE parcela_id=?");
        $st->execute([$parcelaId]);
        if($st->fetchColumn()!=='CONCLUIDA')throw new RuntimeException('A liquidação precisa estar concluída antes da CMDF.');
        $dataConclusao=(string)($d['data_conclusao']??'');
        if($dataConclusao==='')throw new InvalidArgumentException('Informe a data de conclusão da CMDF.');
        $this->db->beginTransaction();
        try{
            $q=$this->db->prepare("UPDATE cmdf_etapas SET status='CONCLUIDA',data_envio_seinfra=?,data_despacho_seinfra=?,data_envio_economia=?,data_atendimento_economia=?,data_conclusao=?,observacoes=?,atualizado_em=NOW() WHERE parcela_id=?");
            $q->execute([
                ($d['data_envio_seinfra']??'')?:null,
                ($d['data_despacho_seinfra']??'')?:null,
                ($d['data_envio_economia']??'')?:null,
                ($d['data_atendimento_economia']??'')?:null,
                $dataConclusao,
                trim((string)($d['observacoes']??''))?:null,
                $parcelaId
            ]);
            if($q->rowCount()===0){
                $check=$this->db->prepare("SELECT id FROM cmdf_etapas WHERE parcela_id=?");$check->execute([$parcelaId]);
                if(!$check->fetchColumn())throw new RuntimeException('Etapa CMDF não encontrada.');
            }
            $this->db->prepare("INSERT INTO pagamentos(parcela_id) VALUES(?) ON DUPLICATE KEY UPDATE parcela_id=VALUES(parcela_id)")->execute([$parcelaId]);
            $this->db->commit();
        }catch(Throwable $e){$this->db->rollBack();throw $e;}
    }
}
