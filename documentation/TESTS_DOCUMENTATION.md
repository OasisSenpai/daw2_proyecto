# 📋 Documentación de Tests - Proyecto MVC

## 📑 Índice
1. [Tests PHP](#tests-php)
   - [ControllerTest](#controllertest)
   - [ModelTest](#modeltest)
   - [ViewTest](#viewtest)
2. [Tests JavaScript](#tests-javascript)
   - [test_buscador.js](#test_buscadorjs)
   - [test_functions.js](#test_functionsjs)

---

## Tests PHP

### ControllerTest

**Ubicación:** `php/ControllerTest.php`

**Descripción:** Conjunto de pruebas para la clase `Controller` que gestiona la lógica principal de la aplicación.

#### Métodos de Prueba:

##### `testMainReturnsAViewObject()`
- **Propósito:** Verificar que el método `main()` del controlador retorna un objeto de tipo `View`
- **Qué prueba:** 
  - Llama a `$controller->main()`
  - Confirma que retorna una instancia de la clase `View`
- **Notas importantes:** Este test puede fallar si `Model::mostrarTabla()` intenta conectar a la BD sin tener una conexión válida
- **Aserción:** `assertInstanceOf(View::class, $view)`

---

### ModelTest

**Ubicación:** `php/ModelTest.php`

**Descripción:** Conjunto de pruebas para la clase `Model` que maneja todas las operaciones con la base de datos.

#### Métodos de Prueba:

##### `testModelClassExists()`
- **Propósito:** Verificación básica de que la clase `Model` existe
- **Qué prueba:**
  - Valida la existencia de la clase `Model`
- **Aserción:** `assertTrue(class_exists('Model'))`

---

##### `testObtenerTabla()`
- **Propósito:** Probar la obtención de datos de una tabla de la BD
- **Qué prueba:**
  - Se conecta a la BD usando `bbdd.php`
  - Llama a `Model::obtenerTabla($conexion, 'cursos')` para obtener los datos de la tabla `cursos`
  - Verifica que retorna un array con los datos y el número de columnas
- **Validaciones:**
  - `assertIsArray($tabla)` - Los datos retornados deben ser un array
  - `assertNotFalse($n_columnas)` - El conteo de columnas no debe ser false
- **Limpieza:** Cierra la conexión con `mysqli_close()`

---

##### `testColocarEncabezadoTabla()`
- **Propósito:** Verificar la generación del encabezado HTML de una tabla
- **Qué prueba:**
  - Se conecta a la BD usando `bbdd.php`
  - Llama a `Model::colocarEncabezadoTabla($conexion, 'cursos')` para generar el HTML
  - Valida que el HTML contenga las etiquetas correctas
- **Validaciones:**
  - `assertStringContainsString('<tr>')` - Debe contener filas
  - `assertStringContainsString('<th>')` - Debe contener encabezados
- **Limpieza:** Cierra la conexión con `mysqli_close()`

---

##### `testSincronizacionCompletaGuardarTabla()` ⭐ **Prueba crítica - CRUD completo**
- **Propósito:** Verificar que el sistema de sincronización de datos funciona correctamente (INSERT, UPDATE, DELETE)
- **Qué prueba:** Simula todo el ciclo de vida de un registro

**Fases de la prueba:**

1. **Limpieza Inicial:**
   - Borra datos de test anteriores con IDs '998' y '999'

2. **Fase 1: Inserción (INSERT)**
   - Envía datos con un ID nuevo ('999')
   - `Model::guardarTabla()` detecta que no existe y lo inserta
   - Verifica que el registro se creó con nombre `'Original'`

3. **Fase 2: Actualización (UPDATE)**
   - Envía los mismos datos pero con nombre modificado (`'Modificado'`)
   - El código detecta el error MySQL 1062 (clave duplicada)
   - Ejecuta UPDATE en lugar de INSERT
   - Verifica que el nombre se actualizó correctamente

4. **Fase 3: Borrado (DELETE)**
   - Envía una tabla vacía (solo encabezados, sin filas de datos)
   - El sistema detecta que el ID '999' ya no está en el array
   - Lo elimina de la BD automáticamente
   - Verifica que el registro fue borrado

---

##### `testManejoDuplicados()`
- **Propósito:** Prueba específica del manejo de registros duplicados (error 1062)
- **Qué prueba:**
  - Inserta manualmente una fila de prueba
  - Intenta guardar la misma fila con cambios en el nombre
  - Verifica que se ejecuta UPDATE correctamente
  - Confirma el cambio en la BD
- **Limpieza:** Borra el registro de test tras finalizar

---

### ViewTest

**Ubicación:** `php/ViewTest.php`

**Descripción:** Conjunto de pruebas para la clase `View` que genera el HTML de la aplicación.

#### Métodos de Prueba:

##### `testInitialPhtmlIsEmpty()`
- **Propósito:** Verificar que una Vista nueva comienza vacía
- **Qué prueba:** Al crear una instancia de `View`, no debe tener contenido HTML acumulado
- **Aserción:** `assertEquals("", (string)$this->view)`

---

##### `testHeaderGeneratesHtml()`
- **Propósito:** Verificar que el método `header()` genera HTML válido
- **Qué prueba:**
  - Llama a `$view->header()`
  - Valida que genere la estructura correcta del `<head>`
- **Validaciones:**
  - Contiene `<!DOCTYPE html>`
  - Contiene `<title>Gestor Brianda</title>`
  - Incluye referencia a `js/functions.js`

---

##### `testMainColsGeneratesActions()`
- **Propósito:** Verificar que se generan los botones de acción principal
- **Qué prueba:**
  - Llama a `$view->mainCols()`
  - Valida que aparezcan los botones de interfaz
- **Validaciones:**
  - Contiene botón "Añadir fila"
  - Contiene botón "Eliminar fila"
  - Contiene botón "Guardar tabla"

---

##### `testFooterClosesBodyAndHtml()`
- **Propósito:** Verificar que el método `footer()` cierra correctamente el HTML
- **Qué prueba:**
  - Llama a `$view->footer()`
  - Valida que genere las etiquetas de cierre
- **Validaciones:**
  - Contiene `</body>`
  - Contiene `</html>`

---

## Tests JavaScript

Se utilizan **QUnit** como framework de testing para JavaScript. Los tests se ejecutan en `js/run_tests.html`

### test_buscador.js

**Ubicación:** `js/test_buscador.js`

**Descripción:** Pruebas para la funcionalidad de búsqueda/filtrado de la tabla.

#### Métodos de Prueba:

##### `buscarTabla filtra correctamente las filas por contenido`

**Propósito:** Verificar que el buscador filtra filas según su contenido

**Estructura de datos de prueba (fixture):**
```
Encabezado: [ID, Nombre, Descripción]
Fila 1: [1, PHP Básico, ...]
Fila 2: [2, JavaScript Avanzado, ...]
```

**Casos de prueba:**

| Caso | Búsqueda | Comportamiento esperado |
|------|----------|------------------------|
| 1 | "PHP" | Fila 1 visible (`display: ''`), Fila 2 oculta (`display: 'none'`) |
| 2 | "JS" | Fila 1 oculta, Fila 2 visible |
| 3 | "Inexistente" | Ambas filas ocultas |
| 4 | "" (vacío) | Ambas filas visibles (se limpia el filtro) |

**Lógica de filtrado:**
```javascript
buscarTabla('PHP')  // Filtra y muestra solo filas que contienen "PHP"
buscarTabla('JS')   // Filtra y muestra solo filas que contienen "JS"
buscarTabla('')     // Limpia el filtro, muestra todas las filas
```

---

### test_functions.js

**Ubicación:** `js/test_functions.js`

**Descripción:** Pruebas completas para todas las funciones de manipulación de la tabla.

#### Métodos de Prueba:

##### `contarCeldasTabla cuenta correctamente los encabezados (th)`

- **Propósito:** Verificar que se cuenta correctamente el número de columnas
- **Qué prueba:** Llama a `contarCeldasTabla()` y comprueba que retorna el número correcto
- **Estructura de fixture:** Tabla con 3 encabezados (ID, Nombre, Descripción)
- **Aserción:** `assert.equal(total, 3)`

---

##### `annadirFila agrega una fila nueva y vacía a la tabla`

- **Propósito:** Verificar que se puede agregar una fila a la tabla dinámicamente
- **Qué prueba:**
  - Cuenta las filas iniciales
  - Llama a `annadirFila()`
  - Cuenta las filas finales
- **Validaciones:**
  - El número de filas aumenta en 1
  - La nueva fila tiene el mismo número de celdas que encabezados (3)
  - Las celdas contienen el texto por defecto `"ESCRIBE AQUÍ"`
  - Las celdas tienen la propiedad `contentEditable = true`

**Código de la función:**
```javascript
function annadirFila() {
    const tabla = document.getElementById("tablaDatos");
    const fila = document.createElement("tr");
    for (let i=0;i<contarCeldasTabla();i++) {
        const celda = document.createElement("td");
        celda.contentEditable = "true";
        celda.innerHTML = "ESCRIBE AQUÍ";
        fila.appendChild(celda);
    }
    tabla.querySelector("tbody").appendChild(fila);
}
```

---

##### `seleccionarFila activa y desactiva la clase CSS de selección`

- **Propósito:** Verificar la selección de filas con soporte para selección múltiple
- **Qué prueba:** Diferentes escenarios de click en filas

**Casos de prueba:**

| Caso | Acción | Comportamiento esperado |
|------|--------|------------------------|
| 1 | Click sin Ctrl en fila 1 | Fila 1 obtiene clase `"fila-seleccionada"` |
| 2 | Click sin Ctrl en fila 1 de nuevo | Clase se quita (toggle) |
| 3 | Click sin Ctrl en fila 1, luego en fila 2 | Fila 1 pierde clase, fila 2 la obtiene |
| 4 | Click con Ctrl | Selección múltiple (ambas filas pueden estar seleccionadas) |

**Lógica de selección:**
```javascript
function seleccionarFila(fila, event) {
    if (!event.ctrlKey) {
        // Sin Ctrl: deselecciona todas y selecciona la actual
        document.querySelectorAll(".fila-seleccionada")
                .forEach(el => el.classList.remove("fila-seleccionada"));
    }
    // Toggle (añade o quita la clase)
    fila.classList.toggle("fila-seleccionada");
}
```

---

##### `eliminarFila quita del DOM las filas que tienen la clase de selección`

- **Propósito:** Verificar que se pueden eliminar filas seleccionadas
- **Qué prueba:**
  - Cuenta las filas iniciales
  - Marca una fila con la clase `"fila-seleccionada"`
  - Llama a `eliminarFila()`
  - Verifica que la fila fue eliminada
- **Validaciones:**
  - El número de filas disminuye en 1
  - La fila eliminada ya no existe en el DOM
- **Validación HTML:**
```javascript
assert.equal(totalFinal, totalInicial - 1, 'Debe haber una fila menos');
assert.notOk(tbody.contains(fila), 'La fila no debe estar en el DOM');
```

---

## 📊 Resumen de Cobertura de Tests

### PHP - Cobertura CRUD

| Operación | Test | Estado |
|-----------|------|--------|
| **C**reate (INSERT) | `testSincronizacionCompletaGuardarTabla` (Fase 1) | ✅ Probado |
| **R**ead (SELECT) | `testObtenerTabla`, `testColocarEncabezadoTabla` | ✅ Probado |
| **U**pdate (UPDATE) | `testSincronizacionCompletaGuardarTabla` (Fase 2) | ✅ Probado |
| **D**elete (DELETE) | `testSincronizacionCompletaGuardarTabla` (Fase 3) | ✅ Probado |
| Manejo de duplicados (Error 1062) | `testManejoDuplicados` | ✅ Probado |

### JavaScript - Cobertura de Funcionalidad

| Función | Test | Estado |
|---------|------|--------|
| `contarCeldasTabla()` | ✅ Probado (3 casos) |
| `annadirFila()` | ✅ Probado (3 validaciones) |
| `seleccionarFila()` | ✅ Probado (4 casos) |
| `eliminarFila()` | ✅ Probado (2 validaciones) |
| `buscarTabla()` | ✅ Probado (4 escenarios) |

---

## 🚀 Ejecución de Tests

### Tests PHP
```bash
# Desde la raíz del proyecto
vendor/bin/phpunit tests/php/
```

### Tests JavaScript
1. Abre `js/run_tests.html` en un navegador
2. Los resultados aparecerán con la interfaz de QUnit

---

## 📝 Notas Importantes

- Los tests PHP requieren una conexión válida a la base de datos
- Los tests JavaScript utilizan fixtures HTML incluidos en el HTML de test
- La prueba `testSincronizacionCompletaGuardarTabla` es la más completa, validando todo el ciclo CRUD
- El sistema de actualización usa el error MySQL 1062 (clave duplicada) como mecanismo para detectar actualizaciones
