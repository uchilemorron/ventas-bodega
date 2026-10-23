<?php

declare(strict_types=1);

use App\Controllers\ProductoController;
use App\Controllers\VentaController;
use App\Exceptions\BaseDatosException;
use App\Repositories\ProductoRepository;
use App\Repositories\VentaRepository;
use App\Services\ProductoService;
use App\Services\VentaService;
ini_set('display_errors', '0');
date_default_timezone_set('America/Lima');

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

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

try {
    $pdo = require dirname(__DIR__)
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR . 'database.php';

    $productoRepository = new ProductoRepository($pdo);
    $ventaRepository = new VentaRepository($pdo);
    $productoService = new ProductoService($productoRepository);
    $ventaService = new VentaService($pdo, $productoRepository, $ventaRepository);
    $productoController = new ProductoController($productoService);
    $ventaController = new VentaController($ventaService, $productoService);

    $rutas = [
        'productos' => ['GET', [$productoController, 'index']],
        'nuevo-producto' => ['GET', [$productoController, 'crear']],
        'guardar-producto' => ['POST', [$productoController, 'guardar']],
        'nueva-venta' => ['GET', [$ventaController, 'crear']],
        'guardar-venta' => ['POST', [$ventaController, 'guardar']],
        'ventas-dia' => ['GET', [$ventaController, 'delDia']],
    ];

    $accion = $_GET['accion'] ?? 'productos';

    if (!is_string($accion) || !isset($rutas[$accion])) {
        http_response_code(404);
        echo '<!doctype html><html lang="es"><meta charset="UTF-8">'
            . '<title>No encontrado</title><body><h1>Página no encontrada</h1>'
            . '<p>La página solicitada no existe.</p>'
            . '<a href="index.php?accion=productos">Volver al inicio</a>'
            . '</body></html>';
    } else {
        [$metodoEsperado, $controlador] = $rutas[$accion];
        $metodoActual = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($metodoActual !== $metodoEsperado) {
            header('Allow: ' . $metodoEsperado);
            http_response_code(405);
            echo '<!doctype html><html lang="es"><meta charset="UTF-8">'
                . '<title>Método no permitido</title><body>'
                . '<h1>El método de solicitud no está permitido.</h1>'
                . '<a href="index.php?accion=productos">Volver al inicio</a>'
                . '</body></html>';
        } else {
            $controlador();
        }
    }
} catch (BaseDatosException $excepcion) {
    http_response_code(500);
    echo '<!doctype html><html lang="es"><meta charset="UTF-8">'
        . '<title>Error</title><body><h1>No se pudo completar la operación.</h1>'
        . '<p>Verifica la configuración y vuelve a intentarlo.</p>'
        . '<a href="index.php?accion=productos">Volver a intentar</a>'
        . '</body></html>';
} catch (\Throwable $excepcion) {
    error_log('Error no controlado: ' . $excepcion->getMessage());
    http_response_code(500);
    echo '<!doctype html><html lang="es"><meta charset="UTF-8">'
        . '<title>Error</title><body><h1>No se pudo completar la operación.</h1>'
        . '<p>Inténtalo de nuevo más tarde.</p>'
        . '<a href="index.php?accion=productos">Volver a intentar</a>'
        . '</body></html>';
}
