<h1>Registrar producto</h1>
<section class="tarjeta formulario">
    <form method="post" action="index.php?accion=guardar-producto">
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <div class="campo">
            <label for="codigo">Código</label>
            <input id="codigo" name="codigo" type="text" maxlength="10" required
                   value="<?= $e($valores['codigo'] ?? '') ?>">
        </div>
        <div class="campo">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" type="text" maxlength="100" required
                   value="<?= $e($valores['nombre'] ?? '') ?>">
        </div>
        <div class="campo">
            <label for="categoria">Categoría</label>
            <input id="categoria" name="categoria" type="text" maxlength="50" required
                   value="<?= $e($valores['categoria'] ?? '') ?>">
        </div>
        <div class="campo">
            <label for="precio_venta">Precio de venta (S/)</label>
            <input id="precio_venta" name="precio_venta" type="number" min="0" step="0.01" required
                   value="<?= $e($valores['precio_venta'] ?? '') ?>">
        </div>
        <div class="campo">
            <label for="stock_actual">Stock actual</label>
            <input id="stock_actual" name="stock_actual" type="number" min="0" step="1" required
                   value="<?= $e($valores['stock_actual'] ?? '') ?>">
        </div>
        <div class="campo">
            <label for="stock_minimo">Stock mínimo</label>
            <input id="stock_minimo" name="stock_minimo" type="number" min="0" step="1" required
                   value="<?= $e($valores['stock_minimo'] ?? '') ?>">
        </div>
        <div class="acciones">
            <button class="boton" type="submit">Guardar producto</button>
            <a class="boton boton-secundario" href="index.php?accion=productos">Cancelar</a>
        </div>
    </form>
</section>
