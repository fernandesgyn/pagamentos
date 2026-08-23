<?php
declare(strict_types=1);
return [
 ['GET','/login','AuthController','loginForm'],
 ['POST','/login','AuthController','login'],
 ['POST','/logout','AuthController','logout'],

 ['GET','/','HomeController','index'],
 ['GET','/obrigacoes','HomeController','obrigacoes'],
 ['POST','/obrigacoes','HomeController','salvarObrigacao'],
 ['GET','/documentos','HomeController','documentos'],
 ['POST','/documentos','HomeController','salvarDocumento'],
 ['GET','/documentos/{id}','HomeController','documento'],
 ['POST','/documentos/{id}/inspecao','HomeController','inspecao'],
 ['POST','/documentos/{id}/parcelas','HomeController','parcela'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/componentes','HomeController','componente'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/liquidar','HomeController','liquidar'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/cmdf','HomeController','cmdf'],

 ['GET','/pagamentos','PagamentoController','index'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/pagar','PagamentoController','pagar'],

 ['GET','/cadastros','CadastroController','index'],
 ['POST','/cadastros/fornecedores','CadastroController','fornecedor'],
 ['POST','/cadastros/empenhos','CadastroController','empenho'],
 ['POST','/cadastros/tipos-documento','CadastroController','tipoDocumento'],
 ['POST','/cadastros/tipos-obrigacao','CadastroController','tipoObrigacao'],

 ['GET','/usuarios','UsuarioController','index'],
 ['POST','/usuarios','UsuarioController','salvar'],
];
