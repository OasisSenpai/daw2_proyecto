<?php
$db_name = (getenv('PHPUNIT_TESTING') === 'true') ? 'daw_proyecto-tests' : 'daw_proyecto';

// Activar excepciones en mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conexion = mysqli_connect(
    'mariadb',
    'root',
    'root',
    $db_name
);