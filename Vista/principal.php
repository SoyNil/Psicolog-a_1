<?php
session_start();
include("../Controlador/conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['Usuario'])) {
    header("Location: index.php");
    exit;
}
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['Usuario'])) {
    die("Error: Usuario no identificado.");
}
// Obtener datos de sesión
$usuario_actual = $_SESSION['Usuario'];
$ocupacion_actual = $_SESSION['Ocupacion'];
$usuario_id = $_SESSION['ID'];

// Modificar consulta según la ocupación del usuario
if ($ocupacion_actual == 1) {
    // Mostrar todos los pacientes con el nombre del usuario que los registró
    $query = "
        SELECT p.*, r.Usuario AS NombreUsuario
        FROM pacientes p
        JOIN registro r ON p.Usuario_ID = r.ID
    ";
} else {
    // Mostrar solo los pacientes del usuario actual
    $query = "
        SELECT p.*, r.Usuario AS NombreUsuario
        FROM pacientes p
        JOIN registro r ON p.Usuario_ID = r.ID
        WHERE p.Usuario_ID = :usuario_id
    ";
}

$stmt = $pdo->prepare($query);

// Enlazar parámetro solo si la ocupación no es 1
if ($ocupacion_actual != 1) {
    $stmt->bindParam(':usuario_id', $usuario_id);
}

$stmt->execute();
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link rel="stylesheet" href="../Modelo/estilos.css">
    <link rel="stylesheet" href="../Modelo/estilosEditarModal.css">
    <link rel="stylesheet" href="../Modelo/estilosAvisos.css">
    <!-- Añadir jQuery desde CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <!-- Encabezado con datos del usuario -->
    <header class="header">
        <div class="header-container">
            <strong>Bienvenido, <?php echo htmlspecialchars($usuario_actual); ?></strong>
            <span>| Ocupación: 
                <?php 
                    echo $ocupacion_actual == 1 ? "Administrador" : ($ocupacion_actual == 2 ? "Psicólogo" : "Desconocido"); 
                ?>
            </span>
            <a href="../Controlador/logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Sección izquierda: Lista de pacientes y botón para agregar -->
    <main class="main-container">
        <div class="left-container">
            <h3>Mis Pacientes</h3>
            <div class="button-container">
                <button class="add-btn" onclick="openAddModal()">Agregar Paciente</button>
            </div>
            <!-- Cuadro de búsqueda -->
            <input type="text" id="searchInput" class="search-box" placeholder="Buscar paciente por nombre o DNI..." oninput="filterPatients()">
            
            <ul class="patient-list" id="patientList">
                <?php if (empty($pacientes)): ?>
                    <li class="no-patients">No hay usuarios registrados</li>
                <?php else: ?>
                    <?php foreach ($pacientes as $paciente): ?>
                        <li class="patient-item">
                        <div class="patient-info">
                            <div><strong><?php echo htmlspecialchars($paciente['Nombre']); ?></strong></div>
                            <div>DNI: <?php echo htmlspecialchars($paciente['DNI']); ?></div>
                            <div>Fecha creada: <?php echo htmlspecialchars($paciente['fecha_actual']); ?></div>
                            <div>Fecha de la cita: <?php echo htmlspecialchars($paciente['Fecha']); ?></div>
                            <div>Precio: S/.<?php echo htmlspecialchars($paciente['Precio']); ?></div>
                            <div>Ganancia: S/.<?php echo htmlspecialchars($paciente['Precio_Descuento']); ?></div>
                            <?php if ($ocupacion_actual == 1): ?>
                                <div><strong>Psicólogo:</strong> <?php echo htmlspecialchars($paciente['NombreUsuario']); ?></div>
                            <?php endif; ?>
                        </div>
                            
                        <div class="switch-container">
                            <div class="switch-wrapper">
                                <label for="atendio-<?php echo $paciente['ID']; ?>">¿Atendió al paciente?</label>
                                <label class="switch">
                                    <input type="checkbox" id="atendio-<?php echo $paciente['ID']; ?>" 
                                        onchange="handleAtendido(<?php echo $paciente['ID']; ?>, this.checked)"
                                        <?php echo $paciente['Atendido'] ? 'checked' : ''; ?>>
                                    <span class="slider green-red"></span>
                                </label>
                            </div>
                            <div class="switch-wrapper">
                                <label for="hhcc-<?php echo $paciente['ID']; ?>">Historial Clínico</label>
                                <label class="switch">
                                    <input type="checkbox" id="hhcc-<?php echo $paciente['ID']; ?>" 
                                        onchange="handleHHCC(<?php echo $paciente['ID']; ?>, this.checked)"
                                        <?php echo $paciente['Historial_Clinico'] ? 'checked' : ''; ?>>
                                    <span class="slider green-red"></span>
                                </label>
                            </div>
                        </div>
                        <div class="button-container">
                            <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($paciente)); ?>)">Editar</button>
                            <button class="delete-btn" onclick="deletePaciente(<?php echo $paciente['ID']; ?>)">Eliminar</button>
                        </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
    <!-- Diagrama Pastel -->
    <div class="chart-container">
        <!-- Contenedor para la leyenda -->
        <div class="legend-container">
            <ul id="custom-legend"></ul>
        </div>
        <!-- Gráfico de pastel -->
        <canvas id="pieChart"></canvas>
        <!-- Valor total en el centro del gráfico -->
        <div class="chart-center">
            <span id="totalValue">0</span>
        </div>
    </div>

    </main>
    
    <!-- Contenedor de los filtros de fecha -->
    <div id="dateFiltersSection">
        <div id="filterButtons">
            <h2>Seleccione un filtro de fechas</h2>
            <button id="filterWeek">Últimos 7 días</button>
            <button id="filterMonth">Últimos 30 días</button>
            <button id="filterYear">Últimos 365 días</button>
        </div>
        <!-- Formulario para filtrar por fecha personalizada -->
        <div id="customDateFilter">
            <h2>Fecha personalizada</h2>
            <label for="startDate">Fecha de inicio:</label>
            <input type="date" id="startDate">
            <label for="endDate">Fecha de fin:</label>
            <input type="date" id="endDate">
            <button onclick="applyCustomDateFilter()" class="filter-btn">Filtrar</button>
        </div>
    </div>

    <!-- Contenedor del botón y avisos -->
    <div class="avisos-section">
        <!-- Botones para proyección y agregar aviso -->
        <div class="add-buttons">
            <button onclick="openProjectionModal()" class="add-notice-btn">Proyección</button>
            <button onclick="showAddNoticeForm()" class="add-notice-btn">Agregar Aviso</button>
        </div>
        <!-- Sección para mostrar los avisos -->
        <div id="avisos-container"></div>
    </div>

    <!-- Modal para agregar aviso -->
    <div id="noticeModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Agregar Aviso</h2>
            <textarea id="noticeText" placeholder="Escribe tu aviso aquí..."></textarea>
            <button class="submit-btn" onclick="addNotice()">Agregar Aviso</button>
        </div>
    </div>

    <!-- Modal para agregar paciente -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddModal()">&times;</span>
            <h2>Agregar Paciente</h2>
            <form id="addForm">
                <label for="add_dni">DNI:</label>
                <input type="text" name="dni" id="add_dni" required>
                <label for="add_nombre">Nombre:</label>
                <input type="text" name="nombre" id="add_nombre" required>
                <label for="add_fecha">Fecha:</label>
                <input type="date" name="fecha" id="add_fecha" required>
                <label for="add_precio">Precio:</label>
                <input type="number" name="precio" id="add_precio" step="0.01" min="0" oninput="validateDecimals(this)" required>
                <label for="descuento">Porcentaje de ganancia (%):</label>
                <input type="number" name="descuento" id="descuento" step="0.01" min="0" oninput="validateDecimals(this)" value="60" required>
                <button type="button" class="submit-btn" onclick="submitAddForm()">Guardar</button>
            </form>
        </div>
    </div>

    <?php $mostrarExtras = ($_SESSION['Ocupacion'] == 1); ?>
    <!-- Modal para editar paciente -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h2>Editar Paciente</h2>
            <form id="editForm">
                <input type="hidden" name="edit_id" id="edit_id">
                <label for="edit_dni">DNI:</label>
                <input type="text" name="dni" id="edit_dni" required>
                <label for="edit_nombre">Nombre:</label>
                <input type="text" name="nombre" id="edit_nombre" required>
                <label for="edit_fecha">Fecha:</label>
                <input type="date" name="fecha" id="edit_fecha" required>
                <label for="edit_precio">Precio:</label>
                <input type="number" name="precio" id="edit_precio" step="0.01" min="0" oninput="validateDecimals(this)" required>

                <?php if ($mostrarExtras): ?>
                    <label for="edit_descuento">Porcentaje de ganancia (%):</label>
                    <input type="number" name="descuento" id="edit_descuento" step="0.01" min="0" max="100" oninput="validateDecimals(this)">
                    
                    <label for="edit_usuario">Usuario:</label>
                    <select name="usuario" id="edit_usuario">
                        <!-- Llenaremos dinámicamente los usuarios disponibles -->
                    </select>
                <?php endif; ?>

                <button type="button" class="submit-btn" onclick="submitEditForm()">Guardar Cambios</button>
            </form>
        </div>
    </div>
    <!-- Modal para ingresar datos de proyección -->
    <div id="projectionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeProjectionModal()">&times;</span>
            <h2>Proyección de Pacientes</h2>
            <form id="projectionForm">
                <label for="metaSueldo">Meta de Sueldo:</label>
                <input type="number" id="metaSueldo" name="metaSueldo" min="0" step="0.01" required>
                
                <label for="precioPaciente">Precio por Paciente:</label>
                <input type="number" id="precioPaciente" name="precioPaciente" min="0" step="0.01" required>
                
                <label for="porcentajeGanancia">Porcentaje de Ganancia (%):</label>
                <input type="number" id="porcentajeGanancia" name="porcentajeGanancia" min="0" max="100" step="0.01" required>
                
                <button type="button" class="submit-btn" onclick="calculateProjection()">Proyectar</button>
            </form>
        </div>
    </div>

    <!-- Modal para mostrar resultados de la proyección -->
    <div id="resultModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeResultModal()">&times;</span>
            <h2>Resultados de la Proyección</h2>
            <p id="projectionResult"></p>
            <button class="submit-btn" onclick="closeResultModal()">Cerrar</button>
        </div>
    </div>

    <script>
        // Función para aplicar el filtro por fechas personalizadas
        function applyCustomDateFilter() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            // Validar que las fechas sean válidas
            if (!startDate || !endDate) {
                alert('Por favor, selecciona un rango de fechas válido.');
                return;
            }

            // Enviar las fechas al servidor para obtener los pacientes filtrados
            fetch(`../Controlador/get_pacientes.php?startDate=${startDate}&endDate=${endDate}`)
                .then(response => response.json())
                .then(data => renderDonutChart(data))  // Llamar a la función para renderizar el gráfico
                .catch(error => {
                    console.error('Error al cargar los pacientes:', error);
                });
        }
        // Función para filtrar pacientes según el rango de tiempo
        function filterByDateRange(pacientes, range) {
            const today = new Date();
            let startDate;

            // Determinar la fecha de inicio según el rango
            switch (range) {
                case 'week': // Última semana
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - 7);
                    break;
                case 'month': // Último mes
                    startDate = new Date(today);
                    startDate.setMonth(today.getMonth() - 1);
                    break;
                case 'year': // Último año
                    startDate = new Date(today);
                    startDate.setFullYear(today.getFullYear() - 1);
                    break;
                default:
                    return pacientes; // Si no hay rango válido, devolver todos los pacientes
            }

            // Filtrar pacientes según la columna fecha_actual
            return pacientes.filter(paciente => {
                const fechaPaciente = new Date(paciente.fecha_actual);
                return fechaPaciente >= startDate && fechaPaciente <= today;
            });
        }

        // Agregar eventos a los botones
        document.getElementById('filterWeek').addEventListener('click', () => {
            applyFilter('week');
        });
        document.getElementById('filterMonth').addEventListener('click', () => {
            applyFilter('month');
        });
        document.getElementById('filterYear').addEventListener('click', () => {
            applyFilter('year');
        });

        // Función para aplicar el filtro y actualizar el diagrama
        function applyFilter(range) {
            fetch(`../Controlador/get_pacientes.php?range=${range}`)
                .then(response => response.text()) // Cambia a .text() para obtener el texto y depurar
                .then(data => {
                    console.log('Raw response:', data); // Muestra lo que realmente se recibe
                    try {
                        const pacientes = JSON.parse(data); // Intenta convertir el texto a JSON
                        renderDonutChart(pacientes);
                    } catch (error) {
                        console.error('Error al procesar JSON:', error);
                    }
                })
        }

        // Abrir y cerrar modales
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        // Función para abrir el modal y cargar los datos en los campos
        function openEditModal(paciente) {
            console.log(paciente);  // Depura para ver los datos

            // Asignar datos básicos
            document.getElementById('edit_id').value = paciente.ID;
            document.getElementById('edit_dni').value = paciente.DNI;
            document.getElementById('edit_nombre').value = paciente.Nombre;
            document.getElementById('edit_fecha').value = paciente.Fecha;
            document.getElementById('edit_precio').value = paciente.Precio;

            // Asignar datos extra si el campo existe
            const descuentoField = document.getElementById('edit_descuento');
            const usuarioField = document.getElementById('edit_usuario');

            if (descuentoField) {
                descuentoField.value = paciente.Descuento || 0;
            }

            if (usuarioField) {
                // Cargar usuarios en el select
                fetch('../Controlador/obtener_usuarios.php')
                    .then(response => response.json())
                    .then(data => {
                        usuarioField.innerHTML = ''; // Limpiar opciones previas
                        data.forEach(usuario => {
                            const option = document.createElement('option');
                            option.value = usuario.ID;
                            option.textContent = usuario.Usuario;
                            if (usuario.ID === paciente.Usuario_ID) {
                                option.selected = true;
                            }
                            usuarioField.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Error al cargar usuarios:", error));
            }

            // Mostrar el modal
            document.getElementById('editModal').style.display = 'block';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Enviar formulario para agregar paciente
        function submitAddForm() {
            const formData = new FormData(document.getElementById('addForm'));
            fetch('../Controlador/add_paciente.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
        
        // Enviar formulario para editar paciente
        function submitEditForm() {
            const formData = new FormData(document.getElementById('editForm'));
            fetch('../Controlador/actualizar_paciente.php', {
                method: 'POST',
                body: formData, // Enviamos todos los datos del formulario
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                closeEditModal();  // Cerrar el modal después de la actualización
                location.reload();  // Recargar la página para ver los cambios
            })
            .catch(error => console.error("Error:", error));
        }

        // Eliminar paciente
        function deletePaciente(id) {
            if (confirm('¿Estás seguro de eliminar este paciente?')) {
                fetch('../Controlador/delete_paciente.php', {
                    method: 'POST',
                    body: JSON.stringify({ id }),
                    headers: { 'Content-Type': 'application/json' },
                })
                .then(response => response.json()) // Convertir la respuesta JSON
                .then(data => {
                    alert(data.message); // Mostrar solo el mensaje
                    if (data.success) {
                        location.reload(); // Recargar la página si tuvo éxito
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hubo un error al eliminar el paciente.');
                });
            }
        }
        // Función para actualizar ambos switches y recargar el gráfico
        function handleAtendido(id, isChecked) {
            const valorAtendido = isChecked ? 1 : 0;

            // Actualizar "Atendido"
            $.ajax({
                url: '../Controlador/actualizar_paciente.php',
                type: 'POST',
                data: { id: id, columna: 'Atendido', valor: valorAtendido },
                success: function () {
                    // Después de actualizar "Atendido", actualizar "Historial_Clinico" y recargar gráfico
                    updateHHCCAndRefresh(id);
                },
                error: function () {
                    alert("Error al actualizar el estado de 'Atendido'.");
                }
            });
        }

        function handleHHCC(id, isChecked) {
            const valorHistorial = isChecked ? 1 : 0;

            // Actualizar "Historial_Clinico"
            $.ajax({
                url: '../Controlador/actualizar_paciente.php',
                type: 'POST',
                data: { id: id, columna: 'Historial_Clinico', valor: valorHistorial },
                success: function () {
                    // Después de actualizar "Historial_Clinico", actualizar "Atendido" y recargar gráfico
                    updateAtendidoAndRefresh(id);
                },
                error: function () {
                    alert("Error al actualizar el estado de 'Historial Clínico'.");
                }
            });
        }

        // Función para actualizar ambos valores y luego recargar el gráfico
        function updateAtendidoAndRefresh(id) {
            const atendidoValue = $('#atendio-' + id).prop('checked') ? 1 : 0;
            const historialValue = $('#hhcc-' + id).prop('checked') ? 1 : 0;

            // Llamar a la función que recarga el gráfico
            refreshChart(atendidoValue, historialValue);
        }

        // Función para actualizar el historial clínico y luego recargar el gráfico
        function updateHHCCAndRefresh(id) {
            const atendidoValue = $('#atendio-' + id).prop('checked') ? 1 : 0;
            const historialValue = $('#hhcc-' + id).prop('checked') ? 1 : 0;

            // Llamar a la función que recarga el gráfico
            refreshChart(atendidoValue, historialValue);
        }

        //Limitar decimales
        function validateDecimals(input) {
            // Limita el número de decimales a 2
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                input.value = value.toFixed(2);
            }
        }
        //Diagrama
        // Función para recargar el gráfico de pastel con los valores actualizados
        function refreshChart(atendidoValue, historialValue) {
            // Obtener todos los pacientes nuevamente y recargar el gráfico
            fetch('../Controlador/get_pacientes.php')
                .then(response => response.json())
                .then(pacientes => {
                    // Llamar a la función para renderizar el gráfico
                    renderDonutChart(pacientes);  // Cambié renderPieChart por renderDonutChart
                })
                .catch(error => {
                    console.error('Error al cargar los pacientes:', error);
                });
        }

        // Función para calcular el color del gráfico según los switches "Atendido" y "Historial_Clinico"
        function getPieColor(paciente) {
            const atendido = paciente.Atendido;
            const historialClinico = paciente.Historial_Clinico;

            if (atendido === 1 && historialClinico === 1) {
                return 'green';  // Todos los switches son 1
            } else if (atendido === 1 || historialClinico === 1) {
                return 'orange';  // Al menos uno de los switches es 1
            } else {
                return 'red';  // Ambos switches son 0
            }
        }

        // Función para renderizar el gráfico de donut
        function renderDonutChart(pacientes) {
            // Calcular suma total de precios con descuento
            const totalValue = pacientes.reduce((sum, paciente) => sum + parseFloat(paciente.Precio_Descuento || 0), 0);
            document.getElementById('totalValue').textContent = `S/. ${totalValue.toFixed(2)}`;

            // Crear los datos para el gráfico a partir de los pacientes
            const data = {
                labels: pacientes.map(p => p.Nombre), // Nombres de los pacientes
                datasets: [{
                    data: pacientes.map(p => p.Precio_Descuento || 0), // Precios de los pacientes
                    backgroundColor: pacientes.map(p => getPieColor(p)), // Colores para cada segmento
                    hoverOffset: 4
                }]
            };

            // Definir la configuración para el gráfico
            const config = {
                type: 'pie',  // Usamos 'pie' porque 'donut' es un tipo de pie
                data: data,
                options: {
                    cutout: '60%',  // Esto crea el efecto donut, ajusta el tamaño del agujero
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const rawValue = Number(context.raw) || 0;
                                    return `${context.label}: S/. ${rawValue.toFixed(2)}`; // Formato del valor
                                }
                            }
                        },
                        legend: {
                            display: false // Ocultamos la leyenda predeterminada
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            };

            // Verificar que el canvas está presente en el DOM antes de crear el gráfico
            const canvas = document.getElementById('pieChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');

                // Verificar si el gráfico ya existe y destruirlo antes de crear uno nuevo
                if (window.myPieChart) {
                    window.myPieChart.destroy();
                }

                // Crear o actualizar el gráfico con la configuración
                window.myPieChart = new Chart(ctx, config);

                // Llamar a la función para renderizar la leyenda personalizada
                renderCustomLegend(window.myPieChart);
            } else {
                console.error("El elemento con id 'pieChart' no está presente en el DOM.");
            }
        }

        // Función para renderizar la leyenda personalizada
        function renderCustomLegend(chart) {
            const legendContainer = document.getElementById('custom-legend'); // Asegúrate de que este contenedor esté en el HTML
            if (!legendContainer) {
                console.error("El contenedor de la leyenda no está presente en el DOM.");
                return;
            }
            
            legendContainer.innerHTML = ''; // Limpiar la leyenda anterior

            chart.data.labels.forEach((label, index) => {
                const listItem = document.createElement('li');
                listItem.classList.add('legend-item');
                
                // Establecer el color del ítem de la leyenda
                const colorBox = document.createElement('span');
                colorBox.style.backgroundColor = chart.data.datasets[0].backgroundColor[index];
                listItem.appendChild(colorBox);
                
                // Añadir el nombre del paciente (o lo que sea la etiqueta)
                const labelText = document.createElement('span');
                labelText.textContent = label;
                listItem.appendChild(labelText);

                // Añadir evento de clic para alternar visibilidad del gráfico
                listItem.addEventListener('click', function () {
                    const meta = chart.getDatasetMeta(0);
                    const currentVisibility = meta.data[index].hidden;
                    meta.data[index].hidden = !currentVisibility;
                    
                    // Recalcular el total visible
                    const visibleTotal = chart.data.datasets[0].data
                        .map((value, i) => meta.data[i].hidden ? 0 : parseFloat(value || 0))
                        .reduce((sum, value) => sum + value, 0);
                    
                    // Actualizar el valor total en el DOM
                    document.getElementById('totalValue').textContent = `S/. ${visibleTotal.toFixed(2)}`;
                    
                    // Actualizar el gráfico
                    chart.update();
                });

                legendContainer.appendChild(listItem);
            });
        }

        // Llamar a la función para cargar el gráfico con datos iniciales
        document.addEventListener('DOMContentLoaded', () => {
            fetch('../Controlador/get_pacientes.php')
                .then(response => response.json())
                .then(data => renderDonutChart(data))  // Cambié renderPieChart por renderDonutChart
                .catch(error => console.error('Error al cargar los pacientes:', error));
        });

        // Mostrar el modal cuando el botón "Agregar Aviso" es presionado
        function showAddNoticeForm() {
            document.getElementById("noticeModal").style.display = "block";
        }
        // Cerrar el modal cuando se hace clic en la "X"
        function closeModal() {
            document.getElementById("noticeModal").style.display = "none";
        }
        // Función para agregar un nuevo aviso
        function addNotice() {
            const noticeText = document.getElementById("noticeText").value.trim();

            if (noticeText === "") {
                alert("Por favor, escribe un aviso.");
                return;
            }

            // Crear una solicitud para agregar el aviso al servidor
            fetch('../Controlador/add_notice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notice: noticeText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Si el aviso fue agregado con éxito, lo mostramos sin recargar la página
                    const aviso = data.notice;
                    showNotice(aviso);
                    closeModal();  // Cerrar el modal después de agregar el aviso
                    document.getElementById("noticeText").value = '';  // Limpiar el campo de texto
                } else {
                    alert("Error al agregar el aviso.");
                }
            })
            .catch(error => console.error("Error:", error));
        }
        function showNotice(aviso) {
            const avisosContainer = document.getElementById('avisos-container');

            // Crear un nuevo elemento para el aviso
            const avisoDiv = document.createElement('div');
            avisoDiv.classList.add('aviso');

            // Convertir la fecha a un formato legible
            const fecha = new Date(aviso.fecha);

            // Generar el contenido del aviso
            const avisoHTML = `
                <p><strong>${aviso.usuario_nombre}</strong> (${fecha.toLocaleString()})</p>
                <p>${aviso.aviso}</p>
            `;
            avisoDiv.innerHTML = avisoHTML;

            // Agregar el nuevo aviso al contenedor
            avisosContainer.prepend(avisoDiv); // `prepend` agrega el aviso al inicio
        }
        function getAvisos() {
            fetch('../Controlador/get_avisos.php')
                .then(response => response.json()) // Convertir la respuesta en JSON
                .then(data => {
                    const avisosContainer = document.getElementById('avisos-container');
                    avisosContainer.innerHTML = ''; // Limpiar el contenedor antes de agregar los nuevos avisos

                    if (data.error) {
                        avisosContainer.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }

                    // Mostrar los avisos en el contenedor
                    data.forEach(aviso => {
                        const avisoDiv = document.createElement('div');
                        avisoDiv.classList.add('aviso');

                        // Mostrar el nombre del usuario, la fecha y el contenido del aviso
                        const fecha = new Date(aviso.fecha);
                        avisoDiv.innerHTML = `
                            <p><strong>${aviso.usuario_nombre}</strong> (${fecha.toLocaleString()})</p>
                            <p>${aviso.aviso}</p>
                        `;
                        avisosContainer.appendChild(avisoDiv);
                    });
                })
                .catch(error => console.error('Error al obtener los avisos:', error));
        }

        // Ejecutar la función getAvisos cuando la página haya terminado de cargar
        document.addEventListener('DOMContentLoaded', function() {
            getAvisos(); // Llamar la función para mostrar los avisos
        });

        // Abrir el modal de proyección
        function openProjectionModal() {
            document.getElementById('projectionModal').style.display = 'block';
        }

        // Cerrar el modal de proyección
        function closeProjectionModal() {
            document.getElementById('projectionModal').style.display = 'none';
        }

        // Cerrar el modal de resultados
        function closeResultModal() {
            document.getElementById('resultModal').style.display = 'none';
        }

        // Calcular la proyección
        function calculateProjection() {
            // Obtener valores del formulario
            const metaSueldo = parseFloat(document.getElementById('metaSueldo').value);
            const precioPaciente = parseFloat(document.getElementById('precioPaciente').value);
            const porcentajeGanancia = parseFloat(document.getElementById('porcentajeGanancia').value) / 100;

            // Validar datos
            if (isNaN(metaSueldo) || isNaN(precioPaciente) || isNaN(porcentajeGanancia) || porcentajeGanancia <= 0) {
                alert("Por favor, completa todos los campos correctamente.");
                return;
            }

            // Calcular la ganancia por paciente
            const gananciaPorPaciente = precioPaciente * porcentajeGanancia;

            // Calcular el número mínimo de pacientes necesarios
            const pacientesNecesarios = Math.ceil(metaSueldo / gananciaPorPaciente);

            // Mostrar resultados en el modal
            const resultMessage = `
                Para alcanzar una meta de sueldo de <strong>S/. ${metaSueldo.toFixed(2)}</strong>, 
                cobrando <strong>S/. ${precioPaciente.toFixed(2)}</strong> por paciente y obteniendo un 
                <strong>${(porcentajeGanancia * 100).toFixed(2)}%</strong> de ganancia, 
                necesitas atender al menos <strong>${pacientesNecesarios}</strong> pacientes.
            `;

            document.getElementById('projectionResult').innerHTML = resultMessage;

            // Cerrar el modal de proyección y abrir el de resultados
            closeProjectionModal();
            document.getElementById('resultModal').style.display = 'block';
        }
        function filterPatients() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const patients = document.querySelectorAll('.patient-item');

            patients.forEach(patient => {
                const name = patient.querySelector('strong').textContent.toLowerCase();
                const dni = patient.textContent.toLowerCase();
                
                if (name.includes(input) || dni.includes(input)) {
                    patient.style.display = '';
                } else {
                    patient.style.display = 'none';
                }
            });
        }

    </script>
    <script></script>
</body>
</html>