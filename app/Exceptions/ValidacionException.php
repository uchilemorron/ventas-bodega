<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Representa datos que no cumplen las reglas de validación del sistema.
 */
final class ValidacionException extends RuntimeException
{
}
