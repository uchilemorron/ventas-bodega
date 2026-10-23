# Sistema de ventas — Minimarket La Esquina

Aplicación web interna en PHP 8 y MySQL para registrar productos, consultar existencias, registrar ventas y ver el total vendido en el día. No incluye autenticación ni roles.

## Requisitos

- PHP 8.0 o superior.
- Extensiones PHP `PDO` y `pdo_mysql`.
- MySQL compatible con `database/schema.sql` (InnoDB, `utf8mb4`).
- Laragon para el entorno local recomendado en Windows.

## Crear la base de datos

1. Inicia MySQL en Laragon.
2. Abre phpMyAdmin, selecciona la herramienta **Importar** y ejecuta `database/schema.sql`. El script crea y selecciona `bodega_ventas` y sus tablas.
3. Después, importa `database/seed.sql` para cargar los productos de demostración.

También puedes ejecutar los dos scripts en ese orden desde un cliente MySQL que admita archivos SQL.

## Configurar `.env`

Copia el archivo de ejemplo a `.env` en la raíz del proyecto:

```powershell
Copy-Item .env.example .env
```

Edita `.env` con los datos de tu instalación local de MySQL:

```dotenv
DB_HOST=127.0.0.1
DB_NAME=bodega_ventas
DB_USER=tu_usuario
DB_PASS=tu_contraseña
```

`.env` está excluido en `.gitignore`; no incluyas credenciales reales en commits. `.env.example` contiene solo valores ilustrativos.

## Ejecutar la aplicación con Laragon

Inicia Apache y MySQL desde Laragon y abre:

<http://localhost/ventas-bodega/public/>

El punto de entrada de la aplicación es `public/index.php`.

## Ejecutar las pruebas

Desde la raíz del proyecto, ejecuta:

```bash
php tests/run.php
```

Las pruebas de Services usan repositorios falsos en memoria y un PDO simulado; no requieren que MySQL esté instalado o activo. Cada caso imprime `OK` o `FALLA` y el proceso devuelve código de salida distinto de cero si hay fallas.
