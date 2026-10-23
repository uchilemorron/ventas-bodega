<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CodigoDuplicadoException;
use App\Exceptions\ValidacionException;
use App\Models\Producto;
use App\Repositories\ProductoRepositoryInterface;

/**
 * Aplica las reglas de negocio para el catálogo de productos.
 */
final class ProductoService
{
    /**
     * Recibe el repositorio usado para persistir productos.
     */
    public function __construct(private ProductoRepositoryInterface $productos)
    {
    }

    /**
     * Valida y registra un producto nuevo.
     */
    public function registrarProducto(
        string $codigo,
        string $nombre,
        string $categoria,
        float $precioVenta,
        int $stockActual,
        int $stockMinimo
    ): Producto {
        $codigo = trim($codigo);
        $nombre = trim($nombre);
        $categoria = trim($categoria);

        if ($codigo === '' || $nombre === '' || $categoria === '') {
            throw new ValidacionException('Todos los campos son obligatorios.');
        }

        if (!is_finite($precioVenta) || $precioVenta < 0) {
            throw new ValidacionException('El precio no puede ser negativo.');
        }

        if ($stockActual < 0 || $stockMinimo < 0) {
            throw new ValidacionException('El stock debe ser un entero mayor o igual a cero.');
        }

        if ($this->productos->buscarPorCodigo($codigo) !== null) {
            throw new CodigoDuplicadoException('El código del producto ya está registrado.');
        }

        $producto = new Producto(
            null,
            $codigo,
            $nombre,
            $categoria,
            $precioVenta,
            $stockActual,
            $stockMinimo
        );
        $idProducto = $this->productos->guardar($producto);

        return new Producto(
            $idProducto,
            $codigo,
            $nombre,
            $categoria,
            $precioVenta,
            $stockActual,
            $stockMinimo
        );
    }

    /**
     * Devuelve todos los productos del catálogo.
     *
     * @return Producto[]
     */
    public function listarProductos(): array
    {
        return $this->productos->listarTodos();
    }

    /**
     * Devuelve los productos en su mínimo o por debajo.
     *
     * @return Producto[]
     */
    public function listarStockBajo(): array
    {
        return $this->productos->listarStockBajo();
    }
}
