CREATE DATABASE sistema_temperatura;
USE sistema_temperatura;

CREATE TABLE DISPOSITIVOS(
	ID_DEV INT PRIMARY KEY,
	UBICACION VARCHAR(15),
	AJUSTE_GMT INT,
	DELTA_SEGUNDOS INT,
	DELTA_TEMP REAL,
	ESTADO BOOLEAN
);

CREATE TABLE TEMPERATURAS(
	ID_TEMP INT PRIMARY KEY AUTO_INCREMENT,
	TEMP REAL,
	ID_DEV INT,
	FECHA DATE,
	HORA TIME,
	FOREIGN KEY (ID_DEV) REFERENCES DISPOSITIVOS(ID_DEV)
);

CREATE TABLE USUARIOS(
	ID_USER INT PRIMARY KEY AUTO_INCREMENT,
	USUARIO VARCHAR(50) NOT NULL UNIQUE,
	PASS VARCHAR(50) NOT NULL
);