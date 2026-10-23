<h1>Productos e inventario</h1>
<div class="acciones">
    <a class="boton" href="index.php?accion=nuevo-producto">Registrar producto</a>
    <a class="boton boton-secundario" href="index.php?accion=nueva-venta">Registrar venta</a>
</div>
<section class="tarjeta" aria-label="Listado de productos">
    <?php if ($productos === []): ?>
        <p class="vacio">No hay productos registrados.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Precio</th>
                    <th scope="col">Stock actual</th>
                    <th scope="col">Stock mínimo</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr class="<?= $producto->estaEnStockBajo() ? 'stock-bajo' : '' ?>">
                        <td><?= $e($producto->getCodigo()) ?></td>
                        <td>
                            <?= $e($producto->getNombre()) ?>
                            <?php if ($producto->estaEnStockBajo()): ?>
                                <span class="etiqueta-reponer">⚠ Reponer</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $e($producto->getCategoria()) ?></td>
                        <td>S/ <?= $e(number_format($producto->getPrecioVenta(), 2, '.', ',')) ?></td>
                        <td><?= $e($producto->getStockActual()) ?></td>
                        <td><?= $e($producto->getStockMinimo()) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
