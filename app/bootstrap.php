<?php
declare(strict_types=1);
$root=dirname(__DIR__);
foreach(['app/core','app/controllers','app/models','app/helpers'] as $dir){foreach(glob($root.'/'.$dir.'/*.php')?:[] as $file)require_once $file;}
require_once $root.'/config/config.php';
