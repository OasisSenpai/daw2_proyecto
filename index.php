<?php
include_once "controllers/Controller.php";

$controller = new Controller();

if (isset($_GET['opciones']) && !is_null($_GET['opciones'])) {
    if ($_GET['opciones'] == 'generateCSV') {
        $table = $_GET['tabla'] ?? 'cursos';
        echo $controller->generateCSV($table);
    }
} else {
    echo $controller->main();
}
