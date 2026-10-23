<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Producto;

/**
 * Define las operaciones de persistencia para productos.
 */
interface ProductoRepositoryInterface
{
    /**
     * Guarda un producto y devuelve su identificador.
     */
    public function guardar(Producto $producto): int;

    /**
     * Busca un producto por su identificador.
     */
    public function buscarPorId(int $idProducto): ?Producto;

    /**
     * Busca un producto por su código único.
     */
    public function buscarPorCodigo(string $codigo): ?Producto;

    /**
     * Devuelve todos los productos registrados.
     *
     * @return Producto[]
     */
    public function listarTodos(): array;

    /**
     * Devuelve los productos en su mínimo o por debajo.
     *
     * @return Producto[]
     */
    public function listarStockBajo(): array;

    /**
     * Resta la cantidad indicada al stock persistido.
     */
    public function descontarStock(int $idProducto, int $cantidad): void;

    /**
     * Bloquea el producto hasta finalizar la transacción actual.
     */
    public function bloquearParaVenta(int $idProducto): ?Producto;
}
