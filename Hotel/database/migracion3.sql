-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL) con la base "hotel" seleccionada.
ALTER TABLE productos ADD COLUMN stock INT NOT NULL DEFAULT 0, ADD COLUMN stock_minimo INT NOT NULL DEFAULT 5;
ALTER TABLE consumos ADD COLUMN producto_id INT NULL AFTER estadia_id;
CREATE TABLE movimientos_stock(id INT AUTO_INCREMENT PRIMARY KEY, producto_id INT NOT NULL, cantidad INT NOT NULL, motivo ENUM('compra','venta','ajuste','devolucion') NOT NULL, estadia_id INT NULL, usuario VARCHAR(40) NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(producto_id) REFERENCES productos(id));
