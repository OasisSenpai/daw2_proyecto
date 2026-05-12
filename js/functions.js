function annadirFila() {
    const tabla = document.getElementById("tablaDatos");
    const fila = document.createElement("tr");
    for (let i=0;i<contarCeldasTabla();i++) {
        const celda = document.createElement("td");
        celda.contentEditable = "true";
        celda.innerHTML = "ESCRIBE AQUÍ";
        fila.appendChild(celda);
    }
    tabla.querySelector("tbody").appendChild(fila);
}


function contarCeldasTabla() {
    const tabla = document.getElementById("tablaDatos");
    if (!tabla) {
        console.error("La tabla con ID 'tablaDatos' no fue encontrada.");
        return;
    }
    const primeraFila = tabla.querySelector("tr");
    if (!primeraFila) {
        console.warn("La tabla no contiene filas.");
        return 0;
    }
    const contadorCeldas = primeraFila.querySelectorAll("th").length;
    // console.log("Número total de celdas en la primera fila:", contadorCeldas);
    return contadorCeldas;
}


function prepararEnvioGeneradorCSV() {
    const formulario = document.createElement("form");
    const generateCSV = document.createElement("input");
    const tabla = document.createElement("input");
    const filtro = document.createElement("input");

    formulario.target = "_blank";

    generateCSV.type = "hidden";
    generateCSV.name = "generateCSV";
    generateCSV.value = "generateCSV";

    tabla.type = "hidden";
    tabla.name = "tabla";
    const urlParams = new URLSearchParams(window.location.search);
    tabla.value = urlParams.get('tabla');  //document.getElementById("tabla").value;

    filtro.type = "hidden";
    filtro.name = "filtro";
    filtro.value = document.getElementById("buscador").value;

    formulario.appendChild(generateCSV);
    formulario.appendChild(tabla);
    formulario.appendChild(filtro);

    document.body.appendChild(formulario);

    document.getElementById("generateCSV").selected = false;
    document.getElementById("opcionPredeterminada").selected = true;

    formulario.submit();
}


async function guardarTabla() {
    const tabla = document.getElementById("tablaDatos");
    const datos = [];
    tabla.querySelectorAll("tr").forEach(fila => {
        const registro = [];
        fila.querySelectorAll("td").forEach(celda => {
            registro.push(celda.innerHTML);
        });
        datos.push(registro);
    });

    const response = await fetch("index.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            tabla: document.getElementById("tableName").innerText,
            datos: datos
        })
    });

    const resultado = await response.json();
    alert(resultado.mensaje);
}


function seleccionarFila(fila, event) {
    // const filaSeleccionada = document.querySelector(".fila-seleccionada");
    const filasSeleccionadas = document.querySelectorAll(".fila-seleccionada");

    // if (filaSeleccionada && filaSeleccionada !== fila && !event.ctrlKey) {
    if (!event.ctrlKey) {
        // filaSeleccionada.classList.remove("fila-seleccionada");
        filasSeleccionadas.forEach(element => {
            element.classList.remove("fila-seleccionada");
        });
    }

    fila.classList.toggle("fila-seleccionada");
}


function eliminarFila() {
    const filasSeleccionadas = document.querySelectorAll(".fila-seleccionada");

    filasSeleccionadas.forEach(fila => {
        console.log(fila);
        fila.remove();
    });
}
