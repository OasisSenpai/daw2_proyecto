<?php
include_once "controllers/Controller.php";

$controller = new Controller();

// if (isset($_GET['opciones']) && !is_null($_GET['opciones'])) {
//     if ($_GET['opciones'] == 'generateCSV') {
//         $table = $_GET['tabla'] ?? 'cursos';
//         echo $controller->generateCSV($table);
//     }
// }
if (isset($_GET['generateCSV']) && isset($_GET['tabla']) && isset($_GET['filtro']) && !is_null($_GET['filtro'])) {
    $table = $_GET['tabla'] ?? 'cursos';
    $filtro = $_GET['filtro'] ?? '';
    echo $controller->generateCSV($table, $filtro);
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode($controller->saveTable($data['tabla'], $data['datos']));
    exit();
}
else {
    echo $controller->main();
}
