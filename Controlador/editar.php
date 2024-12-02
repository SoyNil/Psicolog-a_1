<?php
session_start();
include("conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['Usuario'])) {
    // Redirigir a la página de inicio de sesión si no está autenticado
    header("Location: index.php");
    exit;
}

// Verificar si el parámetro "id" está presente en la URL
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Consulta para obtener los datos del usuario con el ID proporcionado
    $query = "SELECT ID, Nombre, Apellido, Usuario, Correo, Ocupacion FROM registro WHERE ID = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    // Verificar si el usuario existe
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "Usuario no encontrado.";
        exit;
    }
} else {
    echo "ID no proporcionado.";
    exit;
}

// Si el formulario ha sido enviado, actualizamos los datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir los nuevos datos del formulario
    $nombre = trim($_POST["Nombre"]);
    $apellido = trim($_POST["Apellido"]);
    $usuario_nuevo = trim($_POST["Usuario"]);
    $correo = trim($_POST["Correo"]);
    $ocupacion = $_POST["Ocupacion"]; // Ocupación ahora toma el valor 2 o 3

    // Consulta para actualizar los datos del usuario
    $query = "UPDATE registro SET Nombre = :Nombre, Apellido = :Apellido, Usuario = :Usuario, Correo = :Correo, Ocupacion = :Ocupacion WHERE ID = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":Nombre", $nombre);
    $stmt->bindValue(":Apellido", $apellido);
    $stmt->bindValue(":Usuario", $usuario_nuevo);
    $stmt->bindValue(":Correo", $correo);
    $stmt->bindValue(":Ocupacion", $ocupacion);
    $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<div class='alert success'>Datos actualizados correctamente.</div>";
    } else {
        echo "<div class='alert error'>Error al actualizar los datos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="estilos_Editar.css">
</head>
<body>
    <header>
        <div class="header-content">
            <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['Usuario']); ?></strong>
        </div>
    </header>
<!-- Botón de regreso a principal.php -->
<div class="back-button">
    <a href="principal.php"><button type="button" class="btn">Regresar a la Página Principal</button></a>
</div>

    <section class="form-section">
        <h2>Editar Usuario</h2>
        <form method="POST" class="form-edit">
            <div class="input-box">
                <label for="Nombre">Nombre:</label>
                <input type="text" id="Nombre" name="Nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required>
            </div>
            <div class="input-box">
                <label for="Apellido">Apellido:</label>
                <input type="text" id="Apellido" name="Apellido" value="<?php echo htmlspecialchars($usuario['Apellido']); ?>" required>
            </div>
            <div class="input-box">
                <label for="Usuario">Nombre de Usuario:</label>
                <input type="text" id="Usuario" name="Usuario" value="<?php echo htmlspecialchars($usuario['Usuario']); ?>" required>
            </div>
            <div class="input-box">
                <label for="Correo">Correo Electrónico:</label>
                <input type="email" id="Correo" name="Correo" value="<?php echo htmlspecialchars($usuario['Correo']); ?>" required>
            </div>
            <div class="input-box">
                <label for="Ocupacion">Ocupación:</label>
                <select id="Ocupacion" name="Ocupacion" required>
                    <option value="2" <?php echo ($usuario['Ocupacion'] == '2') ? 'selected' : ''; ?>>Empleado</option>
                    <option value="3" <?php echo ($usuario['Ocupacion'] == '3') ? 'selected' : ''; ?>>Paciente</option>
                </select>
            </div>
            <button type="submit" class="btn">Actualizar Datos</button>
        </form>
    </section>
</body>
</html>
