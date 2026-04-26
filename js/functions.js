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
    const filtro = document.getElementById("buscador").value;
    const formulario = document.getElementById("formOptions");
    // const input = document.getElementById("filtro");
    // input.value = filtro;
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "filtro";
    input.value = filtro;
    formulario.appendChild(input);
    document.getElementById("opcionPredeterminada").selected = true;  //FIXME
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
