<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../views/View.php';

class ViewTest extends TestCase {
    
    private $view;

    protected function setUp(): void {
        $this->view = new View();
    }

    public function testInitialPhtmlIsEmpty() {
        // Al crear la vista, el contenido acumulado debe estar vacío
        $this->assertEquals("", (string)$this->view);
    }

    public function testHeaderGeneratesHtml() {
        $this->view->header();
        $html = (string)$this->view;
        
        // Verificamos que contenga etiquetas básicas de cabecera
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('<title>Gestor Brianda</title>', $html);
        $this->assertStringContainsString('js/functions.js', $html);
    }

    public function testMainColsGeneratesActions() {
        $this->view->mainCols();
        $html = (string)$this->view;
        
        // Verificamos que aparezcan los botones de acción
        $this->assertStringContainsString('Añadir fila', $html);
        $this->assertStringContainsString('Eliminar fila', $html);
        $this->assertStringContainsString('Guardar tabla', $html);
    }

    public function testFooterClosesBodyAndHtml() {
        $this->view->footer();
        $html = (string)$this->view;
        
        $this->assertStringContainsString('</body>', $html);
        $this->assertStringContainsString('</html>', $html);
    }
}
