<?php

declare(strict_types=1);

use App\Exceptions\ProductoNoEncontradoException;
use App\Exceptions\StockInsuficienteException;
use App\Exceptions\ValidacionException;
use App\Models\Producto;
use App\Models\Venta;
use App\Repositories\ProductoRepositoryInterface;
use App\Repositories\VentaRepositoryInterface;
use App\Services\ProductoService;
use App\Services\VentaService;

spl_autoload_register(
    static function (string $clase): void {
        $prefijo = 'App\\';

        if (!str_starts_with($clase, $prefijo)) {
            return;
        }

        $rutaRelativa = str_replace(
            '\\',
            DIRECTORY_SEPARATOR,
            substr($clase, strlen($prefijo))
        );
        $rutaArchivo = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'app'
            . DIRECTORY_SEPARATOR . $rutaRelativa
            . '.php';

        if (is_file($rutaArchivo)) {
            require $rutaArchivo;
        }
    }
);

/**
 * Contiene los datos compartidos por los repositorios falsos.
 */
final class FakeMemoryDatabase
{
    /**
     * @param Producto[] $productos Productos indexados por identificador.
     * @param Venta[] $ventas Ventas guardadas.
     */
    public function __construct(
        public array $productos = [],
        public array $ventas = [],
        public int $siguienteIdVenta = 1
    ) {
    }
}

/**
 * Simula la conexión PDO y sus límites transaccionales en memoria.
 */
final class FakePdo extends PDO
{
    private bool $transaccionActiva = false;

    /**
     * Copia el estado inicial para poder revertir la transacción.
     *
     * @var array{productos: Producto[], ventas: Venta[], siguienteIdVenta: int}|null
     */
    private ?array $instantanea = null;

    public int $transaccionesConfirmadas = 0;
    public int $transaccionesRevertidas = 0;

    /**
     * Recibe la base en memoria sin abrir una conexión real.
     */
    public function __construct(private FakeMemoryDatabase $base)
    {
    }

    /**
     * Inicia una transacción simulada.
     */
    public function beginTransaction(): bool
    {
        if ($this->transaccionActiva) {
            throw new RuntimeException('Ya existe una transacción activa.');
        }

        $this->instantanea = [
            'productos' => $this->base->productos,
            'ventas' => $this->base->ventas,
            'siguienteIdVenta' => $this->base->siguienteIdVenta,
        ];
        $this->transaccionActiva = true;

        return true;
    }

    /**
     * Confirma la transacción simulada.
     */
    public function commit(): bool
    {
        if (!$this->transaccionActiva) {
            throw new RuntimeException('No existe una transacción activa.');
        }

        $this->transaccionActiva = false;
        $this->instantanea = null;
        $this->transaccionesConfirmadas++;

        return true;
    }

    /**
     * Revierte las modificaciones realizadas en la transacción.
     */
    public function rollBack(): bool
    {
        if (!$this->transaccionActiva || $this->instantanea === null) {
            throw new RuntimeException('No existe una transacción para revertir.');
        }

        $this->base->productos = $this->instantanea['productos'];
        $this->base->ventas = $this->instantanea['ventas'];
        $this->base->siguienteIdVenta = $this->instantanea['siguienteIdVenta'];
        $this->instantanea = null;
        $this->transaccionActiva = false;
        $this->transaccionesRevertidas++;

        return true;
    }

    /**
     * Indica si hay una transacción simulada activa.
     */
    public function inTransaction(): bool
    {
        return $this->transaccionActiva;
    }
}

/**
 * Implementa el contrato de productos usando exclusivamente memoria.
 */
final class FakeProductoRepository implements ProductoRepositoryInterface
{
    /**
     * Recibe la base compartida con el PDO y el repositorio de ventas.
     */
    public function __construct(private FakeMemoryDatabase $base)
    {
    }

    /**
     * Guarda un producto y devuelve un identificador de prueba.
     */
    public function guardar(Producto $producto): int
    {
        $id = $producto->getIdProducto() ?? count($this->base->productos) + 1;
        $this->base->productos[$id] = new Producto(
            $id,
            $producto->getCodigo(),
            $producto->getNombre(),
            $producto->getCategoria(),
            $producto->getPrecioVenta(),
            $producto->getStockActual(),
            $producto->getStockMinimo()
        );

        return $id;
    }

    /**
     * Busca un producto por identificador.
     */
    public function buscarPorId(int $idProducto): ?Producto
    {
        return $this->base->productos[$idProducto] ?? null;
    }

    /**
     * Busca un producto por código.
     */
    public function buscarPorCodigo(string $codigo): ?Producto
    {
        foreach ($this->base->productos as $producto) {
            if ($producto->getCodigo() === $codigo) {
                return $producto;
            }
        }

        return null;
    }

    /**
     * Devuelve todos los productos guardados.
     *
     * @return Producto[]
     */
    public function listarTodos(): array
    {
        return array_values($this->base->productos);
    }

    /**
     * Devuelve únicamente los productos en o bajo el stock mínimo.
     *
     * @return Producto[]
     */
    public function listarStockBajo(): array
    {
        return array_values(array_filter(
            $this->base->productos,
            static fn (Producto $producto): bool => $producto->estaEnStockBajo()
        ));
    }

    /**
     * Sustituye el producto por una copia con el stock actualizado.
     */
    public function descontarStock(int $idProducto, int $cantidad): void
    {
        $producto = $this->base->productos[$idProducto] ?? null;

        if ($producto === null) {
            return;
        }

        $this->base->productos[$idProducto] = new Producto(
            $producto->getIdProducto(),
            $producto->getCodigo(),
            $producto->getNombre(),
            $producto->getCategoria(),
            $producto->getPrecioVenta(),
            $producto->getStockActual() - $cantidad,
            $producto->getStockMinimo()
        );
    }

    /**
     * Devuelve el producto solicitado para una venta en memoria.
     */
    public function bloquearParaVenta(int $idProducto): ?Producto
    {
        return $this->buscarPorId($idProducto);
    }
}

/**
 * Implementa el contrato de ventas usando exclusivamente memoria.
 */
final class FakeVentaRepository implements VentaRepositoryInterface
{
    /**
     * Recibe la base compartida con el repositorio de productos.
     */
    public function __construct(private FakeMemoryDatabase $base)
    {
    }

    /**
     * Guarda una venta y devuelve un identificador de prueba.
     */
    public function guardar(Venta $venta): int
    {
        $id = $this->base->siguienteIdVenta++;
        $this->base->ventas[] = new Venta(
            $id,
            $venta->getIdProducto(),
            $venta->getCantidad(),
            $venta->getPrecioUnitario(),
            $venta->getTotal(),
            $venta->getFechaHora(),
            $venta->getNombreProducto()
        );

        return $id;
    }

    /**
     * Devuelve las ventas que corresponden a la fecha solicitada.
     *
     * @return Venta[]
     */
    public function listarPorFecha(DateTimeImmutable $fecha): array
    {
        return array_values(array_filter(
            $this->base->ventas,
            static fn (Venta $venta): bool => $venta->getFechaHora()->format('Y-m-d')
                === $fecha->format('Y-m-d')
        ));
    }

    /**
     * Calcula el total de las ventas de la fecha solicitada.
     */
    public function totalPorFecha(DateTimeImmutable $fecha): float
    {
        return array_reduce(
            $this->listarPorFecha($fecha),
            static fn (float $total, Venta $venta): float => $total + $venta->getTotal(),
            0.0
        );
    }
}

/**
 * Agrupa los dobles necesarios para ejecutar una prueba sin MySQL.
 */
function crearEntornoPrueba(array $productos = []): array
{
    $base = new FakeMemoryDatabase();

    foreach ($productos as $producto) {
        $base->productos[$producto->getIdProducto()] = $producto;
    }

    $pdo = new FakePdo($base);
    $productosRepositorio = new FakeProductoRepository($base);
    $ventasRepositorio = new FakeVentaRepository($base);

    return [
        'base' => $base,
        'pdo' => $pdo,
        'productos' => $productosRepositorio,
        'ventas' => $ventasRepositorio,
        'productoService' => new ProductoService($productosRepositorio),
        'ventaService' => new VentaService($pdo, $productosRepositorio, $ventasRepositorio),
    ];
}

/**
 * Crea un producto de prueba con valores fáciles de reconocer.
 */
function crearProducto(
    int $id,
    float $precio = 2.50,
    int $stock = 10,
    int $minimo = 3
): Producto {
    return new Producto(
        $id,
        'P' . str_pad((string) $id, 3, '0', STR_PAD_LEFT),
        'Producto ' . $id,
        'Pruebas',
        $precio,
        $stock,
        $minimo
    );
}

/**
 * Verifica que la operación lance la excepción indicada.
 *
 * @param class-string<Throwable> $claseExcepcion Clase esperada.
 */
function comprobarExcepcion(string $claseExcepcion, callable $operacion): void
{
    try {
        $operacion();
    } catch (Throwable $excepcion) {
        if ($excepcion instanceof $claseExcepcion) {
            return;
        }

        throw new RuntimeException(
            'Se esperaba ' . $claseExcepcion . ' y se recibió '
                . $excepcion::class . '.',
            0,
            $excepcion
        );
    }

    throw new RuntimeException('No se lanzó ' . $claseExcepcion . '.');
}

/**
 * Comprueba una condición y falla con un mensaje legible.
 */
function comprobar(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

$casos = [
    'Venta válida calcula el total y descuenta stock' => static function (): void {
        $entorno = crearEntornoPrueba([crearProducto(1, 2.50, 10, 3)]);
        $venta = $entorno['ventaService']->registrarVenta(1, 4);

        comprobar($venta->getTotal() === 10.0, 'El total de la venta no fue 10.00.');
        comprobar(
            $entorno['productos']->buscarPorId(1)?->getStockActual() === 6,
            'El stock no se redujo de 10 a 6.'
        );
        comprobar(count($entorno['base']->ventas) === 1, 'No se guardó la venta.');
        comprobar(
            $entorno['pdo']->transaccionesConfirmadas === 1,
            'La transacción no se confirmó.'
        );
    },
    'Cantidad cero o negativa se rechaza' => static function (): void {
        foreach ([0, -1] as $cantidad) {
            $entorno = crearEntornoPrueba([crearProducto(1)]);
            comprobarExcepcion(
                ValidacionException::class,
                static fn () => $entorno['ventaService']->registrarVenta(1, $cantidad)
            );
            comprobar(
                $entorno['productos']->buscarPorId(1)?->getStockActual() === 10,
                'El stock cambió al rechazar cantidad ' . $cantidad . '.'
            );
        }
    },
    'Cantidad mayor al stock se rechaza sin modificar stock' => static function (): void {
        $entorno = crearEntornoPrueba([crearProducto(1, 2.50, 3, 1)]);

        comprobarExcepcion(
            StockInsuficienteException::class,
            static fn () => $entorno['ventaService']->registrarVenta(1, 4)
        );
        comprobar(
            $entorno['productos']->buscarPorId(1)?->getStockActual() === 3,
            'El stock cambió pese a rechazarse la venta.'
        );
        comprobar($entorno['base']->ventas === [], 'Se guardó una venta rechazada.');
        comprobar(
            $entorno['pdo']->transaccionesRevertidas === 1,
            'La transacción rechazada no se revirtió.'
        );
    },
    'Producto inexistente se rechaza' => static function (): void {
        $entorno = crearEntornoPrueba();

        comprobarExcepcion(
            ProductoNoEncontradoException::class,
            static fn () => $entorno['ventaService']->registrarVenta(404, 1)
        );
        comprobar($entorno['base']->ventas === [], 'Se guardó una venta sin producto.');
    },
    'Producto con precio negativo se rechaza' => static function (): void {
        $entorno = crearEntornoPrueba([crearProducto(1, -1.00, 10, 3)]);

        comprobarExcepcion(
            ValidacionException::class,
            static fn () => $entorno['ventaService']->registrarVenta(1, 1)
        );
        comprobar(
            $entorno['productos']->buscarPorId(1)?->getStockActual() === 10,
            'El stock cambió al rechazar el precio negativo.'
        );
        comprobar($entorno['base']->ventas === [], 'Se guardó una venta con precio negativo.');
    },
    'Listado de stock bajo devuelve solo productos en o bajo el mínimo' => static function (): void {
        $entorno = crearEntornoPrueba([
            crearProducto(1, 2.50, 3, 3),
            crearProducto(2, 2.50, 1, 3),
            crearProducto(3, 2.50, 4, 3),
        ]);
        $ids = array_map(
            static fn (Producto $producto): ?int => $producto->getIdProducto(),
            $entorno['productoService']->listarStockBajo()
        );

        sort($ids);
        comprobar($ids === [1, 2], 'El listado no devolvió exactamente los productos 1 y 2.');
    },
];

$correctos = 0;
$fallidos = 0;

foreach ($casos as $nombre => $prueba) {
    try {
        $prueba();
        $correctos++;
        echo 'OK - ' . $nombre . PHP_EOL;
    } catch (Throwable $excepcion) {
        $fallidos++;
        echo 'FALLA - ' . $nombre . ': ' . $excepcion->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL;
echo 'Resumen: ' . $correctos . ' OK, ' . $fallidos . ' FALLA'
    . ($fallidos === 1 ? '' : 'S')
    . ', ' . count($casos) . ' casos.' . PHP_EOL;

exit($fallidos === 0 ? 0 : 1);
