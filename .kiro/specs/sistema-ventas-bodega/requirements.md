# Requirements Document

## Introduction

Este documento especifica los requisitos del sistema web interno del minimarket «La Esquina», ubicado en San Vicente de Cañete. El sistema reemplaza el control manual en cuaderno de las ventas y del stock por un registro digital confiable. Permite al Cajero registrar productos, listar productos destacando los que están por debajo del stock mínimo, registrar ventas calculando el total y descontando el stock, y consultar las ventas del día con su total general. El Dueño revisa el stock y las ventas sin operar la caja. El sistema se desarrolla en PHP 8 orientado a objetos con base de datos MySQL, sin inicio de sesión ni roles, y se ejecuta en la red interna del local.

## Glossary

- **Sistema**: Aplicación web interna del minimarket «La Esquina» que gestiona productos y ventas.
- **Cajero**: Usuario que registra productos y ventas en el Sistema.
- **Dueño**: Usuario que revisa el stock y las ventas del día sin operar la caja.
- **Producto**: Artículo vendido en el minimarket, identificado por código, nombre, categoría, precio de venta, stock actual y stock mínimo.
- **Código**: Identificador único de un Producto, no repetible.
- **Stock actual**: Cantidad de unidades disponibles de un Producto en el momento de la consulta.
- **Stock mínimo**: Cantidad de referencia por debajo de la cual un Producto se destaca para reponer.
- **Venta**: Registro de la venta de un único Producto con cantidad, precio unitario, total, fecha y hora.
- **Total**: Resultado de multiplicar el precio de venta del Producto por la cantidad vendida.
- **Total vendido del día**: Suma de los Totales de todas las Ventas registradas en la fecha actual.
- **Token CSRF**: Valor oculto incluido en los formularios y verificado por el servidor para evitar solicitudes falsificadas.
- **PDO**: Extensión de PHP para acceder a la base de datos con sentencias preparadas.
- **Transacción**: Grupo de operaciones de base de datos que se ejecutan de forma atómica; si una falla, todas se revierten.
- **Excepción propia**: Clase de error definida en el Sistema para representar errores de validación, de stock, de registro no encontrado o de base de datos.

## Requirements

### Requirement 1: RF1 - Registrar productos

**User Story:** Como cajero, quiero registrar productos con su código, nombre, categoría, precio de venta, stock actual y stock mínimo, para mantener un inventario confiable de la mercadería que ingresa.

#### Acceptance Criteria

1. **RF1-1** CUANDO el cajero registra un producto con código, nombre y categoría no vacíos, precio de venta mayor o igual a cero, y stock actual y stock mínimo como enteros mayores o iguales a cero, EL SISTEMA DEBERÁ guardar el Producto con código, nombre, categoría, precio de venta, stock actual y stock mínimo en la base de datos.
2. **RF1-2** SI el código del producto ya existe en la base de datos, ENTONCES EL SISTEMA DEBERÁ rechazar el registro sin guardar el producto e informar al cajero que el código está duplicado. (RN4)
3. **RF1-3** SI el precio de venta ingresado es un valor negativo, ENTONCES EL SISTEMA DEBERÁ rechazar el registro sin guardar el producto e informar al cajero que el precio no puede ser negativo. (RN3)
4. **RF1-4** CUANDO el cajero envía el formulario de registro de producto, EL SISTEMA DEBERÁ verificar el token CSRF antes de procesar la solicitud.
5. **RF1-5** SI un campo obligatorio (código, nombre, categoría, precio de venta, stock actual o stock mínimo) está vacío, ENTONCES EL SISTEMA DEBERÁ rechazar el registro e informar al cajero que todos los campos son obligatorios.
6. **RF1-6** SI el token CSRF del formulario de registro de producto es inválido o está ausente, ENTONCES EL SISTEMA DEBERÁ rechazar la solicitud sin procesar el registro e informar al cajero que la solicitud no es válida.
7. **RF1-7** SI el precio de venta, stock actual o stock mínimo no son valores numéricos, ENTONCES EL SISTEMA DEBERÁ rechazar el registro e informar al cajero que los valores deben ser numéricos.
8. **RF1-8** SI el stock actual o el stock mínimo ingresado es un valor negativo, ENTONCES EL SISTEMA DEBERÁ rechazar el registro sin guardar el producto e informar al cajero que el stock no puede ser negativo.

### Requirement 2: RF2 - Listar productos

**User Story:** Como cajero y como dueño, quiero ver la lista de productos destacando los que están en su stock mínimo o por debajo, para saber qué productos se deben reponer pronto.

#### Acceptance Criteria

1. **RF2-1** CUANDO el cajero o el dueño solicita la lista de productos, EL SISTEMA DEBERÁ mostrar todos los Productos registrados con código, nombre, categoría, precio de venta, stock actual y stock mínimo.
2. **RF2-2** CUANDO se muestra la lista de productos y un Producto tiene stock actual menor o igual al stock mínimo, EL SISTEMA DEBERÁ aplicar un estilo visual diferenciado y observable a ese Producto en la lista que lo distinga de los Productos cuyo stock actual está por encima del stock mínimo.
3. **RF2-3** CUANDO se muestra la lista de productos, EL SISTEMA DEBERÁ imprimir toda la información con htmlspecialchars, de modo que los datos que contengan etiquetas HTML o scripts se muestren como texto literal en el navegador y no se interpreten como código ejecutable.
4. **RF2-4** SI no existen Productos registrados al solicitarse la lista de productos, ENTONCES EL SISTEMA DEBERÁ mostrar un mensaje indicando que no hay productos registrados.

### Requirement 3: RF3 - Registrar venta

**User Story:** Como cajero, quiero registrar la venta de un producto eligiendo el producto y la cantidad, para que el Sistema calcule el total, guarde la fecha y hora y descuente el stock en una sola operación.

#### Acceptance Criteria

1. **RF3-1** CUANDO el cajero selecciona un Producto e indica una cantidad entera mayor que cero y menor o igual al stock actual del Producto, EL SISTEMA DEBERÁ registrar la Venta guardando como precio unitario el precio de venta vigente del Producto en ese momento, calcular el Total como precio unitario multiplicado por la cantidad, registrar la fecha y hora actuales y descontar la cantidad vendida del stock actual del Producto. (RN5)
2. **RF3-2** SI la cantidad ingresada no es un número entero mayor que cero, ENTONCES EL SISTEMA DEBERÁ rechazar la Venta e informar al cajero que la cantidad debe ser un número entero mayor que cero. (RN1)
3. **RF3-3** SI la cantidad solicitada es mayor al stock actual del Producto, ENTONCES EL SISTEMA DEBERÁ rechazar la Venta e informar al cajero que no hay stock suficiente. (RN2)
4. **RF3-4** SI el registro de la Venta o el descuento del stock fallan durante la operación, ENTONCES EL SISTEMA DEBERÁ revertir toda la operación de modo que ni la Venta se registre ni el stock se descuente. (RN5)
5. **RF3-5** CUANDO el cajero envía el formulario de registro de Venta, EL SISTEMA DEBERÁ verificar el token CSRF antes de procesar la solicitud.
6. **RF3-6** CUANDO se registra una Venta, EL SISTEMA DEBERÁ ejecutar el registro de la Venta y el descuento del stock dentro de una transacción de base de datos. (RN5)
7. **RF3-7** SI el token CSRF del formulario de registro de Venta es inválido o está ausente, ENTONCES EL SISTEMA DEBERÁ rechazar la solicitud sin procesar la Venta ni descontar el stock e informar al cajero que la solicitud no es válida.
8. **RF3-8** SI el Producto seleccionado no existe, ENTONCES EL SISTEMA DEBERÁ rechazar la Venta e informar al cajero que el Producto no existe, sin registrar la Venta ni descontar el stock.

### Requirement 4: RF4 - Listar ventas del día

**User Story:** Como cajero y como dueño, quiero consultar las ventas registradas en la fecha actual con su total general, para saber cuánto vendió el negocio al cerrar el día.

#### Acceptance Criteria

1. **RF4-1** CUANDO el cajero o el dueño consulta las ventas del día, EL SISTEMA DEBERÁ mostrar todas las Ventas registradas en la fecha actual desde las 00:00 hasta las 23:59, ordenadas de forma ascendente según la fecha y hora de la Venta, con el Producto, cantidad, precio unitario, Total y fecha y hora de cada Venta.
2. **RF4-2** CUANDO se muestran las ventas del día, EL SISTEMA DEBERÁ mostrar el total vendido del día calculado como la suma de los Totales de todas las Ventas del día.
3. **RF4-3** CUANDO se muestra la lista de ventas, EL SISTEMA DEBERÁ imprimir toda la información con htmlspecialchars, de modo que los datos que contengan etiquetas HTML o scripts se muestren como texto literal en el navegador y no se interpreten como código ejecutable.
4. **RF4-4** SI no existen Ventas registradas en la fecha actual, ENTONCES EL SISTEMA DEBERÁ mostrar un mensaje indicando que no hay ventas y un total vendido igual a 0.

### Requirement 5: RNF1 - Tecnología PHP 8 orientado a objetos

**User Story:** Como desarrollador, quiero que el Sistema se desarrolle en PHP 8 orientado a objetos, para que el código sea mantenible y ordenado.

#### Acceptance Criteria

1. **RNF1-1** CUANDO se desarrolla el Sistema, EL SISTEMA DEBERÁ estar implementado en PHP 8 orientado a objetos, con su lógica organizada en clases que contengan propiedades y métodos.
2. **RNF1-2** CUANDO se desarrolla el Sistema, EL SISTEMA DEBERÁ declarar las propiedades de cada clase con visibilidad restringida, de modo que ningún atributo de clase sea público y el acceso a ellos se realice únicamente mediante métodos.
3. **RNF1-3** CUANDO se desarrolla el Sistema, EL SISTEMA DEBERÁ distribuir su lógica de modo que cada clase se centre en una única responsabilidad funcional, sin combinar en una misma clase lógicas correspondientes a funcionalidades distintas.

### Requirement 6: RNF2 - Base de datos MySQL

**User Story:** Como desarrollador, quiero que los datos se guarden en MySQL con tablas bien definidas, para mantener la integridad de la información de productos y ventas.

#### Acceptance Criteria

1. **RNF2-1** CUANDO el Sistema almacena los datos de Productos y Ventas, EL SISTEMA DEBERÁ guardarlos en una base de datos MySQL con las tablas de productos y ventas definidas con tipos de datos adecuados a la naturaleza de cada atributo (texto, numérico entero, numérico decimal, fecha y hora).
2. **RNF2-2** CUANDO se define la relación entre las tablas de productos y ventas, EL SISTEMA DEBERÁ asociar cada Venta al Producto vendido mediante una relación con integridad referencial, de modo que cada Venta corresponda a un Producto existente.

### Requirement 7: RNF3 - Integridad de datos con PDO y sentencias preparadas

**User Story:** Como desarrollador, quiero que toda consulta use PDO con sentencias preparadas, para evitar la inyección SQL.

#### Acceptance Criteria

1. **RNF3-1** CUANDO se ejecuta una consulta a la base de datos, EL SISTEMA DEBERÁ usar PDO con sentencias preparadas enlazando los valores como parámetros, sin concatenar los valores directamente en la cadena SQL.
2. **RNF3-2** SI se recibe un valor que contiene sintaxis SQL maliciosa en un campo de formulario, ENTONCES EL SISTEMA DEBERÁ tratarlo como texto literal y no ejecutarlo como parte de la consulta SQL.

### Requirement 8: RNF4 - Seguridad con token CSRF

**User Story:** Como desarrollador, quiero que todos los formularios incluyan un token CSRF, para evitar que alguien envíe datos falsos desde otra página.

#### Acceptance Criteria

1. **RNF4-1** CUANDO se muestra un formulario al usuario, EL SISTEMA DEBERÁ incluir un token CSRF como valor oculto.
2. **RNF4-2** SI el token CSRF recibido no está presente o no coincide con el token esperado, ENTONCES EL SISTEMA DEBERÁ no procesar el envío del formulario, de modo que no se registre ningún producto, no se registre ninguna venta ni se modifique el stock, e informar al usuario que la solicitud no es válida.

### Requirement 9: RNF5 - Seguridad con htmlspecialchars

**User Story:** Como desarrollador, quiero que toda la información mostrada se imprima con htmlspecialchars, para evitar que se ejecute código malicioso en el navegador.

#### Acceptance Criteria

1. **RNF5-1** CUANDO se muestran en la página datos de un Producto o de una Venta, EL SISTEMA DEBERÁ imprimir el contenido con htmlspecialchars, de modo que los caracteres especiales HTML se conviertan en entidades y se muestren como texto literal.
2. **RNF5-2** SI un dato mostrado contiene etiquetas HTML o scripts, ENTONCES EL SISTEMA DEBERÁ mostrarlo como texto literal en el navegador, sin interpretarlo ni ejecutarlo como código.

### Requirement 10: RNF6 - Credenciales en archivo .env

**User Story:** Como desarrollador, quiero que las credenciales de la base de datos se guarden en un archivo .env no versionado, para que nadie con acceso al código pueda verlas.

#### Acceptance Criteria

1. **RNF6-1** CUANDO el Sistema accede a la base de datos, EL SISTEMA DEBERÁ leer las credenciales de usuario, contraseña, nombre de la base y host desde un archivo .env excluido del repositorio de control de versiones y no desde credenciales codificadas directamente en el código fuente.
2. **RNF6-2** SI el archivo .env no existe o no contiene todas las credenciales de usuario, contraseña, nombre de la base y host, ENTONCES EL SISTEMA DEBERÁ rechazar la conexión a la base de datos e informar el error sin exponer credenciales ni detalles técnicos.
3. **RNF6-3** CUANDO se versiona el repositorio de control de versiones, EL SISTEMA DEBERÁ incluir un archivo .env.example con valores de ejemplo y no reales para las credenciales de usuario, contraseña, nombre de la base y host.

### Requirement 11: RNF7 - Integridad de datos con transacción

**User Story:** Como desarrollador, quiero que el registro de la venta y el descuento del stock se ejecuten en una transacción, para que si una operación falla todo se revierta.

#### Acceptance Criteria

1. **RNF7-1** CUANDO se registra una Venta, EL SISTEMA DEBERÁ ejecutar el registro de la Venta y el descuento del stock dentro de una transacción de base de datos. (RN5)
2. **RNF7-2** SI alguna operación dentro de la transacción falla, ENTONCES EL SISTEMA DEBERÁ revertir todas las operaciones de modo que ni la Venta quede registrada ni el stock quede descontado. (RN5)

### Requirement 12: RNF8 - Manejo de errores

**User Story:** Como desarrollador, quiero que los errores de base de datos se capturen con excepciones propias y al usuario solo se le muestre un mensaje claro, para no exponer trazas ni detalles técnicos.

#### Acceptance Criteria

1. **RNF8-1** SI ocurre un error de base de datos, ENTONCES EL SISTEMA DEBERÁ capturar el error lanzando una Excepción propia en lugar de la excepción original de PDO.
2. **RNF8-2** SI ocurre un error de base de datos, ENTONCES EL SISTEMA DEBERÁ conservar la información detallada del error de forma interna sin mostrarla al usuario en la página de respuesta.
3. **RNF8-3** SI ocurre un error de base de datos, ENTONCES EL SISTEMA DEBERÁ mostrar al usuario un mensaje que indique que la operación no pudo completarse, sin incluir trazas de pila, sentencias SQL, rutas de archivos ni nombres de clases de excepción.

## Trazabilidad de reglas de negocio

| Regla de negocio | Descripción | Criterios de aceptación que la cubren |
|------------------|-------------|---------------------------------------|
| RN1 | La cantidad vendida debe ser mayor que cero | RF3-2 |
| RN2 | No se puede vender más de lo disponible en stock | RF3-3 |
| RN3 | El precio de venta no puede ser negativo | RF1-3 |
| RN4 | El código del producto no se puede repetir | RF1-2 |
| RN5 | La venta y el descuento del stock van juntos | RF3-1, RF3-4, RF3-6, RNF7-1, RNF7-2 |
