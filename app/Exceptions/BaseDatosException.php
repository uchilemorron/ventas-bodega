<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Representa un error de persistencia sin revelar detalles al usuario.
 */
final class BaseDatosException extends RuntimeException
{
    /**
     * Crea la excepción y registra internamente el detalle técnico.
     */
    public function __construct(string $mensaje, ?Throwable $anterior = null)
    {
        if ($anterior !== null) {
            error_log('Error de base de datos: ' . $anterior->getMessage());
        }

        parent::__construct($mensaje, 0, $anterior);
    }
}
