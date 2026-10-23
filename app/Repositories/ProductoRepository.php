<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\BaseDatosException;
use App\Exceptions\CodigoDuplicadoException;
use App\Models\Producto;
use PDO;
use PDOException;

/**
 * Ejecuta consultas PDO relacionadas con productos.
 */
final class ProductoRepository implements ProductoRepositoryInterface
{
    /**
     * Recibe la conexión PDO de la aplicación.
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Guarda un producto y devuelve su identificador.
     */
    public function guardar(Producto $producto): int
    {
        $sql = 'INSERT INTO producto '
            . '(codigo, nombre, categoria, precio_venta, stock_actual, stock_minimo) '
            . 'VALUES (:codigo, :nombre, :categoria, :precio, :stock, :minimo)';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute([
                'codigo' => $producto->getCodigo(),
                'nombre' => $producto->getNombre(),
                'categoria' => $producto->getCategoria(),
                'precio' => $producto->getPrecioVenta(),
                'stock' => $producto->getStockActual(),
                'minimo' => $producto->getStockMinimo(),
            ]);

            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $excepcion) {
            if (
                $excepcion->getCode() === '23000'
                && (int) ($excepcion->errorInfo[1] ?? 0) === 1062
            ) {
                throw new CodigoDuplicadoException('El código del producto ya está registrado.');
            }

            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Busca un producto por su identificador.
     */
    public function buscarPorId(int $idProducto): ?Producto
    {
        $sql = 'SELECT id_producto, codigo, nombre, categoria, precio_venta, '
            . 'stock_actual, stock_minimo FROM producto WHERE id_producto = :id';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute(['id' => $idProducto]);
            $fila = $sentencia->fetch();

            return $fila === false ? null : $this->crearProducto($fila);
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Busca un producto por su código único.
     */
    public function buscarPorCodigo(string $codigo): ?Producto
    {
        $sql = 'SELECT id_producto, codigo, nombre, categoria, precio_venta, '
            . 'stock_actual, stock_minimo FROM producto WHERE codigo = :codigo';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute(['codigo' => $codigo]);
            $fila = $sentencia->fetch();

            return $fila === false ? null : $this->crearProducto($fila);
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Devuelve todos los productos registrados.
     *
     * @return Producto[]
     */
    public function listarTodos(): array
    {
        $sql = 'SELECT id_producto, codigo, nombre, categoria, precio_venta, '
            . 'stock_actual, stock_minimo FROM producto ORDER BY nombre ASC';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute();
            $filas = $sentencia->fetchAll();

            return array_map(fn (array $fila): Producto => $this->crearProducto($fila), $filas);
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Devuelve los productos en su mínimo o por debajo.
     *
     * @return Producto[]
     */
    public function listarStockBajo(): array
    {
        $sql = 'SELECT id_producto, codigo, nombre, categoria, precio_venta, '
            . 'stock_actual, stock_minimo FROM producto '
            . 'WHERE stock_actual <= stock_minimo ORDER BY nombre ASC';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute();
            $filas = $sentencia->fetchAll();

            return array_map(fn (array $fila): Producto => $this->crearProducto($fila), $filas);
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Resta la cantidad indicada al stock persistido.
     */
    public function descontarStock(int $idProducto, int $cantidad): void
    {
        $sql = 'UPDATE producto SET stock_actual = stock_actual - :cantidad '
            . 'WHERE id_producto = :id';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute([
                'cantidad' => $cantidad,
                'id' => $idProducto,
            ]);
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Bloquea el producto hasta finalizar la transacción actual.
     */
    public function bloquearParaVenta(int $idProducto): ?Producto
    {
        $sql = 'SELECT id_producto, codigo, nombre, categoria, precio_venta, '
            . 'stock_actual, stock_minimo FROM producto WHERE id_producto = :id FOR UPDATE';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute(['id' => $idProducto]);
            $fila = $sentencia->fetch();

            return $fila === false ? null : $this->crearProducto($fila);
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Convierte una fila de producto en su modelo.
     *
     * @param array<string, mixed> $fila Fila devuelta por PDO.
     */
    private function crearProducto(array $fila): Producto
    {
        return new Producto(
            (int) $fila['id_producto'],
            (string) $fila['codigo'],
            (string) $fila['nombre'],
            (string) $fila['categoria'],
            (float) $fila['precio_venta'],
            (int) $fila['stock_actual'],
            (int) $fila['stock_minimo']
        );
    }
}
