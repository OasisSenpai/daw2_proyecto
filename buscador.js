function buscarTablaAntiguo(value) {
    let tabla = document.getElementById('tabla');
    let filas = tabla.getElementsByTagName('tr');
    let datosTabla = [];

    for (let i = 1; i < filas.length; i++) { // Empezar desde 1 para saltar la fila de encabezado
        let celdas = filas[i].getElementsByTagName('td');
        let filaDatos = [];
        for (let j = 0; j < celdas.length; j++) {
            filaDatos.push(celdas[j].textContent);
        }
        datosTabla.push(filaDatos);
    }
    // console.table(datosTabla);

    let filasFiltradas = datosTabla.filter(fila => fila[1].toLowerCase().includes(value.toLowerCase()));
    console.table(filasFiltradas);
}


function buscarTabla(value) {
    let tabla = document.getElementById('tabla');
    let filas = tabla.getElementsByTagName('tr');

    for (let i = 1; i < filas.length; i++) { // Empezar desde 1 para saltar la fila de encabezado
        let celdas = filas[i].getElementsByTagName('td');
        let encontrado = false;
        // Asumiendo que quieres buscar en la segunda columna (índice 1) como en buscarTablaAntiguo
        if (celdas.length > 1 && celdas[1].textContent.toLowerCase().includes(value.toLowerCase())) {
            encontrado = true;
        }

        if (encontrado) {
            filas[i].style.display = ''; // Mostrar la fila
        } else {
            filas[i].style.display = 'none'; // Ocultar la fila
        }
    }
}