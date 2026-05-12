<?php
$db_name = (getenv('PHPUNIT_TESTING') === 'true') ? 'daw_proyecto-tests' : 'daw_proyecto';

$conexion = mysqli_connect(
    'mariadb',
    'root',
    'root',
    $db_name
);