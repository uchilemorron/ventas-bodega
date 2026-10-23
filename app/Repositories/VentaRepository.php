<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\BaseDatosException;
use App\Models\Venta;
use DateTimeImmutable;
use PDO;
use PDOException;

/**
 * Ejecuta consultas PDO relacionadas con ventas.
 */
final class VentaRepository implements VentaRepositoryInterface
{
    /**
     * Recibe la conexión PDO de la aplicación.
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Guarda una venta y devuelve su identificador.
     */
    public function guardar(Venta $venta): int
    {
        $sql = 'INSERT INTO venta '
            . '(id_producto, cantidad, precio_unitario, total, fecha_hora) '
            . 'VALUES (:producto, :cantidad, :precio, :total, :fecha)';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute([
                'producto' => $venta->getIdProducto(),
                'cantidad' => $venta->getCantidad(),
                'precio' => $venta->getPrecioUnitario(),
                'total' => $venta->getTotal(),
                'fecha' => $venta->getFechaHora()->format('Y-m-d H:i:s'),
            ]);

            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Devuelve las ventas del día indicado ordenadas cronológicamente.
     *
     * @return Venta[]
     */
    public function listarPorFecha(DateTimeImmutable $fecha): array
    {
        $inicio = $fecha->setTime(0, 0, 0);
        $fin = $inicio->modify('+1 day');
        $sql = 'SELECT v.id_venta, v.id_producto, p.nombre AS nombre_producto, '
            . 'v.cantidad, v.precio_unitario, v.total, v.fecha_hora '
            . 'FROM venta v INNER JOIN producto p ON p.id_producto = v.id_producto '
            . 'WHERE v.fecha_hora >= :inicio AND v.fecha_hora < :fin '
            . 'ORDER BY v.fecha_hora ASC, v.id_venta ASC';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute([
                'inicio' => $inicio->format('Y-m-d H:i:s'),
                'fin' => $fin->format('Y-m-d H:i:s'),
            ]);

            return array_map(
                fn (array $fila): Venta => $this->crearVenta($fila),
                $sentencia->fetchAll()
            );
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Devuelve la suma de las ventas del día indicado.
     */
    public function totalPorFecha(DateTimeImmutable $fecha): float
    {
        $inicio = $fecha->setTime(0, 0, 0);
        $fin = $inicio->modify('+1 day');
        $sql = 'SELECT COALESCE(SUM(total), 0) AS total_dia FROM venta '
            . 'WHERE fecha_hora >= :inicio AND fecha_hora < :fin';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute([
                'inicio' => $inicio->format('Y-m-d H:i:s'),
                'fin' => $fin->format('Y-m-d H:i:s'),
            ]);
            $fila = $sentencia->fetch();

            return (float) $fila['total_dia'];
        } catch (PDOException $excepcion) {
            throw new BaseDatosException('No se pudo completar la operación.', $excepcion);
        }
    }

    /**
     * Convierte una fila de venta en su modelo.
     *
     * @param array<string, mixed> $fila Fila devuelta por PDO.
     */
    private function crearVenta(array $fila): Venta
    {
        return new Venta(
            (int) $fila['id_venta'],
            (int) $fila['id_producto'],
            (int) $fila['cantidad'],
            (float) $fila['precio_unitario'],
            (float) $fila['total'],
            new DateTimeImmutable((string) $fila['fecha_hora']),
            (string) $fila['nombre_producto']
        );
    }
}
