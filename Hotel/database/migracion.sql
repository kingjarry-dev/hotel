-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL) con la base "hotel" ya creada.
USE hotel;
ALTER TABLE estadias ADD COLUMN salida_prevista DATE NULL AFTER noches;
UPDATE estadias SET salida_prevista=DATE_ADD(DATE(entrada),INTERVAL noches DAY);
CREATE TABLE pagos(id INT AUTO_INCREMENT PRIMARY KEY, estadia_id INT NOT NULL, monto DECIMAL(10,2) NOT NULL, metodo ENUM('Efectivo','Yape','Plin','Tarjeta') NOT NULL, tipo ENUM('adelanto','saldo') NOT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(estadia_id) REFERENCES estadias(id));
INSERT INTO pagos(estadia_id,monto,metodo,tipo,fecha) SELECT id,total_hab+total_prod,'Efectivo','saldo',salida FROM estadias WHERE estado='cerrada';
