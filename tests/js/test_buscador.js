QUnit.module('buscador.js tests', function() {
    QUnit.test('buscarTabla filtra correctamente las filas por contenido', function(assert) {
        // Obtenemos las filas de la tabla de pruebas (qunit-fixture)
        const tabla = document.getElementById('tablaDatos');
        const filas = tabla.getElementsByTagName('tr');
        
        // 1. Buscamos "PHP"
        buscarTabla('PHP');
        
        // filas[1] es "PHP Básico" (índice 1 de filas porque el encabezado es el 0)
        assert.equal(filas[1].style.display, '', 'La fila de PHP debe estar visible al buscar "PHP"');
        // filas[2] es "JavaScript Avanzado"
        assert.equal(filas[2].style.display, 'none', 'La fila de JS debe estar oculta al buscar "PHP"');

        // 2. Buscamos "JS"
        buscarTabla('JS');
        assert.equal(filas[1].style.display, 'none', 'La fila de PHP debe ocultarse al buscar "JS"');
        assert.equal(filas[2].style.display, '', 'La fila de JS debe mostrarse al buscar "JS"');

        // 3. Buscamos algo que no existe
        buscarTabla('Inexistente');
        assert.equal(filas[1].style.display, 'none', 'Fila 1 oculta para búsqueda inexistente');
        assert.equal(filas[2].style.display, 'none', 'Fila 2 oculta para búsqueda inexistente');
        
        // 4. Limpiamos búsqueda
        buscarTabla('');
        assert.equal(filas[1].style.display, '', 'Fila 1 vuelve a mostrarse con búsqueda vacía');
        assert.equal(filas[2].style.display, '', 'Fila 2 vuelve a mostrarse con búsqueda vacía');
    });
});
