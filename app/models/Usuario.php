<?php
declare(strict_types=1);
final class Usuario{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function todos():array{return $this->db->query("SELECT u.id,u.perfil_id,u.nome,u.login,u.email,u.ativo,u.trocar_senha,p.nome perfil FROM usuarios u JOIN perfis p ON p.id=u.perfil_id ORDER BY u.nome")->fetchAll();}
    public function perfis(bool $somenteAtivos=false):array{return $this->db->query("SELECT id,nome,ativo FROM perfis ".($somenteAtivos?'WHERE ativo=1 ':'')."ORDER BY nome")->fetchAll();}
    public function buscar(int $id):?array{$st=$this->db->prepare("SELECT id,perfil_id,nome,login,email,ativo,trocar_senha FROM usuarios WHERE id=?");$st->execute([$id]);$r=$st->fetch();return $r?:null;}

    public function salvar(array $d,?int $id=null):void{
        $nome=trim((string)($d['nome']??''));$login=trim((string)($d['login']??''));$email=trim((string)($d['email']??''));$senha=(string)($d['senha']??'');$perfil=(int)($d['perfil_id']??0);
        if($nome===''||$login===''||$perfil<=0)throw new InvalidArgumentException('Informe nome, login e perfil.');
        if(!$id&&strlen($senha)<8)throw new InvalidArgumentException('A senha inicial deve possuir pelo menos 8 caracteres.');
        if($id&&$senha!==''&&strlen($senha)<8)throw new InvalidArgumentException('A nova senha deve possuir pelo menos 8 caracteres.');
        $ativo=isset($d['ativo'])?1:0;$trocar=isset($d['trocar_senha'])?1:0;
        if($id){
            if($senha!==''){$st=$this->db->prepare("UPDATE usuarios SET perfil_id=?,nome=?,login=?,email=?,senha_hash=?,ativo=?,trocar_senha=?,atualizado_em=NOW() WHERE id=?");$st->execute([$perfil,$nome,$login,$email?:null,password_hash($senha,PASSWORD_DEFAULT),$ativo,$trocar,$id]);}
            else{$st=$this->db->prepare("UPDATE usuarios SET perfil_id=?,nome=?,login=?,email=?,ativo=?,trocar_senha=?,atualizado_em=NOW() WHERE id=?");$st->execute([$perfil,$nome,$login,$email?:null,$ativo,$trocar,$id]);}
        }else{
            $st=$this->db->prepare("INSERT INTO usuarios(perfil_id,nome,login,email,senha_hash,ativo,trocar_senha) VALUES(?,?,?,?,?,?,?)");$st->execute([$perfil,$nome,$login,$email?:null,password_hash($senha,PASSWORD_DEFAULT),$ativo,$trocar]);
        }
    }

    public function excluir(int $id,int $usuarioAtualId):void{
        if($id<=0)throw new InvalidArgumentException('Usuário inválido.');
        if($id===$usuarioAtualId)throw new RuntimeException('Não é permitido excluir o próprio usuário logado.');
        try{$st=$this->db->prepare("DELETE FROM usuarios WHERE id=?");$st->execute([$id]);if($st->rowCount()===0)throw new RuntimeException('Usuário não encontrado.');}
        catch(PDOException $e){if((string)$e->getCode()==='23000')throw new RuntimeException('O usuário não pode ser excluído porque possui registros vinculados. Desative-o na tela de edição.');throw $e;}
    }
}
