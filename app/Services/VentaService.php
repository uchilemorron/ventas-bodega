<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BaseDatosException;
use App\Exceptions\ProductoNoEncontradoException;
use App\Exceptions\StockInsuficienteException;
use App\Exceptions\ValidacionException;
use App\Models\Venta;
use App\Repositories\ProductoRepositoryInterface;
use App\Repositories\VentaRepositoryInterface;
use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;

/**
 * Aplica las reglas de negocio y la transacción de ventas.
 */
final class VentaService
{
    /**
     * Recibe la conexión transaccional y los repositorios de la venta.
     */
    public function __construct(
        private PDO $pdo,
        private ProductoRepositoryInterface $productos,
        private VentaRepositoryInterface $ventas
    ) {
    }

    /**
     * Registra la venta y descuenta el stock en una única transacción.
     */
    public function registrarVenta(int $idProducto, int $cantidad): Venta
    {
        if ($cantidad <= 0) {
            throw new ValidacionException(
                'La cantidad debe ser un número entero mayor que cero.'
            );
        }

        try {
            $this->pdo->beginTransaction();
            $producto = $this->productos->bloquearParaVenta($idProducto);

            if ($producto === null) {
                throw new ProductoNoEncontradoException('El producto seleccionado no existe.');
            }

            if ($producto->getPrecioVenta() < 0) {
                throw new ValidacionException('El precio no puede ser negativo.');
            }

            if ($cantidad > $producto->getStockActual()) {
                throw new StockInsuficienteException(
                    'No hay stock suficiente: quedan '
                        . $producto->getStockActual()
                        . ' unidades.'
                );
            }

            $precioUnitario = $producto->getPrecioVenta();
            $total = round($precioUnitario * $cantidad, 2);
            $fechaHora = new DateTimeImmutable();
            $venta = new Venta(
                null,
                $idProducto,
                $cantidad,
                $precioUnitario,
                $total,
                $fechaHora,
                $producto->getNombre()
            );

            $idVenta = $this->ventas->guardar($venta);
            $this->productos->descontarStock($idProducto, $cantidad);
            $this->pdo->commit();

            return new Venta(
                $idVenta,
                $idProducto,
                $cantidad,
                $precioUnitario,
                $total,
                $fechaHora,
                $producto->getNombre()
            );
        } catch (Throwable $excepcion) {
            if ($this->pdo->inTransaction()) {
                try {
                    $this->pdo->rollBack();
                } catch (PDOException $errorRollback) {
                    throw new BaseDatosException(
                        'No se pudo completar la operación.',
                        $errorRollback
                    );
                }
            }

            if ($excepcion instanceof PDOException) {
                throw new BaseDatosException(
                    'No se pudo completar la operación.',
                    $excepcion
                );
            }

            throw $excepcion;
        }
    }

    /**
     * Devuelve las ventas de la fecha actual.
     *
     * @return Venta[]
     */
    public function listarVentasDelDia(): array
    {
        return $this->ventas->listarPorFecha(new DateTimeImmutable('today'));
    }

    /**
     * Devuelve el importe total vendido en la fecha actual.
     */
    public function totalVendidoDelDia(): float
    {
        return $this->ventas->totalPorFecha(new DateTimeImmutable('today'));
    }
}
