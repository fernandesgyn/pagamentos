<?php
declare(strict_types=1);
return [
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
];
