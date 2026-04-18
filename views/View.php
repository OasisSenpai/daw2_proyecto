<?php

require_once 'models/Model.php';
class View {
    private $phtml;

    public function __construct() {
        $this->phtml = "";
    }

    public function __toString(): string {
        return $this->phtml;
    }

    public function header(): void {
        $this->phtml .= "
<!DOCTYPE html>
<html lang='es'>
    <head>
        <meta charset='UTF-8' />
        <meta name='viewport' content='width=device-width, initial-scale=1.0' />
        <title>Gestor Brianda</title>
        <link rel='shortcut icon' href='img/logo.png' type='image/x-icon' />
        <!-- Estilos -->
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css' />
        <!-- <link rel='stylesheet' href='css/style.css' />
        <link rel='stylesheet' href='css/tabla.css' />
        <link rel='stylesheet' href='css/temp.css' /> -->
        <link rel='stylesheet' href='css/styles.css' />
        <!-- JavaScript -->
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js'></script>
        <script src='js/buscador.js'></script>
        <script src='js/functions.js'></script>
    </head>
    <body>
        <header>
            <nav class='mb-3 d-flex justify-content-start gap-3'>
                <form action='' method='get' id='formOptions' target='_blank'>
                    <select name='opciones' id='opciones' class='form-select form-select-sm' onchange='prepararEnvioGeneradorCSV()' style='width: min-content;'>
                        <option value='' id='opcionPredeterminada' disabled selected hidden>Opciones</option>
                        <option value='generateCSV'>Exportar tabla (.csv)</option>
                    </select>
                </form>
                <form action='' method='get'>
                    <select name='tabla' id='tabla' class='form-select form-select-sm' onchange='form.submit()' style='width: min-content;'>
                        <option value='' disabled selected hidden>Tablas</option>
                        <option value='cursos'>Cursos</option>
                        <option value='departamentos'>Departamentos</option>
                        <option value='especialidades'>Especialidades</option>
                        <option value='vista_horas_departamentos'>Horas departamentos (vista)</option>
                        <option value='materias'>Materias</option>
                        <option value='vista_materias_cursos'>Materias (vista)</option>
                        <option value='optativas'>Optativas</option>
                        <option value='tipo_asignatura'>Tipos de asignatura</option>
                        <option value='turnos'>Turnos</option>
                    </select>
                </form>
            </nav>
        </header>";
    }

    public function footer(): void {
        $this->phtml .= "
    </body>
</html>";
    }

    public function mainCols(): void {
        $this->phtml .= "
        <div class='parent'>
            <div class='div1'>
                <img src='img/logo.png' alt='Logo' id='logo' />
            </div>
            <div class='div2'>
                <h1 id='tableName'>Nombre de la tabla</h1>
                <input type='text' id='buscador' placeholder='Buscar' onkeyup='buscarTabla(value)' />
                <!-- <input type='text' id='buscador' placeholder='Buscar' /> -->
            </div>
            <div class='div3'>
                <h3>Acciones</h3>
                <div>
                    <button onclick='annadirFila()'>Añadir fila</button>
                    <button>Eliminar fila</button>
                    <button>Guardar tabla (BBDD)</button>
                </div>
            </div>";
    }

    public function table(): void {
        $this->phtml .= "
            <div class='div4'>";

        if (isset($_GET['tabla']) && !is_null($_GET['tabla'])) {
            $nombre_tabla = $_GET['tabla'] ?? 'cursos';
            $this->phtml .= Model::mostrarTabla($nombre_tabla);
        }
        else $this->phtml .= Model::mostrarTabla();

        // $nombre_tabla = "";
        // if (isset($_GET['tabla']) && !is_null($_GET['tabla'])) {
        //     $nombre_tabla = $_GET['tabla'] ?? 'cursos';
        // }
        // $this->phtml .= Model::mostrarTabla($nombre_tabla);

        $this->phtml .="
            </div>
        </div>";
    }
}