<?php
include_once "functions.php";
// include_once "bbdd.php";
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Gestor Brianda</title>
        <link rel="shortcut icon" href="img/logo.png" type="image/x-icon" />
        <!-- Estilos -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" />
        <link rel="stylesheet" href="style.css" />
        <link rel="stylesheet" href="tabla.css" />
        <link rel="stylesheet" href="temp.css" />
        <!-- JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="buscador.js"></script>
    </head>
    <body>
        <header>
            <nav>
                <form action="" method="get" class="mb-3 d-flex justify-content-start gap-3">
                    <select name="opciones" id="opciones" class="form-select form-select-sm" style="width: min-content;">
                        <option value="" disabled selected hidden>Opciones</option>
                        <option value="0">Exportar tabla (.csv)</option>
                    </select>
                    <select name="tablas" id="tablas" class="form-select form-select-sm" onchange="form.submit()" style="width: min-content;">
                        <option value="" disabled selected hidden>Tablas</option>
                        <option value="cursos">Cursos</option>
                        <option value="departamentos">Departamentos</option>
                        <option value="especialidades">Especialidades</option>
                        <option value="vista_horas_departamentos">Horas departamentos (vista)</option>
                        <option value="materias">Materias</option>
                        <option value="vista_materias_cursos">Materias (vista)</option>
                        <option value="optativas">Optativas</option>
                        <option value="tipo_asignatura">Tipos de asignatura</option>
                        <option value="turnos">Turnos</option>
                    </select>
                </form>
            </nav>
        </header>
        <div class="parent">
            <div class="div1">
                <img src="img/logo.png" alt="Logo" />
            </div>
            <div class="div2">
                <h1 id="tableName">Nombre de la tabla</h1>
                <input type="text" id="buscador" placeholder="Buscar" onkeyup="buscarTabla(value)" />
            </div>
            <div class="div3">
                <h3>Acciones</h3>
                <div>
                    <button>Añadir fila</button>
                    <button>Eliminar fila</button>
                    <button>Guardar tabla (BBDD)</button>
                </div>
            </div>
            <div class="div4">
                <?php
                if (isset($_GET["tablas"]) && !is_null($_GET["tablas"])) {
                    $nombre_tabla = $_GET["tablas"] ?? "cursos";
                    echo mostrarTabla($nombre_tabla);
                }
                else echo mostrarTabla();
                ?>
            </div>
        </div>
    </body>
</html>