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
}
