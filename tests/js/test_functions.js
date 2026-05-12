QUnit.module('functions.js tests', function() {
    
    QUnit.test('contarCeldasTabla cuenta correctamente los encabezados (th)', function(assert) {
        const total = contarCeldasTabla();
        assert.equal(total, 3, 'Debe haber 3 encabezados según el fixture (ID, Nombre, Descripción)');
    });

    QUnit.test('annadirFila agrega una fila nueva y vacía a la tabla', function(assert) {
        const tbody = document.querySelector('#tablaDatos tbody');
        const numFilasInicial = tbody.querySelectorAll('tr').length;
        
        annadirFila();
        
        const numFilasFinal = tbody.querySelectorAll('tr').length;
        assert.equal(numFilasFinal, numFilasInicial + 1, 'El número de filas debe haber aumentado en 1');
        
        const nuevaFila = tbody.lastElementChild;
        assert.equal(nuevaFila.cells.length, 3, 'La nueva fila debe tener el mismo número de celdas que encabezados (3)');
        assert.equal(nuevaFila.cells[0].innerHTML, "ESCRIBE AQUÍ", 'La celda debe contener el texto por defecto');
        assert.equal(nuevaFila.cells[0].contentEditable, "true", 'Las nuevas celdas deben ser editables');
    });

    QUnit.test('seleccionarFila activa y desactiva la clase CSS de selección', function(assert) {
        const fila = document.querySelector('#tablaDatos tbody tr');
        
        // 1. Probar selección normal (sin Ctrl)
        seleccionarFila(fila, { ctrlKey: false });
        assert.ok(fila.classList.contains('fila-seleccionada'), 'La fila debe tener la clase "fila-seleccionada" tras el click');
        
        // 2. Probar deselección al volver a pulsar
        seleccionarFila(fila, { ctrlKey: false });
        assert.notOk(fila.classList.contains('fila-seleccionada'), 'La clase debe quitarse al volver a pulsar la misma fila');

        // 3. Probar que si hay una seleccionada y pulsamos OTRA sin Ctrl, la primera se deselecciona
        const fila1 = document.querySelectorAll('#tablaDatos tbody tr')[0];
        const fila2 = document.querySelectorAll('#tablaDatos tbody tr')[1];
        
        seleccionarFila(fila1, { ctrlKey: false });
        seleccionarFila(fila2, { ctrlKey: false });
        
        assert.notOk(fila1.classList.contains('fila-seleccionada'), 'La primera fila debe deseleccionarse al pulsar la segunda sin Ctrl');
        assert.ok(fila2.classList.contains('fila-seleccionada'), 'La segunda fila debe quedar seleccionada');
    });

    QUnit.test('eliminarFila quita del DOM las filas que tienen la clase de selección', function(assert) {
        const tbody = document.querySelector('#tablaDatos tbody');
        const totalInicial = tbody.querySelectorAll('tr').length;
        const fila = tbody.querySelector('tr');
        
        // Marcamos para eliminar
        fila.classList.add('fila-seleccionada');
        
        eliminarFila();
        
        const totalFinal = tbody.querySelectorAll('tr').length;
        assert.equal(totalFinal, totalInicial - 1, 'Debe haber una fila menos en la tabla');
        assert.notOk(tbody.contains(fila), 'La fila eliminada ya no debe estar en el cuerpo de la tabla');
    });
});
