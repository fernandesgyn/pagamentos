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

 ['GET','/inspecoes','FilaController','inspecoes'],
 ['GET','/inspecoes/{documentoId}','FilaController','inspecao'],
 ['POST','/inspecoes/{documentoId}','FilaController','salvarInspecao'],

 ['GET','/programacao','FilaController','programacao'],
 ['GET','/programacao/{documentoId}','FilaController','programar'],
 ['POST','/programacao/{documentoId}/parcelas','FilaController','adicionarParcela'],

 ['GET','/liquidacoes','FilaController','liquidacoes'],
 ['GET','/liquidacoes/{parcelaId}','FilaController','liquidacao'],
 ['POST','/liquidacoes/{parcelaId}','FilaController','salvarLiquidacao'],

 ['GET','/cmdf','FilaController','cmdf'],
 ['GET','/cmdf/{parcelaId}','FilaController','cmdfParcela'],
 ['POST','/cmdf/{parcelaId}','FilaController','salvarCmdf'],

 ['GET','/pagamentos','PagamentoController','index'],
 ['POST','/documentos/{documentoId}/parcelas/{parcelaId}/pagar','PagamentoController','pagar'],

 ['GET','/fornecedores','CadastroController','fornecedores'],
 ['GET','/fornecedores/novo','CadastroController','novoFornecedor'],
 ['POST','/fornecedores','CadastroController','salvarFornecedor'],
 ['GET','/fornecedores/{id}/editar','CadastroController','editarFornecedor'],
 ['POST','/fornecedores/{id}','CadastroController','atualizarFornecedor'],
 ['POST','/fornecedores/{id}/excluir','CadastroController','excluirFornecedor'],

 ['GET','/fontes-recurso','CadastroController','fontes'],
 ['GET','/fontes-recurso/nova','CadastroController','novaFonte'],
 ['POST','/fontes-recurso','CadastroController','salvarFonte'],
 ['GET','/fontes-recurso/{id}/editar','CadastroController','editarFonte'],
 ['POST','/fontes-recurso/{id}','CadastroController','atualizarFonte'],
 ['POST','/fontes-recurso/{id}/excluir','CadastroController','excluirFonte'],

 ['GET','/naturezas-despesa','CadastroController','naturezas'],
 ['GET','/naturezas-despesa/nova','CadastroController','novaNatureza'],
 ['POST','/naturezas-despesa','CadastroController','salvarNatureza'],
 ['GET','/naturezas-despesa/{id}/editar','CadastroController','editarNatureza'],
 ['POST','/naturezas-despesa/{id}','CadastroController','atualizarNatureza'],
 ['POST','/naturezas-despesa/{id}/excluir','CadastroController','excluirNatureza'],

 ['GET','/tipos-recurso','CadastroController','tiposRecurso'],
 ['GET','/tipos-recurso/novo','CadastroController','novoTipoRecurso'],
 ['POST','/tipos-recurso','CadastroController','salvarTipoRecurso'],
 ['GET','/tipos-recurso/{id}/editar','CadastroController','editarTipoRecurso'],
 ['POST','/tipos-recurso/{id}','CadastroController','atualizarTipoRecurso'],
 ['POST','/tipos-recurso/{id}/excluir','CadastroController','excluirTipoRecurso'],

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
