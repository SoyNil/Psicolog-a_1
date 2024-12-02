<?php
session_start();
include("conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['Usuario'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['ID']; // ID del usuario actual

// Obtener los datos del paciente a editar
if (isset($_GET['id'])) {
    $paciente_id = $_GET['id'];

    $query = "SELECT * FROM pacientes WHERE ID = :paciente_id AND Usuario_ID = :usuario_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':paciente_id', $paciente_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        echo "Paciente no encontrado o no autorizado.";
        exit;
    }
} else {
    header("Location: principal.php");
    exit;
}

// Manejar la actualización de datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $precio = $_POST['precio'];

    $update_query = "UPDATE pacientes SET DNI = :dni, Nombre = :nombre, Fecha = :fecha, Precio = :precio WHERE ID = :paciente_id AND Usuario_ID = :usuario_id";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':dni', $dni);
    $update_stmt->bindParam(':nombre', $nombre);
    $update_stmt->bindParam(':fecha', $fecha);
    $update_stmt->bindParam(':precio', $precio);
    $update_stmt->bindParam(':paciente_id', $paciente_id);
    $update_stmt->bindParam(':usuario_id', $usuario_id);

    if ($update_stmt->execute()) {
        header("Location: principal.php");
        exit;
    } else {
        echo "Error al actualizar paciente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Paciente</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Editar Paciente</h2>
    <form action="" method="post" class="form">
        <label for="dni">DNI:</label>
        <input type="text" name="dni" id="dni" value="<?php echo htmlspecialchars($paciente['DNI']); ?>" required>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($paciente['Nombre']); ?>" required>

        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" value="<?php echo htmlspecialchars($paciente['Fecha']); ?>" required>

        <label for="precio">Precio:</label>
        <input type="number" name="precio" id="precio" step="0.01" value="<?php echo htmlspecialchars($paciente['Precio']); ?>" required>

        <button type="submit" class="submit-btn">Guardar Cambios</button>
    </form>
</body>
</html>
