<?php
declare(strict_types=1);
final class Csrf{
    public static function token():string{
        if(empty($_SESSION['_csrf']))$_SESSION['_csrf']=bin2hex(random_bytes(32));
        return (string)$_SESSION['_csrf'];
    }
    public static function field():string{return '<input type="hidden" name="_token" value="'.htmlspecialchars(self::token(),ENT_QUOTES,'UTF-8').'">';}
    public static function validate(?string $token):bool{return is_string($token)&&$token!==''&&hash_equals(self::token(),$token);}
}
