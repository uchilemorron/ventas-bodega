<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\BaseDatosException;
use App\Exceptions\ProductoNoEncontradoException;
use App\Exceptions\StockInsuficienteException;
use App\Exceptions\ValidacionException;
use App\Services\ProductoService;
use App\Services\VentaService;
use DateTimeImmutable;

/**
 * Atiende los formularios de venta y el resumen diario.
 */
final class VentaController extends AbstractController
{
    /**
     * Recibe los servicios necesarios para las operaciones de venta.
     */
    public function __construct(
        private VentaService $ventaService,
        private ProductoService $productoService
    ) {
    }

    /**
     * Muestra el formulario de registro de venta.
     */
    public function crear(): void
    {
        $this->renderizar('ventas/nueva.php', [
            'productos' => $this->productoService->listarProductos(),
            'valores' => [],
        ]);
    }

    /**
     * Verifica CSRF, valida la solicitud y registra la venta.
     */
    public function guardar(): void
    {
        if (!$this->csrfValido()) {
            http_response_code(419);
            $this->renderizar(
                'ventas/nueva.php',
                [
                    'productos' => $this->productoService->listarProductos(),
                    'valores' => [],
                ],
                'La solicitud no es válida. Vuelve a cargar el formulario.',
                'error'
            );

            return;
        }

        $valores = [
            'id_producto' => $this->campoPost('id_producto'),
            'cantidad' => $this->campoPost('cantidad'),
        ];

        try {
            $idProducto = $this->convertirEntero($valores['id_producto']);

            if ($idProducto === null || $idProducto <= 0) {
                throw new ProductoNoEncontradoException(
                    'El producto seleccionado no existe.'
                );
            }

            $cantidad = $this->convertirEntero($valores['cantidad']);

            if ($cantidad === null || $cantidad <= 0) {
                throw new ValidacionException(
                    'La cantidad debe ser un número entero mayor que cero.'
                );
            }

            $venta = $this->ventaService->registrarVenta($idProducto, $cantidad);
            $this->redirigir(
                'ventas-dia',
                'Venta registrada correctamente. Total: S/ '
                    . number_format($venta->getTotal(), 2, '.', ',')
            );
        } catch (
            ValidacionException
            | StockInsuficienteException
            | ProductoNoEncontradoException $excepcion
        ) {
            $this->renderizar(
                'ventas/nueva.php',
                [
                    'productos' => $this->productoService->listarProductos(),
                    'valores' => $valores,
                ],
                $excepcion->getMessage(),
                'error'
            );
        } catch (BaseDatosException $excepcion) {
            $this->renderizar(
                'ventas/nueva.php',
                [
                    'productos' => $this->productoService->listarProductos(),
                    'valores' => $valores,
                ],
                'No se pudo completar la operación. Inténtalo de nuevo.',
                'error'
            );
        }
    }

    /**
     * Muestra las ventas registradas durante el día actual.
     */
    public function delDia(): void
    {
        $this->renderizar('ventas/dia.php', [
            'ventas' => $this->ventaService->listarVentasDelDia(),
            'totalDia' => $this->ventaService->totalVendidoDelDia(),
            'fechaActual' => new DateTimeImmutable('today'),
        ]);
    }

    /**
     * Convierte texto de dígitos a entero y permite ceros iniciales.
     */
    private function convertirEntero(string $valor): ?int
    {
        if (preg_match('/^\d+$/', $valor) !== 1) {
            return null;
        }

        $normalizado = ltrim($valor, '0');
        $entero = filter_var(
            $normalizado === '' ? '0' : $normalizado,
            FILTER_VALIDATE_INT
        );

        return $entero === false ? null : $entero;
    }
}
