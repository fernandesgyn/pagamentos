<?php
declare(strict_types=1);
return [
 ['GET','/login','AuthController','loginForm'],
 ['POST','/login','AuthController','login'],
 ['POST','/logout','AuthController','logout'],

 ['GET','/','HomeController','index'],

 ['GET','/obrigacoes','HomeController','obrigacoes'],
 ['GET','/obrigacoes/nova','HomeController','novaObrigacao'],
 ['POST','/obrigacoes','HomeController','salvarObrigacao'],

 ['GET','/documentos','HomeController','documentos'],
 ['GET','/documentos/novo','HomeController','novoDocumento'],
 ['POST','/documentos','HomeController','salvarDocumento'],
 ['GET','/documentos/{id}','HomeController','documento'],
 ['POST','/documentos/{id}/inspecao','HomeController','inspecao'],
 ['POST','/documentos/{id}/parcelas','HomeController','parcela'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/componentes','HomeController','componente'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/liquidar','HomeController','liquidar'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/cmdf','CmdfController','concluir'],

 ['GET','/inspecoes','FilaController','inspecoes'],
 ['GET','/programacao','FilaController','programacao'],
 ['GET','/liquidacoes','FilaController','liquidacoes'],
 ['GET','/cmdf','FilaController','cmdf'],

 ['GET','/pagamentos','PagamentoController','index'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/pagar','PagamentoController','pagar'],

 ['GET','/fornecedores','CadastroController','fornecedores'],
 ['GET','/fornecedores/novo','CadastroController','novoFornecedor'],
 ['POST','/fornecedores','CadastroController','salvarFornecedor'],
 ['GET','/fornecedores/{id}/editar','CadastroController','editarFornecedor'],
 ['POST','/fornecedores/{id}','CadastroController','atualizarFornecedor'],
 ['POST','/fornecedores/{id}/excluir','CadastroController','excluirFornecedor'],

 ['GET','/empenhos-pagamento','CadastroController','empenhos'],
 ['GET','/empenhos-pagamento/novo','CadastroController','novoEmpenho'],
 ['POST','/empenhos-pagamento','CadastroController','salvarEmpenho'],
 ['GET','/empenhos-pagamento/{id}/editar','CadastroController','editarEmpenho'],
 ['POST','/empenhos-pagamento/{id}','CadastroController','atualizarEmpenho'],
 ['POST','/empenhos-pagamento/{id}/excluir','CadastroController','excluirEmpenho'],

 ['GET','/tipos-documento','CadastroController','tiposDocumento'],
 ['GET','/tipos-documento/novo','CadastroController','novoTipoDocumento'],
 ['POST','/tipos-documento','CadastroController','salvarTipoDocumento'],
 ['GET','/tipos-documento/{id}/editar','CadastroController','editarTipoDocumento'],
 ['POST','/tipos-documento/{id}','CadastroController','atualizarTipoDocumento'],
 ['POST','/tipos-documento/{id}/excluir','CadastroController','excluirTipoDocumento'],

 ['GET','/tipos-obrigacao','CadastroController','tiposObrigacao'],
 ['GET','/tipos-obrigacao/novo','CadastroController','novoTipoObrigacao'],
 ['POST','/tipos-obrigacao','CadastroController','salvarTipoObrigacao'],
 ['GET','/tipos-obrigacao/{id}/editar','CadastroController','editarTipoObrigacao'],
 ['POST','/tipos-obrigacao/{id}','CadastroController','atualizarTipoObrigacao'],
 ['POST','/tipos-obrigacao/{id}/excluir','CadastroController','excluirTipoObrigacao'],

 ['GET','/usuarios','UsuarioController','index'],
 ['GET','/usuarios/novo','UsuarioController','novo'],
 ['POST','/usuarios','UsuarioController','salvar'],
 ['GET','/usuarios/{id}/editar','UsuarioController','editar'],
 ['POST','/usuarios/{id}','UsuarioController','atualizar'],
 ['POST','/usuarios/{id}/excluir','UsuarioController','excluir'],

 ['GET','/perfis','PerfilController','index'],
 ['GET','/perfis/novo','PerfilController','novo'],
 ['POST','/perfis','PerfilController','salvar'],
 ['GET','/perfis/{id}/editar','PerfilController','editar'],
 ['POST','/perfis/{id}','PerfilController','atualizar'],
 ['POST','/perfis/{id}/excluir','PerfilController','excluir'],
];
