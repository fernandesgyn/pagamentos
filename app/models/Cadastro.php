<?php
declare(strict_types=1);

final class Cadastro
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function fornecedores():array{
        return $this->db->query("SELECT * FROM fornecedores ORDER BY ativo DESC,razao_social")->fetchAll();
    }
    public function fontesRecurso():array{
        return $this->db->query("SELECT * FROM fontes_recurso ORDER BY ativo DESC,codigo,nome")->fetchAll();
    }
    public function naturezasDespesa():array{
        return $this->db->query("SELECT * FROM naturezas_despesa ORDER BY ativo DESC,codigo,nome")->fetchAll();
    }
    public function tiposRecurso():array{
        return $this->db->query("SELECT * FROM tipos_recurso ORDER BY ativo DESC,codigo,nome")->fetchAll();
    }
    public function tiposDocumento():array{
        return $this->db->query("SELECT * FROM tipos_documento_pagamento ORDER BY ativo DESC,nome")->fetchAll();
    }
    public function tiposObrigacao():array{
        return $this->db->query("SELECT * FROM tipos_obrigacao ORDER BY ativo DESC,nome")->fetchAll();
    }

    public function fornecedor(int $id):?array{return $this->buscar('fornecedores',$id);}
    public function fonteRecurso(int $id):?array{return $this->buscar('fontes_recurso',$id);}
    public function naturezaDespesa(int $id):?array{return $this->buscar('naturezas_despesa',$id);}
    public function tipoRecurso(int $id):?array{return $this->buscar('tipos_recurso',$id);}
    public function tipoDocumento(int $id):?array{return $this->buscar('tipos_documento_pagamento',$id);}
    public function tipoObrigacao(int $id):?array{return $this->buscar('tipos_obrigacao',$id);}

    public function salvarFornecedor(array $d,?int $id=null):void{
        $razao=trim((string)($d['razao_social']??''));
        $documento=preg_replace('/\D/','',(string)($d['documento']??''));
        $tipo=(string)($d['tipo_pessoa']??'');
        if($razao==='')throw new InvalidArgumentException('Informe a Razão Social/Nome.');
        if(!in_array($tipo,['PF','PJ'],true))throw new InvalidArgumentException('Selecione Pessoa Física ou Pessoa Jurídica.');
        $esperado=$tipo==='PF'?11:14;
        if(strlen($documento)!==$esperado)throw new InvalidArgumentException($tipo==='PF'?'CPF deve possuir 11 dígitos.':'CNPJ deve possuir 14 dígitos.');
        $ativo=isset($d['ativo'])?1:0;
        try{
            if($id){
                $st=$this->db->prepare("UPDATE fornecedores SET razao_social=?,documento=?,tipo_pessoa=?,ativo=? WHERE id=?");
                $st->execute([$razao,$documento,$tipo,$ativo,$id]);
            }else{
                $st=$this->db->prepare("INSERT INTO fornecedores(razao_social,documento,tipo_pessoa,ativo) VALUES(?,?,?,?)");
                $st->execute([$razao,$documento,$tipo,$ativo]);
            }
        }catch(PDOException $e){
            if((string)$e->getCode()==='23000')throw new RuntimeException('Já existe fornecedor com este CPF/CNPJ.');
            throw $e;
        }
    }

    public function salvarFonteRecurso(array $d,?int $id=null):void{
        $this->salvarCodigoNome('fontes_recurso',$d,$id,'Fonte de recurso');
    }
    public function salvarNaturezaDespesa(array $d,?int $id=null):void{
        $this->salvarCodigoNome('naturezas_despesa',$d,$id,'Natureza da despesa');
    }
    public function salvarTipoRecurso(array $d,?int $id=null):void{
        $this->salvarCodigoNome('tipos_recurso',$d,$id,'Tipo de recurso');
    }

    public function salvarTipoDocumento(array $d,?int $id=null):void{
        $nome=trim((string)($d['nome']??''));
        if($nome==='')throw new InvalidArgumentException('Informe o tipo de documento.');
        $valores=[$nome,isset($d['exige_numero'])?1:0,isset($d['ativo'])?1:0];
        if($id){
            $st=$this->db->prepare("UPDATE tipos_documento_pagamento SET nome=?,exige_numero=?,ativo=? WHERE id=?");
            $st->execute([...$valores,$id]);
        }else{
            $st=$this->db->prepare("INSERT INTO tipos_documento_pagamento(nome,exige_numero,ativo) VALUES(?,?,?)");
            $st->execute($valores);
        }
    }

    public function salvarTipoObrigacao(array $d,?int $id=null):void{
        $nome=trim((string)($d['nome']??''));
        if($nome==='')throw new InvalidArgumentException('Informe o tipo de obrigação.');
        $valores=[$nome,isset($d['exige_numero_ano'])?1:0,isset($d['ativo'])?1:0];
        if($id){
            $st=$this->db->prepare("UPDATE tipos_obrigacao SET nome=?,exige_numero_ano=?,ativo=? WHERE id=?");
            $st->execute([...$valores,$id]);
        }else{
            $st=$this->db->prepare("INSERT INTO tipos_obrigacao(nome,exige_numero_ano,ativo) VALUES(?,?,?)");
            $st->execute($valores);
        }
    }

    public function excluirFornecedor(int $id):void{$this->excluir('fornecedores',$id,'Fornecedor');}
    public function excluirFonteRecurso(int $id):void{$this->excluir('fontes_recurso',$id,'Fonte de recurso');}
    public function excluirNaturezaDespesa(int $id):void{$this->excluir('naturezas_despesa',$id,'Natureza da despesa');}
    public function excluirTipoRecurso(int $id):void{$this->excluir('tipos_recurso',$id,'Tipo de recurso');}
    public function excluirTipoDocumento(int $id):void{$this->excluir('tipos_documento_pagamento',$id,'Tipo de documento');}
    public function excluirTipoObrigacao(int $id):void{$this->excluir('tipos_obrigacao',$id,'Tipo de obrigação');}

    private function salvarCodigoNome(string $tabela,array $d,?int $id,string $rotulo):void{
        $codigo=trim((string)($d['codigo']??''));
        $nome=trim((string)($d['nome']??''));
        if($codigo===''||$nome==='')throw new InvalidArgumentException("Informe código e nome de {$rotulo}.");
        $ativo=isset($d['ativo'])?1:0;
        try{
            if($id){
                $st=$this->db->prepare("UPDATE {$tabela} SET codigo=?,nome=?,ativo=? WHERE id=?");
                $st->execute([$codigo,$nome,$ativo,$id]);
            }else{
                $st=$this->db->prepare("INSERT INTO {$tabela}(codigo,nome,ativo) VALUES(?,?,?)");
                $st->execute([$codigo,$nome,$ativo]);
            }
        }catch(PDOException $e){
            if((string)$e->getCode()==='23000')throw new RuntimeException("Já existe {$rotulo} com este código.");
            throw $e;
        }
    }

    private function buscar(string $tabela,int $id):?array{
        if($id<=0)return null;
        $permitidas=['fornecedores','fontes_recurso','naturezas_despesa','tipos_recurso','tipos_documento_pagamento','tipos_obrigacao'];
        if(!in_array($tabela,$permitidas,true))throw new LogicException('Cadastro inválido.');
        $st=$this->db->prepare("SELECT * FROM {$tabela} WHERE id=?");$st->execute([$id]);
        $r=$st->fetch();return $r?:null;
    }

    private function excluir(string $tabela,int $id,string $rotulo):void{
        if($id<=0)throw new InvalidArgumentException($rotulo.' inválido.');
        $permitidas=['fornecedores','fontes_recurso','naturezas_despesa','tipos_recurso','tipos_documento_pagamento','tipos_obrigacao'];
        if(!in_array($tabela,$permitidas,true))throw new LogicException('Cadastro inválido.');
        try{
            $st=$this->db->prepare("DELETE FROM {$tabela} WHERE id=?");$st->execute([$id]);
            if($st->rowCount()===0)throw new RuntimeException($rotulo.' não encontrado.');
        }catch(PDOException $e){
            if((string)$e->getCode()==='23000')throw new RuntimeException($rotulo.' não pode ser excluído porque possui registros vinculados.');
            throw $e;
        }
    }
}
