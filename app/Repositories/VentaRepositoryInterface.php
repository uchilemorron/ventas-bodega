<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Venta;
use DateTimeImmutable;

/**
 * Define las operaciones de persistencia para ventas.
 */
interface VentaRepositoryInterface
{
    /**
     * Guarda una venta y devuelve su identificador.
     */
    public function guardar(Venta $venta): int;

    /**
     * Devuelve las ventas del día indicado ordenadas cronológicamente.
     *
     * @return Venta[]
     */
    public function listarPorFecha(DateTimeImmutable $fecha): array;

    /**
     * Devuelve la suma de las ventas del día indicado.
     */
    public function totalPorFecha(DateTimeImmutable $fecha): float;
}
