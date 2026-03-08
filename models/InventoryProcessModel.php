<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: models/InventoryProcessModel.php
 * Proposito: Gestionar las operaciones de base de datos para el procesamiento de inventario. Incluye el vaciado de tablas temporales (Truncate), la inserción masiva mediante transacciones PDO y la recuperación de registros (incluyendo el ID) para visualizaciones en grid y reportes de exportación.
 * Version: 1.0.3 - Se corrige el error de "Undefined array key" incluyendo la columna 'id' en los SELECT y se asegura la persistencia de las fechas de auditoría en la tabla inventory_batches.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class InventoryProcessModel {

    /**
     * Vacía las 4 tablas temporales para iniciar un proceso de carga limpio.
     */
    public static function mdlVaciarTablas() {
        $conexion = Conexion::conectar();
        try {
            $conexion->exec("TRUNCATE TABLE tbl_shopify1");
            $conexion->exec("TRUNCATE TABLE tbl_ebay1");
            $conexion->exec("TRUNCATE TABLE tbl_shopify2");
            $conexion->exec("TRUNCATE TABLE tbl_ebay2");
            return "ok";
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }

    /**
     * Actualiza el registro maestro del lote con el rango de fechas de la auditoría.
     */
    public static function mdlActualizarFechasLote($id, $fecha_inicio, $fecha_fin) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("UPDATE inventory_batches SET fecha_inicio = :inicio, fecha_fin = :fin WHERE id = :id");
        $stmt->execute(['inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'id' => $id]);
        return "ok";
    }

    /**
     * Inserta masivamente los datos mapeados desde los CSV en la tabla correspondiente.
     */
    public static function mdlInsertarDatos($tabla, $datos) {
        if (empty($datos)) return "ok";

        $conexion = Conexion::conectar();
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare("INSERT INTO $tabla (sku, title, inventory) VALUES (:sku, :title, :inventory)");
            
            foreach ($datos as $fila) {
                $stmt->execute([
                    'sku'       => $fila['sku'], 
                    'title'     => $fila['title'], 
                    'inventory' => $fila['inventory']
                ]);
            }
            
            $conexion->commit();
            return "ok";
        } catch (PDOException $e) {
            $conexion->rollBack();
            return "error: " . $e->getMessage();
        }
    }

    /**
     * Obtiene los primeros 5 registros para la previsualización en los modales de la interfaz.
     * Incluye la columna 'id' para sincronización con los elementos de la vista.
     */
    public static function mdlObtenerVistaPrevia($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT id, sku, title, inventory FROM $tabla LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la totalidad de registros de una tabla para la generación de reportes CSV/Excel.
     */
    public static function mdlObtenerTodo($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT id, sku, title, inventory FROM $tabla");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna el conteo total de filas en una tabla de resultados.
     */
    public static function mdlContarRegistros($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM $tabla");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['total'] : 0;
    }
}