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


    public static function guardarTabla(string $nombreTabla, array $datos): array {  // TODO está sin probar
        include_once "bbdd.php";
        
        $nombreTabla = strtolower($nombreTabla);
        $columnas = Model::obtenerEncabezadoTabla($conexion, $nombreTabla);
        $nombresColumnas = array_map(function($col) { return $col[0]; }, $columnas);
        $nombresColumnasStr = implode(", ", $nombresColumnas);
        
        foreach (array_slice($datos, 1) as $fila) {
            $filaStr = implode("\", \"", $fila);
            $sentenciaInsert = "INSERT INTO $nombreTabla ($nombresColumnasStr)
                                VALUES (\"$filaStr\")";
            // return ["mensaje" => $sentenciaInsert];

            $sentenciaUpdate = "UPDATE $nombreTabla SET ";
            $sets = [];
            foreach ($nombresColumnas as $i => $col) {
                $valor = mysqli_real_escape_string($conexion, $fila[$i]);
                $sets[] = "$col = '$valor'";
            }
            $sentenciaUpdate .= implode(", ", $sets) . " WHERE " . $nombresColumnas[0] . " = \"" . $fila[0] . "\"";
            // return ["mensaje" => $sentenciaUpdate];

            try {
                mysqli_query($conexion, $sentenciaInsert);
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {  // Duplicate entry error
                    mysqli_query($conexion, $sentenciaUpdate);
                } else if ($e->getCode() == 1406) {  // Data too long for column
                    mysqli_close($conexion);
                    return ["mensaje" => "La tabla \"$nombreTabla\" no se ha guardado correctamente.\nError en la siguiente fila:\n\"$filaStr\"."];
                } else if ($e->getCode() == 1366) {  // Incorrect string value or Incorrect integer value  // Pasa por aquí cuando el ID está vacio.
                    continue;  // Continuar para eliminar fila más abajo.
                    // mysqli_close($conexion);
                    // return ["mensaje" => "La tabla \"$nombreTabla\" no se ha guardado correctamente.\nError de espacio en blanco en la siguiente fila:\n\"$filaStr\"."];
                } else {
                    return ["mensaje" => "Error desconocido: " . $e->getCode()];
                }
            }
        }

        $sentenciaSelect = "SELECT * from $nombreTabla";
        $resultado = mysqli_query($conexion, $sentenciaSelect);
        foreach (mysqli_fetch_all($resultado) as $fila) {
            if (!in_array($fila, $datos)) {
                $idEliminar = mysqli_real_escape_string($conexion, $fila[0]);
                mysqli_query($conexion, "DELETE FROM $nombreTabla WHERE " . $nombresColumnas[0] . " = '$idEliminar'");
            }
        }
        
        mysqli_close($conexion);
        return ["mensaje" => "Tabla \"$nombreTabla\" guardada correctamente"];
    }
}