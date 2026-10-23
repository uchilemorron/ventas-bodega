Necesito que trabajes como analista de requisitos y redactes el documento base D0.md de un sistema web interno para un minimarket de barrio. Guárdalo en la raíz del proyecto y escríbelo en español, con lenguaje claro para que lo entienda el dueño del negocio.

CONTEXTO DEL NEGOCIO: El minimarket «La Esquina» está en San Vicente de Cañete y vende abarrotes, lácteos, bebidas, conservas y productos de limpieza. Actualmente las ventas se apuntan en un cuaderno y el stock se controla mirando los estantes. Por eso el dueño no conoce cuánto vendió al cerrar el día y en varias ocasiones se quedó sin productos de alta rotación sin darse cuenta. El sistema lo usará el cajero y debe permitir:
(1) registrar productos con código, nombre, categoría, precio de venta, stock actual y stock mínimo;
(2) listar los productos destacando los que están en su stock mínimo o por debajo;
(3) registrar la venta de un producto: el cajero escoge el producto y la cantidad; el sistema usa el precio vigente, calcula el total, guarda la fecha y hora y descuenta el stock;
(4) listar las ventas del día junto con el total vendido.
Reglas del negocio: la cantidad vendida debe ser mayor que cero; no se puede vender más de lo disponible en stock; el precio de venta no puede ser negativo; el código del producto no se puede repetir; la venta y el descuento del stock se realizan juntos o no se realiza ninguno.

Estructura obligatoria de D0.md:
1. El problema resumido en una sola frase.
2. Tabla de usuarios (cajero y dueño) con lo que hace cada uno en el sistema.
3. Proceso actual, sus dificultades y las consecuencias para el negocio.
4. Requisitos funcionales RF1 a RF4 en una tabla (solo los cuatro del contexto).
5. Requisitos no funcionales RNF1 en adelante, centrados en seguridad e integridad de datos: PHP 8 orientado a objetos, MySQL, PDO con sentencias preparadas, token CSRF en formularios, salida escapada con htmlspecialchars, credenciales en archivo .env y transacción para la venta.
6. Reglas de negocio RN1 a RN5 en una tabla, indicando a qué RF afecta cada una.
7. Fuera de alcance: inicio de sesión, roles, clientes, boletas o facturación electrónica, agentes conversacionales y despliegue público.
8. Supuestos, cada uno empezando con la palabra SUPUESTO:
SUPUESTO: solo un cajero usa el sistema por turno.
SUPUESTO: los precios se manejan en soles (S/) e incluyen IGV.
SUPUESTO: cada venta corresponde a un único producto.
SUPUESTO: el cajero registra los productos nuevos cuando llega mercadería.
SUPUESTO: las ventas del día se cuentan desde las 00:00 hasta las 23:59 de la fecha actual.

No incluyas funcionalidades que no estén en el contexto.


Revisé D0.md. Corrige solo esto, sin cambiar lo demás: agrega en la tabla de requisitos no funcionales el RNF8 de manejo de errores: los errores de base de datos se capturan con excepciones propias, se registran internamente y al usuario solo se le muestra un mensaje claro, nunca trazas ni detalles técnicos.


## Fase 2 · Prompt 3 (Kiro spec) – requirements.md y design.md

Lee D0.md y crea la especificación del sistema del minimarket «La Esquina». Todo en español.

REQUIREMENTS.MD
- Criterios de aceptación en formato EARS en español: «CUANDO <evento> EL SISTEMA DEBERÁ <respuesta>» y «SI <condición> ENTONCES EL SISTEMA DEBERÁ <respuesta>».
- Agrupa los criterios por RF1, RF2, RF3, RF4 y RNF1 a RNF8, con los mismos identificadores de D0.md.
- Cada regla RN1 a RN5 debe aparecer en al menos un criterio; indica entre paréntesis la RN que cubre, por ejemplo (RN2).
- Al final, una tabla de trazabilidad RN → criterios.

DESIGN.MD
1. Arquitectura en capas Model → Repository → Service → Controller → views. Para cada capa, una tabla con qué hace y qué NO hace. Las reglas de negocio solo van en Services; los Repositories solo ejecutan SQL con PDO preparado.
2. Base de datos bodega_ventas (utf8mb4) con dos tablas:
   - producto: id_producto INT AUTO_INCREMENT PK, codigo VARCHAR(10) UNIQUE NOT NULL, nombre VARCHAR(100) NOT NULL, categoria VARCHAR(50) NOT NULL, precio_venta DECIMAL(10,2) NOT NULL CHECK (precio_venta >= 0), stock_actual INT NOT NULL CHECK (stock_actual >= 0), stock_minimo INT NOT NULL CHECK (stock_minimo >= 0).
   - venta: id_venta INT AUTO_INCREMENT PK, id_producto INT NOT NULL FK a producto, cantidad INT NOT NULL CHECK (cantidad > 0), precio_unitario DECIMAL(10,2) NOT NULL, total DECIMAL(10,2) NOT NULL, fecha_hora DATETIME NOT NULL.
   Muestra cada tabla con campo, tipo, clave y restricción, coherente con los datos de prueba (código P001, precios como 22.50).
3. Contratos de clases con firmas tipadas en PHP 8 (namespace App\): Producto, Venta, ProductoRepository, VentaRepository, ProductoService, VentaService, ProductoController, VentaController y excepciones propias (ValidacionException, StockInsuficienteException, ProductoNoEncontradoException, BaseDatosException).
4. Transacción de la venta: explica paso a paso el flujo de VentaService::registrarVenta(int $idProducto, int $cantidad): Venta con beginTransaction, SELECT ... FOR UPDATE del producto, validaciones, INSERT de la venta, UPDATE del stock, commit y rollBack en el catch.
5. Manejo de errores: registro inexistente devuelve null; error de base de datos lanza BaseDatosException; al usuario solo mensajes claros, nunca trazas.

No generes código de la aplicación ni ejecutes tareas.



## Fase 3 · Prompt 4 – schema.sql y seed.sql

Con base en design.md, genera database/schema.sql que cree la base bodega_ventas en utf8mb4 (utf8mb4_unicode_ci, InnoDB) con las tablas producto y venta: PRIMARY KEY, FOREIGN KEY de venta a producto, UNIQUE en codigo y CHECK para precio_venta >= 0, stock_actual >= 0, stock_minimo >= 0 y cantidad > 0. Genera database/seed.sql con los 8 productos de prueba del caso (P001 a P008) con sus precios, stock y mínimos exactos.