<?php
declare(strict_types=1);
final class Cadastro{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function fornecedores():array{return $this->db->query("SELECT * FROM fornecedores ORDER BY ativo DESC,nome")->fetchAll();}
    public function empenhos():array{return $this->db->query("SELECT * FROM empenhos_pagamento ORDER BY ano DESC,numero")->fetchAll();}
    public function tiposDocumento():array{return $this->db->query("SELECT * FROM tipos_documento_pagamento ORDER BY ativo DESC,nome")->fetchAll();}
    public function tiposObrigacao():array{return $this->db->query("SELECT * FROM tipos_obrigacao ORDER BY ativo DESC,nome")->fetchAll();}

    public function criarFornecedor(array $d):void{
        $nome=trim((string)($d['nome']??''));if($nome==='')throw new InvalidArgumentException('Informe o nome do fornecedor.');
        $doc=preg_replace('/[^0-9A-Za-z]/','',(string)($d['documento']??''));
        $st=$this->db->prepare("INSERT INTO fornecedores(nome,documento) VALUES(?,?)");$st->execute([$nome,$doc?:null]);
    }
    public function criarEmpenho(array $d):void{
        $numero=trim((string)($d['numero']??''));$ano=(int)($d['ano']??0);
        if($numero===''||!ctype_digit($numero)||$ano<2000||$ano>2100)throw new InvalidArgumentException('Informe número numérico e ano válido do empenho.');
        $st=$this->db->prepare("INSERT INTO empenhos_pagamento(numero,ano,natureza,exercicio,origem_recurso,fonte,cmdf) VALUES(?,?,?,?,?,?,?)");
        $st->execute([$numero,$ano,trim((string)($d['natureza']??''))?:null,(int)($d['exercicio']??0)?:null,trim((string)($d['origem_recurso']??''))?:null,trim((string)($d['fonte']??''))?:null,trim((string)($d['cmdf']??''))?:null]);
    }
    public function criarTipoDocumento(array $d):void{
        $nome=trim((string)($d['nome']??''));if($nome==='')throw new InvalidArgumentException('Informe o tipo de documento.');
        $this->db->prepare("INSERT INTO tipos_documento_pagamento(nome,exige_numero) VALUES(?,?)")->execute([$nome,isset($d['exige_numero'])?1:0]);
    }
    public function criarTipoObrigacao(array $d):void{
        $nome=trim((string)($d['nome']??''));if($nome==='')throw new InvalidArgumentException('Informe o tipo de obrigação.');
        $this->db->prepare("INSERT INTO tipos_obrigacao(nome,exige_numero_ano) VALUES(?,?)")->execute([$nome,isset($d['exige_numero_ano'])?1:0]);
    }
}
