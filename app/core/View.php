<?php
declare(strict_types=1);
final class View{
  public static function render(string $view,array $data=[]):void{
    extract($data,EXTR_SKIP);ob_start();require BASE_PATH.'/app/views/'.$view.'.php';$conteudo=ob_get_clean();require BASE_PATH.'/app/views/layouts/main.php';
  }
}
