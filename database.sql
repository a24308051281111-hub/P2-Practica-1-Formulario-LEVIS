CREATE DATABASE IF NOT EXISTS levis_tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE levis_tienda;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  correo VARCHAR(120) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  descripcion VARCHAR(160) NOT NULL,
  precio DECIMAL(8,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  producto_id INT NOT NULL,
  talla VARCHAR(4) NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(8,2) NOT NULL,
  direccion VARCHAR(250) NOT NULL,
  notas VARCHAR(500),
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (producto_id) REFERENCES productos(id)
);

INSERT INTO productos (nombre, descripcion, precio) VALUES
('Jeans 501 Original', 'Corte recto clásico en mezclilla azul', 1299.00),
('Jeans 511 Slim', 'Corte ajustado con tela elástica', 1199.00),
('Playera Logo', 'Algodón suave con logotipo al frente', 499.00),
('Chamarra Trucker', 'Chamarra de mezclilla con botones metálicos', 1899.00),
('Camisa de Mezclilla', 'Manga larga con bolsillos frontales', 999.00),
('Sudadera con Capucha', 'Felpa interior y bolsillo canguro', 1099.00);
