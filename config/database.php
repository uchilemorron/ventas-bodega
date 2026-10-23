<?php

declare(strict_types=1);

use App\Exceptions\BaseDatosException;
$rutaEnv = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (!is_file($rutaEnv) || !is_readable($rutaEnv)) {
    throw new BaseDatosException('No se pudo configurar la conexión a la base de datos.');
}

$configuracion = [];
$lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES);

if ($lineas === false) {
    throw new BaseDatosException('No se pudo configurar la conexión a la base de datos.');
}

foreach ($lineas as $linea) {
    $linea = trim($linea);

    if ($linea === '' || str_starts_with($linea, '#')) {
        continue;
    }

    if (preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/', $linea, $coincidencias) !== 1) {
        continue;
    }

    $valor = trim($coincidencias[2]);

    if (
        strlen($valor) >= 2
        && (($valor[0] === '"' && substr($valor, -1) === '"')
            || ($valor[0] === "'" && substr($valor, -1) === "'"))
    ) {
        $valor = substr($valor, 1, -1);
    }

    $configuracion[$coincidencias[1]] = $valor;
}

foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $clave) {
    if (!array_key_exists($clave, $configuracion)) {
        throw new BaseDatosException('No se pudo configurar la conexión a la base de datos.');
    }
}

if (
    $configuracion['DB_HOST'] === ''
    || $configuracion['DB_NAME'] === ''
    || $configuracion['DB_USER'] === ''
) {
    throw new BaseDatosException('No se pudo configurar la conexión a la base de datos.');
}

try {
    return new PDO(
        'mysql:host=' . $configuracion['DB_HOST']
            . ';dbname=' . $configuracion['DB_NAME']
            . ';charset=utf8mb4',
        $configuracion['DB_USER'],
        $configuracion['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $excepcion) {
    throw new BaseDatosException(
        'No se pudo configurar la conexión a la base de datos.',
        $excepcion
    );
}
