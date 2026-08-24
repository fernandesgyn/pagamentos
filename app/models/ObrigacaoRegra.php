<?php
declare(strict_types=1);

final class ObrigacaoRegra
{
    public static function validarNumero(int $tipoId, string $numero): void
    {
        $st = Database::connection()->prepare("SELECT nome FROM tipos_obrigacao WHERE id=? AND ativo=1 LIMIT 1");
        $st->execute([$tipoId]);
        $tipo = (string)$st->fetchColumn();

        if ($tipo === '') {
            throw new InvalidArgumentException('Tipo de obrigação inválido ou inativo.');
        }

        if (mb_strtolower($tipo, 'UTF-8') === 'contrato' && !ctype_digit($numero)) {
            throw new InvalidArgumentException('O número do Contrato deve conter somente algarismos.');
        }
    }
}
