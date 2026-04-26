<?php
include_once "controllers/Controller.php";

$controller = new Controller();

if (isset($_GET['opciones']) && !is_null($_GET['opciones'])) {
    if ($_GET['opciones'] == 'generateCSV') {
        $table = $_GET['tabla'] ?? 'cursos';
        echo $controller->generateCSV($table);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($controller->saveTable($data['tabla'], $data['datos']));
    exit();
}
else {
    echo $controller->main();
}
