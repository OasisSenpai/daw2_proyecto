<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../controllers/Controller.php';
require_once __DIR__ . '/../../views/View.php';

class ControllerTest extends TestCase {
    
    private $controller;

    protected function setUp(): void {
        $this->controller = new Controller();
    }

    public function testMainReturnsAViewObject() {
        // En un entorno de pruebas, Model::mostrarTabla podría fallar si intenta conectar a BD.
        // Como saltamos BD, esta prueba es para verificar el retorno del objeto.
        
        // Obtenemos el resultado de main()
        // Nota: main() llama a $view->table() que llama a Model::mostrarTabla().
        // Si Model::mostrarTabla() falla por BD, esta prueba dará error.
        // Pero como saltamos BD, lo ideal sería que Model tuviera una forma de no conectar.
        
        $view = $this->controller->main();
        $this->assertInstanceOf(View::class, $view);
    }
}
