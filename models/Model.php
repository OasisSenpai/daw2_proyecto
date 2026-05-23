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
        require __DIR__ . "/bbdd.php";
        $txt = "";
        
        [$tabla, $n_columnas] = Model::obtenerTabla($conexion, $nombre);

        $txt .= "<table id='tablaDatos' style='width: 100%;' border='1px'>\n";
        $txt .= Model::colocarEncabezadoTabla($conexion, $nombre, $n_columnas);
        mysqli_close($conexion);

        foreach ($tabla as $linea) {
            $txt .= "\t<tr onclick='seleccionarFila(this, event)'>\n";
            foreach ($linea as $columna) {
                $txt .= "\t<td contenteditable='true'>$columna</td>\n";
            }
            $txt .= "\t</tr>\n";
        }
        $txt .= "</table>\n";
        $txt .= "<script>document.getElementById('tableName').innerText = '" . str_replace("_", " ", ucfirst($nombre)) . "';</script>";

        return $txt;
    }


    public static function obtenerEncabezadoTabla($conexion, string $nombre): array {
        $query = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = '$nombre' AND TABLE_SCHEMA = 'daw_proyecto'";

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
        require __DIR__ . "/bbdd.php";

        if (empty($nombreTabla)) exit();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . htmlspecialchars($nombreTabla) . '.csv"');

        $tabla = Model::obtenerTabla($conexion, $nombreTabla)[0];
        $columnas = Model::obtenerEncabezadoTabla($conexion, $nombreTabla);
        mysqli_close($conexion);

        return [$tabla, $columnas];
    }


    /**
     * Nueva función optimizada para guardar tabla
     * Detecta automáticamente si debe insertar, actualizar o eliminar cada fila
     */
    public static function guardarTabla(string $nombreTabla, array $datos): array {
        require __DIR__ . "/bbdd.php";
        
        $nombreTabla = strtolower($nombreTabla);
        
        try {
            // Iniciar transacción para garantizar consistencia
            mysqli_query($conexion, "START TRANSACTION");
            
            // Validar y preparar datos
            $validacion = self::validarYPrepararDatos($conexion, $nombreTabla, $datos);
            
            // Contadores de operaciones
            $contadores = ['insertadas' => 0, 'actualizadas' => 0, 'eliminadas' => 0];
            
            // Procesar inserciones y actualizaciones
            $contadores['insertadas'] = self::procesarInsertacionesYActualizaciones(
                $conexion, 
                $nombreTabla, 
                $validacion['filasNuevas'],
                $validacion['idsActuales'],
                $validacion['datosActualesMap'],
                $validacion['nombresColumnas'],
                $validacion['columnaPrimaria'],
                $contadores['actualizadas']
            );
            
            // Procesar eliminaciones
            $contadores['eliminadas'] = self::procesarEliminaciones(
                $conexion,
                $nombreTabla,
                $validacion['idsActuales'],
                $validacion['idsNuevos'],
                $validacion['columnaPrimaria']
            );
            
            // Confirmar transacción
            mysqli_query($conexion, "COMMIT");
            mysqli_close($conexion);
            
            $resumen = "Tabla \"$nombreTabla\" guardada correctamente. "
                     . "Insertadas: {$contadores['insertadas']}, "
                     . "Actualizadas: {$contadores['actualizadas']}, "
                     . "Eliminadas: {$contadores['eliminadas']}";
            
            return ["mensaje" => $resumen];
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            @mysqli_query($conexion, "ROLLBACK");
            @mysqli_close($conexion);
            
            return self::manejarError($e);
        }
    }

    /**
     * Valida y prepara los datos para procesar
     */
    private static function validarYPrepararDatos($conexion, string $nombreTabla, array $datos): array {
        // Obtener estructura de la tabla
        $columnas = Model::obtenerEncabezadoTabla($conexion, $nombreTabla);
        $nombresColumnas = array_map(function($col) { return $col[0]; }, $columnas);
        $columnaPrimaria = $nombresColumnas[0];
        
        if (empty($nombresColumnas)) {
            throw new Exception("No se pudieron obtener las columnas de la tabla");
        }
        
        // Obtener datos actuales de la BD
        $querySelect = "SELECT * FROM daw_proyecto.$nombreTabla";
        $resultadoSelect = mysqli_query($conexion, $querySelect);
        $datosActuales = mysqli_fetch_all($resultadoSelect) ?: [];
        
        // Crear mapas de IDs para búsqueda rápida
        $idsActuales = array_map(function($fila) { return $fila[0]; }, $datosActuales);
        $datosActualesMap = [];
        foreach ($datosActuales as $fila) {
            $datosActualesMap[$fila[0]] = $fila;
        }
        
        // Procesar datos nuevos (saltando encabezado en posición 0)
        $filasNuevas = array_slice($datos, 1);
        $idsNuevosVerif = array_map(function($fila) { return $fila[0]; }, $filasNuevas);
        
        // Validar IDs duplicados en los datos nuevos
        self::validarIdsDuplicados($idsNuevosVerif);
        
        // Validar que ningún ID esté vacío
        foreach ($filasNuevas as $fila) {
            if (empty($fila[0])) {
                throw new Exception("El ID (primera columna) no puede estar vacío");
            }
        }
        
        return [
            'filasNuevas' => $filasNuevas,
            'idsActuales' => $idsActuales,
            'datosActualesMap' => $datosActualesMap,
            'nombresColumnas' => $nombresColumnas,
            'columnaPrimaria' => $columnaPrimaria,
            'idsNuevos' => $idsNuevosVerif
        ];
    }

    /**
     * Valida que no haya IDs duplicados
     */
    private static function validarIdsDuplicados(array $ids): void {
        $idsUnicos = array_unique($ids);
        if (count($idsUnicos) !== count($ids)) {
            $idsDuplicados = array_diff_assoc($ids, $idsUnicos);
            $idDuplicado = reset($idsDuplicados);
            throw new Exception("El ID '$idDuplicado' está duplicado en los datos a guardar.", 1000);
        }
    }

    /**
     * Procesa las inserciones y actualizaciones
     */
    private static function procesarInsertacionesYActualizaciones(
        $conexion,
        string $nombreTabla,
        array $filasNuevas,
        array $idsActuales,
        array $datosActualesMap,
        array $nombresColumnas,
        string $columnaPrimaria,
        int &$actualizadas
    ): int {
        $insertadas = 0;
        $actualizadas = 0;
        
        foreach ($filasNuevas as $fila) {
            $idActual = $fila[0];
            
            // Escapar valores
            $valoresEscapados = array_map(function($valor) use ($conexion) {
                return "'" . mysqli_real_escape_string($conexion, $valor) . "'";
            }, $fila);
            
            if (in_array($idActual, $idsActuales)) {
                // ACTUALIZAR
                $actualizadas += self::actualizarFila(
                    $conexion,
                    $nombreTabla,
                    $fila,
                    $datosActualesMap[$idActual],
                    $nombresColumnas,
                    $columnaPrimaria
                );
            } else {
                // INSERTAR
                $insertadas += self::insertarFila(
                    $conexion,
                    $nombreTabla,
                    $fila,
                    $valoresEscapados,
                    $nombresColumnas
                );
            }
        }
        
        return $insertadas;
    }

    /**
     * Actualiza una fila si hay cambios
     */
    private static function actualizarFila(
        $conexion,
        string $nombreTabla,
        array $filaNueva,
        array $filaAnterior,
        array $nombresColumnas,
        string $columnaPrimaria
    ): int {
        $sets = [];
        
        foreach ($nombresColumnas as $i => $col) {
            if (isset($filaNueva[$i]) && $filaNueva[$i] !== $filaAnterior[$i]) {
                $valor = mysqli_real_escape_string($conexion, $filaNueva[$i]);
                $sets[] = "$col = '$valor'";
            }
        }
        
        if (empty($sets)) {
            return 0; // Sin cambios
        }
        
        $idActual = $filaNueva[0];
        $queryUpdate = "UPDATE daw_proyecto.$nombreTabla SET " . implode(", ", $sets) 
                     . " WHERE $columnaPrimaria = '" . mysqli_real_escape_string($conexion, $idActual) . "'";
        
        if (!mysqli_query($conexion, $queryUpdate)) {
            throw new Exception("Error al actualizar fila con ID $idActual: " . mysqli_error($conexion));
        }
        
        return 1;
    }

    /**
     * Inserta una nueva fila
     */
    private static function insertarFila(
        $conexion,
        string $nombreTabla,
        array $fila,
        array $valoresEscapados,
        array $nombresColumnas
    ): int {
        $nombresColumnasStr = implode(", ", $nombresColumnas);
        $valoresStr = implode(", ", $valoresEscapados);
        $queryInsert = "INSERT INTO daw_proyecto.$nombreTabla ($nombresColumnasStr) VALUES ($valoresStr)";
        
        if (!mysqli_query($conexion, $queryInsert)) {
            throw new Exception(mysqli_error($conexion), mysqli_errno($conexion));
        }
        
        return 1;
    }

    /**
     * Procesa las eliminaciones de filas
     */
    private static function procesarEliminaciones(
        $conexion,
        string $nombreTabla,
        array $idsActuales,
        array $idsNuevos,
        string $columnaPrimaria
    ): int {
        $eliminadas = 0;
        
        foreach ($idsActuales as $idActual) {
            if (!in_array($idActual, $idsNuevos)) {
                $queryDelete = "DELETE FROM daw_proyecto.$nombreTabla WHERE $columnaPrimaria = '" 
                             . mysqli_real_escape_string($conexion, $idActual) . "'";
                
                if (!mysqli_query($conexion, $queryDelete)) {
                    throw new Exception("Error al eliminar fila con ID $idActual: " . mysqli_error($conexion));
                }
                
                $eliminadas++;
            }
        }
        
        return $eliminadas;
    }

    /**
     * Maneja los errores y devuelve mensaje apropiado
     */
    private static function manejarError(Exception $e): array {
        $errorCode = (int)$e->getCode();
        $errorMsg = $e->getMessage();
        
        switch ($errorCode) {
            case 1000:
                $mensaje = $errorMsg;
                break;
            case 1406:
                $mensaje = "Valor muy largo en una columna:\n" . $errorMsg . ".";
                break;
            case 1366:
                $mensaje = "Valor incorrecto en la fila:\n" . $errorMsg . ".";
                break;
            case 1471:
            case 1288:
                $mensaje = "Las vistas no se pueden modificar.";
                break;
            case 1062:
                $mensaje = "Entrada duplicada detectada:\n" . $errorMsg . ".";
                break;
            default:
                $mensaje = $errorMsg ?: "Error desconocido al guardar la tabla. Contacte con un administrador.";
                break;
        }
        
        return ["mensaje" => $mensaje];
    }


    // public static function guardarTablaOriginal(string $nombreTabla, array $datos): array {
    //     require __DIR__ . "/bbdd.php";
        
    //     $nombreTabla = strtolower($nombreTabla);
    //     $columnas = Model::obtenerEncabezadoTabla($conexion, $nombreTabla);
    //     $nombresColumnas = array_map(function($col) { return $col[0]; }, $columnas);
    //     $nombresColumnasStr = implode(", ", $nombresColumnas);
        
    //     foreach (array_slice($datos, 1) as $fila) {
    //         $filaStr = implode("\", \"", $fila);
    //         $sentenciaInsert = "INSERT INTO $nombreTabla ($nombresColumnasStr)
    //                             VALUES (\"$filaStr\")";
    //         // return ["mensaje" => $sentenciaInsert];

    //         $sentenciaUpdate = "UPDATE $nombreTabla SET ";
    //         $sets = [];
    //         foreach ($nombresColumnas as $i => $col) {
    //             $valor = mysqli_real_escape_string($conexion, $fila[$i]);
    //             $sets[] = "$col = '$valor'";
    //         }
    //         $sentenciaUpdate .= implode(", ", $sets) . " WHERE " . $nombresColumnas[0] . " = \"" . $fila[0] . "\"";
    //         // return ["mensaje" => $sentenciaUpdate];

    //         try {
    //             mysqli_query($conexion, $sentenciaInsert);
    //         } catch (mysqli_sql_exception $e) {
    //             if ($e->getCode() == 1062) {  // Duplicate entry error
    //                 mysqli_query($conexion, $sentenciaUpdate);
    //             } else if ($e->getCode() == 1406) {  // Data too long for column
    //                 mysqli_close($conexion);
    //                 return ["mensaje" => "La tabla \"$nombreTabla\" no se ha guardado correctamente, valor muy largo.\nError en la siguiente fila:\n\"$filaStr\"."];
    //             } else if ($e->getCode() == 1366) {  // Incorrect string value or Incorrect integer value  // Pasa por aquí cuando el ID está vacio.
    //                 mysqli_close($conexion);
    //                 return ["mensaje" => "Valor incorrecto en la siguiente fila:\n\"$filaStr\"."];
    //                 // continue;  // Continuar para eliminar fila más abajo.
    //                 // mysqli_close($conexion);
    //                 // return ["mensaje" => "La tabla \"$nombreTabla\" no se ha guardado correctamente.\nError de espacio en blanco en la siguiente fila:\n\"$filaStr\"."];
    //             } else if ($e->getCode() == 1471) {  // La vista no se puede modificar.
    //                 mysqli_close($conexion);
    //                 return ["mensaje" => "Las vistas no se pueden modificar."];
    //             } else {
    //                 mysqli_close($conexion);
    //                 return ["mensaje" => "Error desconocido: " . $e->getCode()];
    //             }
    //         }
    //     }

    //     $sentenciaSelect = "SELECT * from $nombreTabla";
    //     $resultado = mysqli_query($conexion, $sentenciaSelect);
    //     foreach (mysqli_fetch_all($resultado) as $fila) {
    //         if (!in_array($fila, $datos)) {
    //             $idEliminar = mysqli_real_escape_string($conexion, $fila[0]);
    //             mysqli_query($conexion, "DELETE FROM $nombreTabla WHERE " . $nombresColumnas[0] . " = '$idEliminar'");
    //         }
    //     }
        
    //     mysqli_close($conexion);
    //     return ["mensaje" => "Tabla \"$nombreTabla\" guardada correctamente"];
    // }
}