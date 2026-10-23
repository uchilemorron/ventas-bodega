<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Representa un intento de venta superior al stock disponible.
 */
final class StockInsuficienteException extends RuntimeException
{
}
