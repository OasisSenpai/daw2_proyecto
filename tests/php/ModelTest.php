<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/Model.php';

class ModelTest extends TestCase {
    
    public function testModelClassExists() {
        $this->assertTrue(class_exists('Model'));
    }

    /**
     * Prueba la obtención de datos de una tabla.
     * Requiere que la base de datos de test tenga la tabla 'cursos'.
     */
    public function testObtenerTabla() {
        // Usamos require para asegurar que la conexión se cree en este ámbito
        require __DIR__ . '/../../models/bbdd.php';
        
        // Verificamos que la conexión sea válida antes de seguir
        $this->assertNotFalse($conexion, "Error de conexión a la base de datos de test: " . mysqli_connect_error());
        
        // Intentamos obtener la tabla 'cursos'
        [$tabla, $n_columnas] = Model::obtenerTabla($conexion, 'cursos');
        
        $this->assertIsArray($tabla, 'La tabla debe ser un array (puede estar vacío)');
        $this->assertNotFalse($n_columnas, 'El recuento de columnas no debe ser false (esto indica error en la consulta)');
        $this->assertIsArray($n_columnas, 'El recuento de columnas debe ser un array');
        
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
     * Prueba el guardado de datos (Insert/Update/Delete).
     */
    public function testGuardarTabla() {
        $nombreTabla = 'cursos';
        
        // Preparamos datos ficticios para guardar
        $datosParaGuardar = [
            ['id', 'nombre', 'descripcion'], // Encabezado
            ['999', 'Curso Test', 'Descripción de test'] // Fila de prueba
        ];
        
        $resultado = Model::guardarTabla($nombreTabla, $datosParaGuardar);
        
        $this->assertArrayHasKey('mensaje', $resultado);
        $this->assertStringContainsString('guardada correctamente', $resultado['mensaje']);
        
        // Verificamos que realmente se guardó
        require __DIR__ . '/../../models/bbdd.php';
        $query = "SELECT * FROM $nombreTabla WHERE id = '999'";
        $res = mysqli_query($conexion, $query);
        $fila = mysqli_fetch_assoc($res);
        
        $this->assertNotNull($fila, 'La fila de prueba debería existir en la base de datos de test');
        // Usamos mb_strtolower para comparar sin importar mayúsculas
        $nombreEncontrado = $fila['nombre'] ?? $fila['Nombre'] ?? '';
        $this->assertEquals('Curso Test', $nombreEncontrado);
        
        // Limpieza: Borramos la fila de prueba
        mysqli_query($conexion, "DELETE FROM $nombreTabla WHERE id = '999'");
        mysqli_close($conexion);
    }
}
