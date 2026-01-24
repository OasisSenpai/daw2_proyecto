<?php

class Model {
    public static function obtenerTabla($conexion, string $nombre): array {
        $query = "SELECT * FROM $nombre";

        $resultado = mysqli_query($conexion, $query);
        $tabla = mysqli_fetch_all($resultado);
        $n_columnas = mysqli_fetch_lengths($resultado);

        return [$tabla, $n_columnas];
    }


    public static function mostrarTabla(string $nombre = "cursos"): string {
        include_once "bbdd.php";
        $txt = "";
        
        [$tabla, $n_columnas] = Model::obtenerTabla($conexion, $nombre);

        $txt .= "<table id='tablaDatos' style='width: 100%;' border='1px'>\n";
        $txt .= Model::colocarEncabezadoTabla($conexion, $nombre, $n_columnas);
        mysqli_close($conexion);

        foreach ($tabla as $linea) {
            $txt .= "\t<tr>\n";
            foreach ($linea as $columna) {
                $txt .= "\t<td contenteditable='true'>$columna</td>\n";
            }
            $txt .= "\t</tr>\n";
        }
        $txt .= "</table>\n";
        $txt .= "<script>document.getElementById('tableName').innerText = '" . ucfirst($nombre) . "';</script>";

        return $txt;
    }


    public static function obtenerEncabezadoTabla($conexion, string $nombre): array {
        $query = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '$nombre'";

        $resultado = mysqli_query($conexion, $query);
        $columnas = mysqli_fetch_all($resultado);
        
        return $columnas;
    }


    public static function colocarEncabezadoTabla($conexion, string $nombre, int $n_columnas = 4): string {
        $txt = "";

        $columnas = Model::obtenerEncabezadoTabla($conexion, $nombre);
        
        $txt .= "\t<tr>\n";
        foreach ($columnas as $columna) {
            $txt .= "\t<th>$columna[0]</th>\n";
        }
        $txt .= "\t</tr>\n";

        return $txt;
    }

    
    public static function obtenerDatosParaCSV(string $nombreTabla): array {
        include_once "models/bbdd.php";

        if (empty($nombreTabla)) exit();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . htmlspecialchars($nombreTabla) . '.csv"');

        $tabla = Model::obtenerTabla($conexion, $nombreTabla)[0];
        $columnas = Model::obtenerEncabezadoTabla($conexion, $nombreTabla);
        mysqli_close($conexion);

        return [$tabla, $columnas];
    }
}