<?php
/**
 * Modulo: Core / Configuración
 * Archivo: /config/db.php
 * Proposito: Establecer la conexión PDO con la base de datos MySQL de XAMPP.
 * Version: 0.0.1 - Conexión básica a db_altair.
 */

class Conexion {
    public static function conectar() {
        try {
            // Configuración por defecto de XAMPP (root, sin contraseña)
            $link = new PDO("mysql:host=localhost;dbname=db_altair", "root", "");
            
            // Forzar el uso de caracteres UTF-8
            $link->exec("set names utf8mb4");
            
            // Habilitar excepciones de errores para depuración
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $link;
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
?>