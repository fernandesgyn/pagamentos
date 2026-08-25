<?php
$flash=$_SESSION['flash']??null;unset($_SESSION['flash']);
$path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/';
$user=Auth::user();
$active=static fn(string $prefix):string=>str_starts_with($path,$prefix)?'active':'';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="<?=e(Csrf::token())?>">
  <title><?=e($titulo??'Painel')?> | <?=e(APP_NAME)?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-rc4/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/admin-table.css">
  <style>.app-sidebar{--lte-sidebar-width:285px}.brand-text{font-size:1rem}.money{white-space:nowrap}.table td,.table th{vertical-align:middle}.card-title{font-weight:600}.summary-value{font-size:1.65rem;font-weight:700}.portal-page-actions{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1rem}</style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary"><div class="app-wrapper">
<nav class="app-header navbar navbar-expand bg-body border-bottom"><div class="container-fluid"><ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-lte-toggle="sidebar" href="#"><i class="fa-solid fa-bars"></i></a></li><li class="nav-item d-none d-md-block"><a class="nav-link" href="/">Painel</a></li></ul><ul class="navbar-nav ms-auto align-items-center"><li class="nav-item d-none d-md-block"><span class="nav-link text-body-secondary"><?=e($user['nome']??'')?> · <?=e($user['perfil']??'')?></span></li><li class="nav-item"><form method="post" action="/logout" class="m-0"><?=Csrf::field()?><button class="nav-link border-0 bg-transparent" type="submit"><i class="fa-solid fa-right-from-bracket me-1"></i>Sair</button></form></li></ul></div></nav>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="light"><div class="sidebar-brand"><a href="/" class="brand-link"><i class="brand-image fa-solid fa-file-invoice-dollar"></i><span class="brand-text fw-light">Liquidação e Pagamentos</span></a></div><div class="sidebar-wrapper"><nav class="mt-2"><ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
<li class="nav-item"><a href="/" class="nav-link <?=$path==='/'?'active':''?>"><i class="nav-icon fa-solid fa-gauge"></i><p>Painel</p></a></li>
<li class="nav-header">FLUXO</li>
<?php if(Auth::can('obrigacao.gerir')):?><li class="nav-item"><a href="/obrigacoes" class="nav-link <?=$active('/obrigacoes')?>"><i class="nav-icon fa-solid fa-file-signature"></i><p>Obrigações</p></a></li><?php endif;?>
<?php if(Auth::can('documento.gerir')):?><li class="nav-item"><a href="/documentos" class="nav-link <?=$active('/documentos')?>"><i class="nav-icon fa-solid fa-receipt"></i><p>Documentos</p></a></li><?php endif;?>
<li class="nav-header">ETAPAS</li>
<?php if(Auth::can('inspecao.gerir')):?><li class="nav-item"><a href="/inspecoes" class="nav-link <?=$active('/inspecoes')?>"><i class="nav-icon fa-solid fa-magnifying-glass"></i><p>Inspeção</p></a></li><?php endif;?>
<?php if(Auth::can('parcela.gerir')):?><li class="nav-item"><a href="/programacao" class="nav-link <?=$active('/programacao')?>"><i class="nav-icon fa-solid fa-list-check"></i><p>Programação</p></a></li><?php endif;?>
<?php if(Auth::can('liquidacao.gerir')):?><li class="nav-item"><a href="/liquidacoes" class="nav-link <?=$active('/liquidacoes')?>"><i class="nav-icon fa-solid fa-check-double"></i><p>Liquidação</p></a></li><?php endif;?>
<?php if(Auth::can('cmdf.gerir')):?><li class="nav-item"><a href="/cmdf" class="nav-link <?=$active('/cmdf')?>"><i class="nav-icon fa-solid fa-layer-group"></i><p>CMDF</p></a></li><?php endif;?>
<?php if(Auth::can('pagamento.gerir')):?><li class="nav-item"><a href="/pagamentos" class="nav-link <?=$active('/pagamentos')?>"><i class="nav-icon fa-solid fa-money-check-dollar"></i><p>Pagamento</p></a></li><?php endif;?>
<?php if(Auth::can('cadastro.gerir')):?><li class="nav-header">CADASTROS</li>
<li class="nav-item"><a href="/fornecedores" class="nav-link <?=$active('/fornecedores')?>"><i class="nav-icon fa-solid fa-building-user"></i><p>Fornecedores</p></a></li>
<li class="nav-item"><a href="/fontes-recurso" class="nav-link <?=$active('/fontes-recurso')?>"><i class="nav-icon fa-solid fa-coins"></i><p>Fontes de recurso</p></a></li>
<li class="nav-item"><a href="/naturezas-despesa" class="nav-link <?=$active('/naturezas-despesa')?>"><i class="nav-icon fa-solid fa-sitemap"></i><p>Naturezas da despesa</p></a></li>
<li class="nav-item"><a href="/origens-recurso" class="nav-link <?=$active('/origens-recurso')?>"><i class="nav-icon fa-solid fa-layer-group"></i><p>Origens do Recurso</p></a></li>
<li class="nav-item"><a href="/tipos-documento" class="nav-link <?=$active('/tipos-documento')?>"><i class="nav-icon fa-solid fa-file-lines"></i><p>Tipos de documento</p></a></li>
<li class="nav-item"><a href="/tipos-obrigacao" class="nav-link <?=$active('/tipos-obrigacao')?>"><i class="nav-icon fa-solid fa-tags"></i><p>Tipos de obrigação</p></a></li><?php endif;?>
<?php if(Auth::can('usuario.gerir')||Auth::can('perfil.gerir')):?><li class="nav-header">ADMINISTRAÇÃO</li><?php endif;?>
<?php if(Auth::can('usuario.gerir')):?><li class="nav-item"><a href="/usuarios" class="nav-link <?=$active('/usuarios')?>"><i class="nav-icon fa-solid fa-users-gear"></i><p>Usuários</p></a></li><?php endif;?>
<?php if(Auth::can('perfil.gerir')):?><li class="nav-item"><a href="/perfis" class="nav-link <?=$active('/perfis')?>"><i class="nav-icon fa-solid fa-user-shield"></i><p>Perfis e permissões</p></a></li><?php endif;?>
</ul></nav></div></aside>
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><h1 class="mb-0 fs-3"><?=e($titulo??'Painel')?></h1></div></div><div class="app-content"><div class="container-fluid"><?php if($flash):?><div class="alert alert-<?=e($flash[0])?> alert-dismissible fade show"><?=e($flash[1])?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif;?><?=$conteudo?></div></div></main>
<footer class="app-footer"><strong><?=e(APP_NAME)?></strong><span class="float-end d-none d-sm-inline">PHP 8.2 · MySQL 8 · AdminLTE</span></footer>
</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-rc4/dist/js/adminlte.min.js"></script><script src="/assets/js/admin-table.js"></script><script src="/assets/js/forms.js"></script><script>document.querySelectorAll('form[method="post"],form[method="POST"]').forEach(function(f){if(f.querySelector('input[name="_token"]'))return;var i=document.createElement('input');i.type='hidden';i.name='_token';i.value=document.querySelector('meta[name="csrf-token"]').content;f.appendChild(i);});</script></body></html>
