<?php
declare(strict_types=1);
final class Perfil{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function todos():array{
        return $this->db->query("SELECT p.id,p.nome,p.descricao,p.ativo,COUNT(DISTINCT u.id) usuarios_total,COUNT(DISTINCT pp.permissao_id) permissoes_total FROM perfis p LEFT JOIN usuarios u ON u.perfil_id=p.id LEFT JOIN perfil_permissoes pp ON pp.perfil_id=p.id GROUP BY p.id,p.nome,p.descricao,p.ativo ORDER BY p.nome")->fetchAll();
    }
    public function buscar(int $id):?array{$st=$this->db->prepare("SELECT id,nome,descricao,ativo FROM perfis WHERE id=?");$st->execute([$id]);$r=$st->fetch();return $r?:null;}
    public function permissoes():array{
        $rows=$this->db->query("SELECT id,chave,nome FROM permissoes ORDER BY chave")->fetchAll();
        foreach($rows as &$r){$partes=explode('.',(string)$r['chave'],2);$r['modulo']=$partes[0]??'Geral';$r['acao']=$partes[1]??$r['chave'];$r['descricao']=$r['nome'];}unset($r);return $rows;
    }
    public function selecionadas(int $perfilId):array{$st=$this->db->prepare("SELECT permissao_id FROM perfil_permissoes WHERE perfil_id=? ORDER BY permissao_id");$st->execute([$perfilId]);return array_map('intval',$st->fetchAll(PDO::FETCH_COLUMN));}

    public function salvar(array $d,?int $id=null):int{
        if($id===1)throw new RuntimeException('O perfil Administrador é protegido contra alterações.');
        $nome=trim((string)($d['nome']??''));$descricao=trim((string)($d['descricao']??''));$ativo=isset($d['ativo'])?1:0;
        if($nome==='')throw new InvalidArgumentException('Informe o nome do perfil.');
        $ids=array_values(array_unique(array_filter(array_map('intval',(array)($d['permissoes']??[])),fn($v)=>$v>0)));
        $this->db->beginTransaction();
        try{
            if($id){$st=$this->db->prepare("UPDATE perfis SET nome=?,descricao=?,ativo=? WHERE id=?");$st->execute([$nome,$descricao?:null,$ativo,$id]);$perfilId=$id;}
            else{$st=$this->db->prepare("INSERT INTO perfis(nome,descricao,ativo) VALUES(?,?,?)");$st->execute([$nome,$descricao?:null,$ativo]);$perfilId=(int)$this->db->lastInsertId();}
            $this->db->prepare("DELETE FROM perfil_permissoes WHERE perfil_id=?")->execute([$perfilId]);
            if($ids){$ins=$this->db->prepare("INSERT INTO perfil_permissoes(perfil_id,permissao_id) VALUES(?,?)");foreach($ids as $permissaoId)$ins->execute([$perfilId,$permissaoId]);}
            $this->db->commit();return $perfilId;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    public function excluir(int $id):void{
        if($id===1)throw new RuntimeException('O perfil Administrador não pode ser excluído.');
        $st=$this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil_id=?");$st->execute([$id]);if((int)$st->fetchColumn()>0)throw new RuntimeException('O perfil não pode ser excluído porque possui usuários vinculados.');
        $del=$this->db->prepare("DELETE FROM perfis WHERE id=?");$del->execute([$id]);if($del->rowCount()===0)throw new RuntimeException('Perfil não encontrado.');
    }
}
