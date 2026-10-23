<h1>Ventas del día: <?= $e($fechaActual->format('d/m/Y')) ?></h1>
<div class="acciones">
    <a class="boton" href="index.php?accion=nueva-venta">Registrar venta</a>
</div>
<section class="tarjeta" aria-label="Ventas de hoy">
    <?php if ($ventas === []): ?>
        <p class="vacio">No hay ventas registradas hoy.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                <tr>
                    <th scope="col">Hora</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Precio unitario</th>
                    <th scope="col">Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?= $e($venta->getFechaHora()->format('H:i:s')) ?></td>
                        <td><?= $e($venta->getNombreProducto() ?? '') ?></td>
                        <td><?= $e($venta->getCantidad()) ?></td>
                        <td>S/ <?= $e(number_format($venta->getPrecioUnitario(), 2, '.', ',')) ?></td>
                        <td>S/ <?= $e(number_format($venta->getTotal(), 2, '.', ',')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <p class="resumen-total">
        Total vendido hoy: S/ <?= $e(number_format($totalDia, 2, '.', ',')) ?>
    </p>
</section>
