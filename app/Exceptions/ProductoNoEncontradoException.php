<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Representa la ausencia del producto solicitado.
 */
final class ProductoNoEncontradoException extends RuntimeException
{
}
