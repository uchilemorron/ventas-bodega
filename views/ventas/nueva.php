<h1>Registrar venta</h1>
<section class="tarjeta formulario">
    <?php if ($productos === []): ?>
        <p class="vacio">No hay productos registrados para vender.</p>
        <p><a class="boton" href="index.php?accion=nuevo-producto">Registrar producto</a></p>
    <?php else: ?>
        <form method="post" action="index.php?accion=guardar-venta">
            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
            <div class="campo">
                <label for="id_producto">Producto</label>
                <select id="id_producto" name="id_producto" required>
                    <option value="">Selecciona un producto</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?= $e($producto->getIdProducto()) ?>"
                            <?= (string) ($valores['id_producto'] ?? '') === (string) $producto->getIdProducto()
                                ? 'selected'
                                : '' ?>
                            <?= $producto->getStockActual() === 0 ? 'disabled' : '' ?>>
                            <?= $e($producto->getCodigo()) ?> —
                            <?= $e($producto->getNombre()) ?> (stock:
                            <?= $e($producto->getStockActual()) ?>, S/
                            <?= $e(number_format($producto->getPrecioVenta(), 2, '.', ',')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="cantidad">Cantidad</label>
                <input id="cantidad" name="cantidad" type="number" min="1" step="1" required
                       value="<?= $e($valores['cantidad'] ?? '') ?>">
            </div>
            <div class="acciones">
                <button class="boton" type="submit">Registrar venta</button>
                <a class="boton boton-secundario" href="index.php?accion=productos">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</section>
