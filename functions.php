<?php

function mostrarTabla(string $nombre = "cursos"): string {
    include_once "bbdd.php";
    $txt = "";
    $query = "SELECT * FROM $nombre";
    $resultado = mysqli_query($conexion, $query);
    // mysqli_close($conexion);
    $tabla = mysqli_fetch_all($resultado);
    $n_columnas = mysqli_fetch_lengths($resultado);

    $txt .= "<table id='tabla' style='width: 100%;' border='1px'>\n";
    $txt .= colocarEncabezadoTabla($conexion, $nombre, $n_columnas);
    foreach ($tabla as $linea) {
        $txt .= "\t<tr>\n";
        foreach ($linea as $columna) {
            $txt .= "\t<td contenteditable='true'>$columna</td>\n";
        }
        $txt .= "\t</tr>\n";
    }
    $txt .= "</table>\n";
    mysqli_close($conexion);
    echo "<script>document.getElementById('tableName').innerText = '" . ucfirst($nombre) . "';</script>";
    return $txt;
}


function colocarEncabezadoTabla($conexion, string $nombre, int $n_columnas = 4): string {
    include_once "bbdd.php";
    $txt = "";
    $query = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '$nombre'";
    $resultado = mysqli_query($conexion, $query);
    $columnas = mysqli_fetch_all($resultado);
    
    $txt .= "\t<tr>\n";
    foreach ($columnas as $columna) {
        $txt .= "\t<th>$columna[0]</th>\n";
    }
    $txt .= "\t</tr>\n";
    return $txt;
}