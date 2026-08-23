<?php
declare(strict_types=1);
final class App{
  public function __construct(private array $routes){}
  public function run(string $method,string $path):void{
    if($method==='POST'&&!Csrf::validate($_POST['_token']??null)){http_response_code(419);echo 'Sessão expirada ou requisição inválida.';return;}
    foreach($this->routes as $r){[$m,$pattern,$controller,$action]=$r;if($m!==$method)continue;$regex='#^'.preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#','(?P<$1>[^/]+)',$pattern).'$#';if(!preg_match($regex,$path,$matches))continue;$args=[];foreach($matches as $k=>$v)if(is_string($k))$args[]=$v;(new $controller())->$action(...$args);return;}http_response_code(404);echo 'Página não encontrada';
  }
}
