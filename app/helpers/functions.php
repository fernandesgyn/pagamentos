<?php
declare(strict_types=1);
function e(mixed $value):string{return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function money(mixed $value):string{return 'R$ '.number_format((float)$value,2,',','.');}
function redirect(string $to):never{header('Location: '.$to);exit;}
