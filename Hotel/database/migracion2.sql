-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL) con la base "hotel" seleccionada.
ALTER TABLE habitaciones MODIFY estado ENUM('libre','ocupada','limpieza','mantenimiento') DEFAULT 'libre';
ALTER TABLE estadias ADD COLUMN dni VARCHAR(20) NULL AFTER huesped, ADD COLUMN telefono VARCHAR(20) NULL AFTER dni;
