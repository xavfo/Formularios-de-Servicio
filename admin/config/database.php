<?php
/**
 * Configuración de conexión a base de datos SQLite
 * 
 * Este archivo proporciona la conexión PDO a la base de datos SQLite
 * ubicada en la raíz del proyecto (database.db)
 */

// Ruta a la base de datos SQLite (relativa desde admin/)
define('DB_PATH', __DIR__ . '/../../database.db');

/**
 * Obtiene una conexión PDO a la base de datos SQLite
 * 
 * @return PDO Instancia de conexión PDO configurada
 * @throws PDOException Si no se puede conectar a la base de datos
 */
function obtenerConexion(): PDO {
    try {
        // Crear conexión PDO a SQLite
        $pdo = new PDO('sqlite:' . DB_PATH);
        
        // Configurar PDO para lanzar excepciones en caso de error
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Configurar PDO para retornar arrays asociativos por defecto
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Habilitar claves foráneas en SQLite
        $pdo->exec('PRAGMA foreign_keys = ON');
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a base de datos: " . $e->getMessage());
        throw new PDOException("No se pudo conectar a la base de datos");
    }
}

/**
 * Inicializa la tabla usuarios_admin si no existe
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @return bool True si la tabla se creó o ya existía
 */
function inicializarTablaUsuarios(PDO $pdo): bool {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS usuarios_admin (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            fecha_creacion TEXT DEFAULT CURRENT_TIMESTAMP,
            ultimo_acceso TEXT
        )";
        
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Error al crear tabla usuarios_admin: " . $e->getMessage());
        return false;
    }
}
