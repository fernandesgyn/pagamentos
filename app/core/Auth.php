<?php
declare(strict_types=1);

final class Auth
{
    private static bool $sessionValidated = false;
    private static ?array $validatedUser = null;

    public static function user(): ?array
    {
        return self::validatedSessionUser();
    }

    public static function id(): ?int
    {
        $user = self::user();
        $id = (int)($user['id'] ?? 0);
        return $id > 0 ? $id : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(string $login, string $senha): bool
    {
        $st = Database::connection()->prepare(
            "SELECT u.id,u.nome,u.login,u.senha_hash,u.ativo,p.id perfil_id,p.nome perfil
             FROM usuarios u
             JOIN perfis p ON p.id=u.perfil_id
             WHERE u.login=? AND p.ativo=1
             LIMIT 1"
        );
        $st->execute([$login]);
        $u = $st->fetch();

        if (!$u || (int)$u['ativo'] !== 1 || !password_verify($senha, $u['senha_hash'])) {
            return false;
        }

        unset($u['senha_hash']);
        $_SESSION['user'] = $u;
        self::$sessionValidated = true;
        self::$validatedUser = $u;
        session_regenerate_id(true);
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        self::$sessionValidated = true;
        self::$validatedUser = null;
        session_regenerate_id(true);
    }

    public static function can(string $chave): bool
    {
        $u = self::user();
        if (!$u) return false;
        if (($u['perfil'] ?? '') === 'Administrador') return true;

        $st = Database::connection()->prepare(
            "SELECT 1
             FROM perfil_permissoes pp
             JOIN permissoes p ON p.id=pp.permissao_id
             WHERE pp.perfil_id=? AND p.chave=?
             LIMIT 1"
        );
        $st->execute([(int)$u['perfil_id'], $chave]);
        return (bool)$st->fetchColumn();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) redirect('/login');
    }

    public static function requirePermission(string $chave): void
    {
        self::requireLogin();
        if (!self::can($chave)) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }

    private static function validatedSessionUser(): ?array
    {
        if (self::$sessionValidated) {
            return self::$validatedUser;
        }

        self::$sessionValidated = true;
        $sessionId = (int)($_SESSION['user']['id'] ?? 0);
        if ($sessionId <= 0) {
            self::$validatedUser = null;
            return null;
        }

        $st = Database::connection()->prepare(
            "SELECT u.id,u.nome,u.login,u.ativo,p.id perfil_id,p.nome perfil
             FROM usuarios u
             JOIN perfis p ON p.id=u.perfil_id
             WHERE u.id=? AND u.ativo=1 AND p.ativo=1
             LIMIT 1"
        );
        $st->execute([$sessionId]);
        $u = $st->fetch();

        if (!$u) {
            unset($_SESSION['user']);
            self::$validatedUser = null;
            return null;
        }

        $_SESSION['user'] = $u;
        self::$validatedUser = $u;
        return $u;
    }
}
