<?php
declare(strict_types=1);
$root=dirname(__DIR__);
$envPath=$root.'/.env';
if(is_file($envPath)){
    $vars=parse_ini_file($envPath,false,INI_SCANNER_RAW)?:[];
    foreach($vars as $key=>$value){
        if(getenv((string)$key)===false){putenv((string)$key.'='.(string)$value);}
    }
}
require_once $root.'/config/config.php';
foreach(['app/core','app/models','app/helpers','app/controllers'] as $dir){
    foreach(glob($root.'/'.$dir.'/*.php')?:[] as $file){require_once $file;}
}
