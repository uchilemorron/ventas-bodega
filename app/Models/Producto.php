<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Representa un producto disponible para la venta.
 */
final class Producto
{
    /**
     * Crea una instancia de producto.
     *
     * @param int|null $idProducto Identificador asignado por la base de datos.
     * @param string $codigo Código único del producto.
     * @param string $nombre Nombre descriptivo.
     * @param string $categoria Categoría del producto.
     * @param float $precioVenta Precio vigente.
     * @param int $stockActual Unidades disponibles.
     * @param int $stockMinimo Nivel mínimo de referencia.
     */
    public function __construct(
        private ?int $idProducto,
        private string $codigo,
        private string $nombre,
        private string $categoria,
        private float $precioVenta,
        private int $stockActual,
        private int $stockMinimo
    ) {
    }

    /**
     * Devuelve el identificador del producto.
     */
    public function getIdProducto(): ?int
    {
        return $this->idProducto;
    }

    /**
     * Devuelve el código único del producto.
     */
    public function getCodigo(): string
    {
        return $this->codigo;
    }

    /**
     * Devuelve el nombre del producto.
     */
    public function getNombre(): string
    {
        return $this->nombre;
    }

    /**
     * Devuelve la categoría del producto.
     */
    public function getCategoria(): string
    {
        return $this->categoria;
    }

    /**
     * Devuelve el precio de venta vigente.
     */
    public function getPrecioVenta(): float
    {
        return $this->precioVenta;
    }

    /**
     * Devuelve las unidades disponibles.
     */
    public function getStockActual(): int
    {
        return $this->stockActual;
    }

    /**
     * Devuelve el nivel mínimo de stock.
     */
    public function getStockMinimo(): int
    {
        return $this->stockMinimo;
    }

    /**
     * Indica si el producto alcanzó o está por debajo del mínimo.
     */
    public function estaEnStockBajo(): bool
    {
        return $this->stockActual <= $this->stockMinimo;
    }
}
