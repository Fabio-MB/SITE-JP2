CREATE DATABASE IF NOT EXISTS JP2Catalog;

USE JP2Catalog;

CREATE TABLE IF NOT EXISTS Lonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    medidas VARCHAR(255) NOT NULL,
    haste ENUM('Com', 'Sem', 'N/A') NOT NULL DEFAULT 'N/A',
    referencias TEXT NOT NULL,
    imagem VARCHAR(255) DEFAULT 'uploads/sem-imagem.jpg',
    nota VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS Carros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano_inicio VARCHAR(4) DEFAULT NULL,
    ano_fim VARCHAR(4) DEFAULT NULL,
    observacao VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS LonaCarros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lona_id INT NOT NULL,
    carro_id INT NOT NULL,
    FOREIGN KEY (lona_id) REFERENCES Lonas(id) ON DELETE CASCADE,
    FOREIGN KEY (carro_id) REFERENCES Carros(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Add indexes for better performance
CREATE INDEX idx_lonas_codigo ON Lonas(codigo);
CREATE INDEX idx_carros_marca_modelo ON Carros(marca, modelo);
CREATE INDEX idx_lona_carros_lona_id ON LonaCarros(lona_id);
CREATE INDEX idx_lona_carros_carro_id ON LonaCarros(carro_id); 