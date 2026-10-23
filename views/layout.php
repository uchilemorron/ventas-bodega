<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($tituloPagina) ?></title>
    <style>
        :root {
            color-scheme: light;
            --morado: #6B21A8;
            --morado-hover: #4C1D95;
            --fondo: #F5F3FF;
            --tarjeta: #FFFFFF;
            --rosado: #DB2777;
            --exito: #10B981;
            --error: #DC2626;
            --texto: #1F2937;
            --borde: #E5E7EB;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--texto);
            background: var(--fondo);
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            line-height: 1.5;
        }
        header {
            color: #fff;
            background: var(--morado);
            box-shadow: 0 2px 8px rgb(31 41 55 / 12%);
        }
        .barra {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem 0;
        }
        .marca {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 750;
            text-decoration: none;
        }
        nav { display: flex; gap: .5rem; flex-wrap: wrap; }
        nav a {
            color: #fff;
            padding: .5rem .7rem;
            border-radius: .45rem;
            text-decoration: none;
        }
        nav a:hover, nav a:focus-visible { background: var(--morado-hover); }
        main {
            width: min(1120px, calc(100% - 2rem));
            min-height: calc(100vh - 150px);
            margin: 2rem auto;
        }
        h1 { margin: 0 0 1.25rem; font-size: clamp(1.6rem, 4vw, 2rem); }
        .tarjeta {
            padding: 1.25rem;
            background: var(--tarjeta);
            border: 1px solid var(--borde);
            border-radius: .8rem;
            box-shadow: 0 3px 12px rgb(31 41 55 / 5%);
        }
        .acciones {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .boton {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 2.65rem;
            padding: .55rem .9rem;
            border: 0;
            border-radius: .5rem;
            color: #fff;
            background: var(--morado);
            font: inherit;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
        }
        .boton:hover, .boton:focus-visible { background: var(--morado-hover); }
        .boton-secundario {
            color: var(--morado);
            background: #EDE9FE;
        }
        .boton-secundario:hover, .boton-secundario:focus-visible {
            color: #fff;
            background: var(--morado-hover);
        }
        .mensaje {
            margin: 0 0 1rem;
            padding: .85rem 1rem;
            border-left: 4px solid;
            border-radius: .4rem;
            background: #fff;
        }
        .mensaje-exito { color: #047857; border-color: var(--exito); }
        .mensaje-error { color: #B91C1C; border-color: var(--error); }
        .tabla-contenedor { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: .8rem .65rem; border-bottom: 1px solid var(--borde); }
        th { color: #4B5563; font-size: .9rem; }
        tbody tr:last-child td { border-bottom: 0; }
        .stock-bajo { color: var(--rosado); font-weight: 700; }
        .etiqueta-reponer {
            display: inline-block;
            margin-left: .35rem;
            padding: .1rem .45rem;
            border-radius: 999px;
            color: #fff;
            background: var(--rosado);
            font-size: .78rem;
            white-space: nowrap;
        }
        .formulario { max-width: 640px; }
        .campo { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .35rem; font-weight: 650; }
        input, select {
            width: 100%;
            min-height: 2.7rem;
            padding: .55rem .7rem;
            border: 1px solid #9CA3AF;
            border-radius: .45rem;
            color: var(--texto);
            background: #fff;
            font: inherit;
        }
        input:focus, select:focus {
            border-color: var(--morado);
            outline: 3px solid rgb(107 33 168 / 18%);
        }
        .resumen-total { margin-top: 1.2rem; font-size: 1.25rem; font-weight: 750; }
        .vacio { color: #4B5563; margin: 0; }
        footer {
            padding: 1rem;
            color: #4B5563;
            text-align: center;
            font-size: .9rem;
        }
        @media (max-width: 640px) {
            .barra { align-items: flex-start; flex-direction: column; }
            nav a { padding-inline: .45rem; }
            .tarjeta { padding: .85rem; }
            th, td { padding: .65rem .5rem; }
        }
    </style>
</head>
<body>
<header>
    <div class="barra">
        <a class="marca" href="index.php?accion=productos">Minimarket La Esquina</a>
        <nav aria-label="Navegación principal">
            <a href="index.php?accion=productos">Productos</a>
            <a href="index.php?accion=nuevo-producto">Nuevo producto</a>
            <a href="index.php?accion=nueva-venta">Registrar venta</a>
            <a href="index.php?accion=ventas-dia">Ventas del día</a>
        </nav>
    </div>
</header>
<main>
    <?php if ($mensaje !== ''): ?>
        <p class="mensaje <?= $tipoMensaje === 'error' ? 'mensaje-error' : 'mensaje-exito' ?>" role="status">
            <?= $e($mensaje) ?>
        </p>
    <?php endif; ?>
    <?= $contenido ?>
</main>
<footer>Sistema interno de ventas e inventario</footer>
</body>
</html>
