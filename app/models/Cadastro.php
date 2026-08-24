<?php
declare(strict_types=1);
final class Cadastro{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function fornecedores():array{return $this->db->query("SELECT * FROM fornecedores ORDER BY ativo DESC,nome")->fetchAll();}
    public function empenhos():array{return $this->db->query("SELECT * FROM empenhos_pagamento ORDER BY ano DESC,numero")->fetchAll();}
    public function tiposDocumento():array{return $this->db->query("SELECT * FROM tipos_documento_pagamento ORDER BY ativo DESC,nome")->fetchAll();}
    public function tiposObrigacao():array{return $this->db->query("SELECT * FROM tipos_obrigacao ORDER BY ativo DESC,nome")->fetchAll();}

    public function fornecedor(int $id):?array{return $this->buscar('fornecedores',$id);}
    public function empenho(int $id):?array{return $this->buscar('empenhos_pagamento',$id);}
    public function tipoDocumento(int $id):?array{return $this->buscar('tipos_documento_pagamento',$id);}
    public function tipoObrigacao(int $id):?array{return $this->buscar('tipos_obrigacao',$id);}

    public function salvarFornecedor(array $d,?int $id=null):void{
        $nome=trim((string)($d['nome']??''));if($nome==='')throw new InvalidArgumentException('Informe o nome do fornecedor.');
        $doc=preg_replace('/[^0-9A-Za-z]/','',(string)($d['documento']??''));$ativo=isset($d['ativo'])?1:0;
        if($id){$st=$this->db->prepare("UPDATE fornecedores SET nome=?,documento=?,ativo=? WHERE id=?");$st->execute([$nome,$doc?:null,$ativo,$id]);}
        else{$st=$this->db->prepare("INSERT INTO fornecedores(nome,documento,ativo) VALUES(?,?,?)");$st->execute([$nome,$doc?:null,$ativo]);}
    }

    public function salvarEmpenho(array $d,?int $id=null):void{
        $numero=trim((string)($d['numero']??''));$ano=(int)($d['ano']??0);
        if($numero===''||$ano<2000||$ano>2100)throw new InvalidArgumentException('Informe número e ano válido do empenho.');
        $valores=[$numero,$ano,trim((string)($d['natureza']??''))?:null,(int)($d['exercicio']??0)?:null,trim((string)($d['origem_recurso']??''))?:null,trim((string)($d['fonte']??''))?:null,trim((string)($d['cmdf']??''))?:null,isset($d['ativo'])?1:0];
        if($id){$st=$this->db->prepare("UPDATE empenhos_pagamento SET numero=?,ano=?,natureza=?,exercicio=?,origem_recurso=?,fonte=?,cmdf=?,ativo=? WHERE id=?");$st->execute([...$valores,$id]);}
        else{$st=$this->db->prepare("INSERT INTO empenhos_pagamento(numero,ano,natureza,exercicio,origem_recurso,fonte,cmdf,ativo) VALUES(?,?,?,?,?,?,?,?)");$st->execute($valores);}
    }

    public function salvarTipoDocumento(array $d,?int $id=null):void{
        $nome=trim((string)($d['nome']??''));if($nome==='')throw new InvalidArgumentException('Informe o tipo de documento.');
        $valores=[$nome,isset($d['exige_numero'])?1:0,isset($d['ativo'])?1:0];
        if($id){$st=$this->db->prepare("UPDATE tipos_documento_pagamento SET nome=?,exige_numero=?,ativo=? WHERE id=?");$st->execute([...$valores,$id]);}
        else{$st=$this->db->prepare("INSERT INTO tipos_documento_pagamento(nome,exige_numero,ativo) VALUES(?,?,?)");$st->execute($valores);}
    }

    public function salvarTipoObrigacao(array $d,?int $id=null):void{
        $nome=trim((string)($d['nome']??''));if($nome==='')throw new InvalidArgumentException('Informe o tipo de obrigação.');
        $valores=[$nome,isset($d['exige_numero_ano'])?1:0,isset($d['ativo'])?1:0];
        if($id){$st=$this->db->prepare("UPDATE tipos_obrigacao SET nome=?,exige_numero_ano=?,ativo=? WHERE id=?");$st->execute([...$valores,$id]);}
        else{$st=$this->db->prepare("INSERT INTO tipos_obrigacao(nome,exige_numero_ano,ativo) VALUES(?,?,?)");$st->execute($valores);}
    }

    public function excluirFornecedor(int $id):void{$this->excluir('fornecedores',$id,'Fornecedor');}
    public function excluirEmpenho(int $id):void{$this->excluir('empenhos_pagamento',$id,'Empenho de pagamento');}
    public function excluirTipoDocumento(int $id):void{$this->excluir('tipos_documento_pagamento',$id,'Tipo de documento');}
    public function excluirTipoObrigacao(int $id):void{$this->excluir('tipos_obrigacao',$id,'Tipo de obrigação');}

    private function buscar(string $tabela,int $id):?array{
        if($id<=0)return null;$st=$this->db->prepare("SELECT * FROM {$tabela} WHERE id=?");$st->execute([$id]);$r=$st->fetch();return $r?:null;
    }
    private function excluir(string $tabela,int $id,string $rotulo):void{
        if($id<=0)throw new InvalidArgumentException($rotulo.' inválido.');
        try{$st=$this->db->prepare("DELETE FROM {$tabela} WHERE id=?");$st->execute([$id]);if($st->rowCount()===0)throw new RuntimeException($rotulo.' não encontrado.');}
        catch(PDOException $e){if((string)$e->getCode()==='23000')throw new RuntimeException($rotulo.' não pode ser excluído porque possui registros vinculados.');throw $e;}
    }
}
