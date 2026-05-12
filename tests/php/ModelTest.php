<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/Model.php';

class ModelTest extends TestCase {
    
    public function testModelClassExists() {
        $this->assertTrue(class_exists('Model'));
    }

    /**
     * Prueba la obtención de datos de una tabla.
     */
    public function testObtenerTabla() {
        require __DIR__ . '/../../models/bbdd.php';
        $this->assertNotFalse($conexion, "Error de conexión a la base de datos de test");
        
        [$tabla, $n_columnas] = Model::obtenerTabla($conexion, 'cursos');
        
        $this->assertIsArray($tabla);
        $this->assertNotFalse($n_columnas);
        
        mysqli_close($conexion);
    }

    /**
     * Prueba la generación del encabezado HTML.
     */
    public function testColocarEncabezadoTabla() {
        require __DIR__ . '/../../models/bbdd.php';
        $this->assertNotFalse($conexion, "Error de conexión a la base de datos de test");
        
        $html = Model::colocarEncabezadoTabla($conexion, 'cursos');
        
        $this->assertStringContainsString('<tr>', $html);
        $this->assertStringContainsString('<th>', $html);
        
        mysqli_close($conexion);
    }

    /**
     * PRUEBA DE SINCRONIZACIÓN COMPLETA (CRUD)
     * Esta prueba verifica que guardarTabla inserte, actualice y borre correctamente.
     */
    public function testSincronizacionCompletaGuardarTabla() {
        $nombreTabla = 'cursos';
        
        // 1. LIMPIEZA INICIAL: Aseguramos que no hay datos de test previos
        require __DIR__ . '/../../models/bbdd.php';
        mysqli_query($conexion, "DELETE FROM $nombreTabla WHERE id IN ('998', '999')");
        mysqli_close($conexion);

        // 2. PRUEBA DE INSERCIÓN
        $datosInsert = [
            ['id', 'nombre', 'descripcion'], // Encabezado
            ['999', 'Original', 'Desc']      // Fila nueva
        ];
        Model::guardarTabla($nombreTabla, $datosInsert);

        require __DIR__ . '/../../models/bbdd.php';
        $res = mysqli_query($conexion, "SELECT nombre FROM $nombreTabla WHERE id = '999'");
        $fila = mysqli_fetch_assoc($res);
        $this->assertEquals('Original', $fila['nombre'] ?? $fila['Nombre'] ?? '');
        mysqli_close($conexion);

        // 3. PRUEBA DE ACTUALIZACIÓN (UPDATE)
        // Enviamos el mismo ID pero con distinto nombre. 
        // Tu código detectará el error 1062 y ejecutará el UPDATE.
        $datosUpdate = [
            ['id', 'nombre', 'descripcion'],
            ['999', 'Modificado', 'Desc']
        ];
        Model::guardarTabla($nombreTabla, $datosUpdate);

        require __DIR__ . '/../../models/bbdd.php';
        $res = mysqli_query($conexion, "SELECT nombre FROM $nombreTabla WHERE id = '999'");
        $fila = mysqli_fetch_assoc($res);
        $this->assertEquals('Modificado', $fila['nombre'] ?? $fila['Nombre'] ?? '');
        mysqli_close($conexion);

        // 4. PRUEBA DE BORRADO (DELETE)
        // Enviamos una tabla VACÍA (solo el encabezado).
        // Tu código debería ver que el ID '999' ya no está en el array y borrarlo de la BD.
        $datosVacios = [
            ['id', 'nombre', 'descripcion']
        ];
        Model::guardarTabla($nombreTabla, $datosVacios);

        require __DIR__ . '/../../models/bbdd.php';
        $res = mysqli_query($conexion, "SELECT * FROM $nombreTabla WHERE id = '999'");
        $this->assertEmpty(mysqli_fetch_assoc($res), 'La fila debería haber sido borrada por la sincronización');
        mysqli_close($conexion);
    }

    /**
     * Prueba el manejo de duplicados explícito (branch 1062)
     */
    public function testManejoDuplicados() {
        $nombreTabla = 'cursos';
        
        // Insertamos manualmente una fila
        require __DIR__ . '/../../models/bbdd.php';
        mysqli_query($conexion, "INSERT INTO $nombreTabla (id, nombre, descripcion) VALUES ('998', 'Test', 'Test')");
        mysqli_close($conexion);

        // Intentamos guardar la misma fila con cambios
        $datos = [
            ['id', 'nombre', 'descripcion'],
            ['998', 'Cambiado', 'Test']
        ];
        
        $resultado = Model::guardarTabla($nombreTabla, $datos);
        $this->assertStringContainsString('guardada correctamente', $resultado['mensaje']);

        require __DIR__ . '/../../models/bbdd.php';
        $res = mysqli_query($conexion, "SELECT nombre FROM $nombreTabla WHERE id = '998'");
        $fila = mysqli_fetch_assoc($res);
        $this->assertEquals('Cambiado', $fila['nombre'] ?? $fila['Nombre'] ?? '');
        
        // Limpieza
        mysqli_query($conexion, "DELETE FROM $nombreTabla WHERE id = '998'");
        mysqli_close($conexion);
    }
}
