-- Base de datos del sistema de ventas del minimarket «La Esquina»
CREATE DATABASE IF NOT EXISTS bodega_ventas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bodega_ventas;

DROP TABLE IF EXISTS venta;
DROP TABLE IF EXISTS producto;

CREATE TABLE producto (
  id_producto   INT AUTO_INCREMENT PRIMARY KEY,
  codigo        VARCHAR(10)   NOT NULL,
  nombre        VARCHAR(100)  NOT NULL,
  categoria     VARCHAR(50)   NOT NULL,
  precio_venta  DECIMAL(10,2) NOT NULL,
  stock_actual  INT           NOT NULL,
  stock_minimo  INT           NOT NULL,
  CONSTRAINT uq_producto_codigo   UNIQUE (codigo),
  CONSTRAINT chk_producto_precio  CHECK (precio_venta >= 0),
  CONSTRAINT chk_producto_stock   CHECK (stock_actual >= 0),
  CONSTRAINT chk_producto_minimo  CHECK (stock_minimo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venta (
  id_venta         INT AUTO_INCREMENT PRIMARY KEY,
  id_producto      INT           NOT NULL,
  cantidad         INT           NOT NULL,
  precio_unitario  DECIMAL(10,2) NOT NULL,
  total            DECIMAL(10,2) NOT NULL,
  fecha_hora       DATETIME      NOT NULL,
  CONSTRAINT fk_venta_producto FOREIGN KEY (id_producto)
    REFERENCES producto (id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_venta_cantidad CHECK (cantidad > 0),
  CONSTRAINT chk_venta_precio   CHECK (precio_unitario >= 0),
  CONSTRAINT chk_venta_total    CHECK (total >= 0),
  INDEX idx_venta_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;