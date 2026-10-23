# Documento de Diseño — Sistema de Ventas «La Esquina»

## 1. Arquitectura en capas

Flujo: **views → Controller → Service → Repository → Model** (PHP 8, `declare(strict_types=1)`, `namespace App\`, PSR-12).

| Capa | Qué hace | Qué NO hace |
|------|----------|-------------|
| **Model** (`app/Models`) | Representa una entidad (Producto, Venta) con propiedades tipadas y getters. | No accede a la base de datos ni valida reglas de negocio. |
| **Repository** (`app/Repositories`) | Ejecuta SQL con PDO y sentencias preparadas; convierte filas en Models. Devuelve `null` si el registro no existe. | No aplica reglas de negocio ni decide si una venta es válida. Nunca concatena SQL. |
| **Service** (`app/Services`) | Aplica las reglas de negocio RN1–RN5 y controla la transacción de la venta. Lanza excepciones propias. | No imprime HTML ni lee `$_POST` directamente. |
| **Controller** (`app/Controllers`) | Recibe la petición, verifica el token CSRF, convierte los datos del formulario a tipos correctos, llama al Service y elige la vista. Captura excepciones y prepara mensajes claros. | No contiene SQL ni reglas de negocio. |
| **views** (`views/`) | Muestra formularios y listados; escapa toda salida con `htmlspecialchars`; incluye el token CSRF oculto. | No contiene lógica de negocio ni consultas. |

## 2. Base de datos `bodega_ventas` (utf8mb4, utf8mb4_unicode_ci, InnoDB)

### Tabla `producto`

| Campo | Tipo | Clave | Restricción |
|-------|------|-------|-------------|
| id_producto | INT AUTO_INCREMENT | PK | — |
| codigo | VARCHAR(10) | UNIQUE | NOT NULL (ej. P001) |
| nombre | VARCHAR(100) | — | NOT NULL |
| categoria | VARCHAR(50) | — | NOT NULL |
| precio_venta | DECIMAL(10,2) | — | NOT NULL, CHECK (precio_venta >= 0) (ej. 22.50) |
| stock_actual | INT | — | NOT NULL, CHECK (stock_actual >= 0) |
| stock_minimo | INT | — | NOT NULL, CHECK (stock_minimo >= 0) |

### Tabla `venta`

| Campo | Tipo | Clave | Restricción |
|-------|------|-------|-------------|
| id_venta | INT AUTO_INCREMENT | PK | — |
| id_producto | INT | FK → producto(id_producto) | NOT NULL, ON DELETE RESTRICT |
| cantidad | INT | — | NOT NULL, CHECK (cantidad > 0) |
| precio_unitario | DECIMAL(10,2) | — | NOT NULL, CHECK (precio_unitario >= 0) — precio vigente al vender |
| total | DECIMAL(10,2) | — | NOT NULL, CHECK (total >= 0) |
| fecha_hora | DATETIME | — | NOT NULL |

## 3. Contratos de clases

### Models
```php
final class Producto {
    public function __construct(
        private ?int $idProducto, private string $codigo, private string $nombre,
        private string $categoria, private float $precioVenta,
        private int $stockActual, private int $stockMinimo) {}
    public function getIdProducto(): ?int;
    public function getCodigo(): string;
    public function getNombre(): string;
    public function getCategoria(): string;
    public function getPrecioVenta(): float;
    public function getStockActual(): int;
    public function getStockMinimo(): int;
    public function estaEnStockBajo(): bool; // stockActual <= stockMinimo
}

final class Venta {
    public function __construct(
        private ?int $idVenta, private int $idProducto, private int $cantidad,
        private float $precioUnitario, private float $total,
        private \DateTimeImmutable $fechaHora, private ?string $nombreProducto = null) {}
    // getters con los mismos tipos
}
```

### Repositories
```php
interface ProductoRepositoryInterface {
    public function guardar(Producto $producto): int;
    public function buscarPorId(int $idProducto): ?Producto;
    public function buscarPorCodigo(string $codigo): ?Producto;
    /** @return Producto[] */ public function listarTodos(): array;
    /** @return Producto[] */ public function listarStockBajo(): array;
    public function descontarStock(int $idProducto, int $cantidad): void;
    public function bloquearParaVenta(int $idProducto): ?Producto; // SELECT ... FOR UPDATE
}

interface VentaRepositoryInterface {
    public function guardar(Venta $venta): int;
    /** @return Venta[] */ public function listarPorFecha(\DateTimeImmutable $fecha): array;
    public function totalPorFecha(\DateTimeImmutable $fecha): float;
}
```
Implementaciones: `ProductoRepository` y `VentaRepository` (reciben `PDO` en el constructor). Las interfaces permiten usar repositorios falsos en memoria en las pruebas.

### Services
```php
final class ProductoService {
    public function __construct(private ProductoRepositoryInterface $productos) {}
    public function registrarProducto(string $codigo, string $nombre, string $categoria,
        float $precioVenta, int $stockActual, int $stockMinimo): Producto;
    /** @return Producto[] */ public function listarProductos(): array;
    /** @return Producto[] */ public function listarStockBajo(): array;
}

final class VentaService {
    public function __construct(private PDO $pdo,
        private ProductoRepositoryInterface $productos,
        private VentaRepositoryInterface $ventas) {}
    public function registrarVenta(int $idProducto, int $cantidad): Venta;
    /** @return Venta[] */ public function listarVentasDelDia(): array;
    public function totalVendidoDelDia(): float;
}
```

### Controllers
```php
final class ProductoController {
    public function __construct(private ProductoService $service) {}
    public function index(): void;   // RF2
    public function crear(): void;   // RF1 (formulario)
    public function guardar(): void; // RF1 (POST + CSRF)
}
final class VentaController {
    public function __construct(private VentaService $ventaService, private ProductoService $productoService) {}
    public function crear(): void;   // RF3 (formulario)
    public function guardar(): void; // RF3 (POST + CSRF)
    public function delDia(): void;  // RF4
}
```

### Excepciones propias (`app/Exceptions`)
| Clase | Cuándo se lanza |
|-------|-----------------|
| `ValidacionException` | Cantidad ≤ 0 (RN1), precio negativo (RN3), stock negativo, datos vacíos. |
| `StockInsuficienteException` | Cantidad mayor al stock actual (RN2). |
| `ProductoNoEncontradoException` | El producto elegido no existe. |
| `CodigoDuplicadoException` | El código ya existe (RN4). |
| `BaseDatosException` | Cualquier `PDOException`; envuelve el error original. |

## 4. Transacción de la venta (RN5)

`VentaService::registrarVenta(int $idProducto, int $cantidad): Venta`

1. Si `$cantidad <= 0` → `ValidacionException` (RN1).
2. `$pdo->beginTransaction()`.
3. `bloquearParaVenta($idProducto)` → `SELECT ... FOR UPDATE`. Si devuelve `null` → `ProductoNoEncontradoException`.
4. Si el precio vigente es negativo → `ValidacionException` (RN3).
5. Si `$cantidad > stockActual` → `StockInsuficienteException` (RN2).
6. `precioUnitario = precioVenta`; `total = round(precioUnitario * cantidad, 2)`; `fechaHora = new DateTimeImmutable()`.
7. `ventas->guardar(...)` (INSERT) y `productos->descontarStock(...)` (UPDATE).
8. `$pdo->commit()`.
9. En el `catch (\Throwable $e)`: `if ($pdo->inTransaction()) $pdo->rollBack();` y se relanza (las `PDOException` como `BaseDatosException`).

Resultado: la venta y el descuento ocurren juntos o ninguno; el stock nunca queda descontado sin venta.

## 5. Manejo de errores

- **Registro inexistente**: el Repository devuelve `null`; el Service decide lanzar `ProductoNoEncontradoException`.
- **Error de base de datos**: el Repository captura `PDOException` y lanza `BaseDatosException`; el detalle se guarda con `error_log()`.
- **Al usuario**: el Controller captura las excepciones y muestra solo mensajes claros (ej. "No hay stock suficiente: quedan 2 unidades"). `display_errors` desactivado; nunca se muestran trazas.
- **CSRF inválido**: se rechaza la petición con un mensaje y código 419.

## 6. Diseño visual de las vistas

Paleta: morado `#6B21A8` (encabezado y botones), morado oscuro `#4C1D95` (hover), fondo lila `#F5F3FF`, tarjetas blancas `#FFFFFF`, stock bajo en rosado `#DB2777` con etiqueta "⚠ Reponer", éxito verde menta `#10B981`, error rojo `#DC2626`, texto `#1F2937`.