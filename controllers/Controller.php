<?php

include_once "views/View.php";

class Controller {
    public function main() {
        $view = new View();
        $view->header();
        $view->mainCols();
        $view->table();
        $view->footer();
        return $view;
    }

    public function generateCSV(string $table): void {
        $filtro = $_GET['filtro'] ?? "";
        // echo "Filtro: " . $filtro;
        [$tabla, $columnas] = Model::obtenerDatosParaCSV($table);
        // print_r($tabla);

        $tablaFiltrada = array_filter($tabla, function($fila) use ($filtro) {
            return isset($fila[1]) && mb_stripos($fila[1], $filtro);
        });
        // print_r($tablaFiltrada);

        $fichero = fopen("php://output", "w");

        $cabecera = [];
        foreach ($columnas as $columna) {
            $cabecera[] = $columna[0];
        }

        fputcsv($fichero, $cabecera);
        foreach ($tablaFiltrada as $fila) {
            fputcsv($fichero, $fila);
        }

        fclose($fichero);
        exit();
    }
}