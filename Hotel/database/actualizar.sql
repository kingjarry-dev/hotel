-- Actualizacion de estructura. Se puede ejecutar muchas veces sin problema.
ALTER TABLE habitaciones MODIFY estado ENUM('libre','ocupada','limpieza','mantenimiento') DEFAULT 'libre';
ALTER TABLE estadias ADD COLUMN IF NOT EXISTS salida_prevista DATE NULL AFTER noches;
UPDATE estadias SET salida_prevista=DATE_ADD(DATE(entrada),INTERVAL noches DAY) WHERE salida_prevista IS NULL;
ALTER TABLE estadias ADD COLUMN IF NOT EXISTS dni VARCHAR(20) NULL AFTER huesped, ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) NULL AFTER dni;
CREATE TABLE IF NOT EXISTS pagos(id INT AUTO_INCREMENT PRIMARY KEY, estadia_id INT NOT NULL, monto DECIMAL(10,2) NOT NULL, metodo ENUM('Efectivo','Yape','Plin','Tarjeta') NOT NULL, tipo ENUM('adelanto','saldo') NOT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(estadia_id) REFERENCES estadias(id));
INSERT INTO pagos(estadia_id,monto,metodo,tipo,fecha) SELECT e.id,e.total_hab+e.total_prod,'Efectivo','saldo',COALESCE(e.salida,NOW()) FROM estadias e WHERE e.estado='cerrada' AND NOT EXISTS (SELECT 1 FROM pagos p WHERE p.estadia_id=e.id);
ALTER TABLE productos ADD COLUMN IF NOT EXISTS stock INT NOT NULL DEFAULT 0, ADD COLUMN IF NOT EXISTS stock_minimo INT NOT NULL DEFAULT 5;
ALTER TABLE consumos ADD COLUMN IF NOT EXISTS producto_id INT NULL AFTER estadia_id;
CREATE TABLE IF NOT EXISTS movimientos_stock(id INT AUTO_INCREMENT PRIMARY KEY, producto_id INT NOT NULL, cantidad INT NOT NULL, motivo ENUM('compra','venta','ajuste','devolucion') NOT NULL, estadia_id INT NULL, usuario VARCHAR(40) NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(producto_id) REFERENCES productos(id));