<?php
declare(strict_types=1);
final class Usuario{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }
    public function todos():array{return $this->db->query("SELECT u.id,u.nome,u.login,u.email,u.ativo,p.nome perfil FROM usuarios u JOIN perfis p ON p.id=u.perfil_id ORDER BY u.nome")->fetchAll();}
    public function perfis():array{return $this->db->query("SELECT id,nome FROM perfis WHERE ativo=1 ORDER BY nome")->fetchAll();}
    public function criar(array $d):void{
        $nome=trim((string)($d['nome']??''));$login=trim((string)($d['login']??''));$senha=(string)($d['senha']??'');$perfil=(int)($d['perfil_id']??0);
        if($nome===''||$login===''||strlen($senha)<8||!$perfil)throw new InvalidArgumentException('Informe nome, login, perfil e senha com pelo menos 8 caracteres.');
        $st=$this->db->prepare("INSERT INTO usuarios(perfil_id,nome,login,email,senha_hash) VALUES(?,?,?,?,?)");
        $st->execute([$perfil,$nome,$login,trim((string)($d['email']??''))?:null,password_hash($senha,PASSWORD_DEFAULT)]);
    }
    public function alternarAtivo(int $id,int $usuarioAtualId):bool{
        if($id<=0)throw new InvalidArgumentException('Usuário inválido.');
        if($id===$usuarioAtualId)throw new RuntimeException('Não é permitido desativar o próprio usuário logado.');
        $st=$this->db->prepare("SELECT ativo FROM usuarios WHERE id=?");$st->execute([$id]);$ativo=$st->fetchColumn();
        if($ativo===false)throw new RuntimeException('Usuário não encontrado.');
        $novo=(int)$ativo===1?0:1;
        $q=$this->db->prepare("UPDATE usuarios SET ativo=?,atualizado_em=NOW() WHERE id=?");$q->execute([$novo,$id]);
        return $novo===1;
    }
}
