<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\BaseDatosException;
use App\Exceptions\CodigoDuplicadoException;
use App\Exceptions\ValidacionException;
use App\Services\ProductoService;

/**
 * Atiende el listado y el registro de productos.
 */
final class ProductoController extends AbstractController
{
    /**
     * Recibe el servicio de productos.
     */
    public function __construct(private ProductoService $service)
    {
    }

    /**
     * Muestra todos los productos registrados.
     */
    public function index(): void
    {
        $this->renderizar('productos/index.php', [
            'productos' => $this->service->listarProductos(),
        ]);
    }

    /**
     * Muestra el formulario para registrar un producto.
     */
    public function crear(): void
    {
        $this->renderizar('productos/nuevo.php', ['valores' => []]);
    }

    /**
     * Verifica CSRF, valida los campos y registra el producto.
     */
    public function guardar(): void
    {
        if (!$this->csrfValido()) {
            http_response_code(419);
            $this->renderizar(
                'productos/nuevo.php',
                ['valores' => []],
                'La solicitud no es válida. Vuelve a cargar el formulario.',
                'error'
            );

            return;
        }

        $valores = [
            'codigo' => $this->campoPost('codigo'),
            'nombre' => $this->campoPost('nombre'),
            'categoria' => $this->campoPost('categoria'),
            'precio_venta' => $this->campoPost('precio_venta'),
            'stock_actual' => $this->campoPost('stock_actual'),
            'stock_minimo' => $this->campoPost('stock_minimo'),
        ];

        try {
            foreach ($valores as $valor) {
                if ($valor === '') {
                    throw new ValidacionException('Todos los campos son obligatorios.');
                }
            }

            if (!is_numeric($valores['precio_venta'])) {
                throw new ValidacionException('Los valores deben ser numéricos.');
            }

            $precio = (float) $valores['precio_venta'];

            if (!is_finite($precio)) {
                throw new ValidacionException('Los valores deben ser numéricos.');
            }

            $stockActual = $this->convertirEnteroNoNegativo($valores['stock_actual']);
            $stockMinimo = $this->convertirEnteroNoNegativo($valores['stock_minimo']);
            $this->service->registrarProducto(
                $valores['codigo'],
                $valores['nombre'],
                $valores['categoria'],
                $precio,
                $stockActual,
                $stockMinimo
            );

            $this->redirigir('productos', 'Producto registrado correctamente.');
        } catch (ValidacionException | CodigoDuplicadoException $excepcion) {
            $this->renderizar(
                'productos/nuevo.php',
                ['valores' => $valores],
                $excepcion->getMessage(),
                'error'
            );
        } catch (BaseDatosException $excepcion) {
            $this->renderizar(
                'productos/nuevo.php',
                ['valores' => $valores],
                'No se pudo completar la operación. Inténtalo de nuevo.',
                'error'
            );
        }
    }

    /**
     * Convierte un campo numérico a entero no negativo.
     */
    private function convertirEnteroNoNegativo(string $valor): int
    {
        if (!is_numeric($valor)) {
            throw new ValidacionException('Los valores deben ser numéricos.');
        }

        if (preg_match('/^\d+$/', $valor) !== 1) {
            throw new ValidacionException('El stock debe ser un número entero mayor o igual a cero.');
        }

        $normalizado = ltrim($valor, '0');
        $entero = filter_var(
            $normalizado === '' ? '0' : $normalizado,
            FILTER_VALIDATE_INT
        );

        if ($entero === false || $entero < 0) {
            throw new ValidacionException('El stock debe ser un número entero mayor o igual a cero.');
        }

        return $entero;
    }
}
