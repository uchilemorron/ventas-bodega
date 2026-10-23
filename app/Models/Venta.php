<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

/**
 * Representa una venta individual de un producto.
 */
final class Venta
{
    /**
     * Crea una instancia de venta.
     *
     * @param int|null $idVenta Identificador asignado por la base de datos.
     * @param int $idProducto Identificador del producto vendido.
     * @param int $cantidad Cantidad vendida.
     * @param float $precioUnitario Precio vigente al vender.
     * @param float $total Importe total de la venta.
     * @param DateTimeImmutable $fechaHora Fecha y hora de registro.
     * @param string|null $nombreProducto Nombre incluido al consultar ventas.
     */
    public function __construct(
        private ?int $idVenta,
        private int $idProducto,
        private int $cantidad,
        private float $precioUnitario,
        private float $total,
        private DateTimeImmutable $fechaHora,
        private ?string $nombreProducto = null
    ) {
    }

    /**
     * Devuelve el identificador de la venta.
     */
    public function getIdVenta(): ?int
    {
        return $this->idVenta;
    }

    /**
     * Devuelve el identificador del producto vendido.
     */
    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    /**
     * Devuelve la cantidad vendida.
     */
    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    /**
     * Devuelve el precio unitario aplicado.
     */
    public function getPrecioUnitario(): float
    {
        return $this->precioUnitario;
    }

    /**
     * Devuelve el importe total de la venta.
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * Devuelve la fecha y hora de la venta.
     */
    public function getFechaHora(): DateTimeImmutable
    {
        return $this->fechaHora;
    }

    /**
     * Devuelve el nombre del producto asociado, si fue consultado.
     */
    public function getNombreProducto(): ?string
    {
        return $this->nombreProducto;
    }
}
