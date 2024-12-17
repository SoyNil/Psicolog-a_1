<?php
session_start();
include("../Controlador/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos actuales del usuario
    $usuario_id = $_SESSION['ID'];
    $query = "SELECT * FROM registro WHERE ID = :usuario_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuarioData) {
        $nombre = $usuarioData['Nombre'];
        $apellido = $usuarioData['Apellido'];
        $usuario = $usuarioData['Usuario'];
        $correo = $usuarioData['Correo'];
    } else {
        echo "Usuario no encontrado.";
        exit();
    }

    // Comprobar si los datos del formulario fueron recibidos correctamente
    if (isset($_POST['Nombre'])) {
        $nombre = $_POST['Nombre'];
    }
    if (isset($_POST['Apellido'])) {
        $apellido = $_POST['Apellido'];
    }
    if (isset($_POST['Usuario'])) {
        $usuario = $_POST['Usuario'];
    }
    if (isset($_POST['Correo'])) {
        $correo = $_POST['Correo'];
    }

    // Validar si la contraseña ha sido proporcionada o no
    if (!empty($_POST['Contrasena'])) {
        $contrasena = password_hash($_POST['Contrasena'], PASSWORD_DEFAULT);
    } else {
        // Si no se proporciona una nueva contraseña, mantenemos la actual
        $contrasena = $usuarioData['Contrasena'];
    }

    // Mostrar los valores para depuración
    echo "Nombre: $nombre, Apellido: $apellido, Usuario: $usuario, Correo: $correo, Contraseña: $contrasena";

    // Actualizar los datos en la base de datos
    $query = "UPDATE registro SET Nombre = :Nombre, Apellido = :Apellido, Usuario = :Usuario, Correo = :Correo, Contrasena = :Contrasena WHERE ID = :usuario_id";
    $stmt = $pdo->prepare($query);

    // Vincular los parámetros
    $stmt->bindValue(":Nombre", $nombre);
    $stmt->bindValue(":Apellido", $apellido);
    $stmt->bindValue(":Usuario", $usuario);
    $stmt->bindValue(":Correo", $correo);
    $stmt->bindValue(":Contrasena", $contrasena);
    $stmt->bindValue(":usuario_id", $usuario_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<script>alert('Perfil actualizado con éxito.'); window.location.href='../Vista/principal.php';</script>";
    } else {
        // Si la ejecución falla, muestra el error y la consulta
        $errorInfo = $stmt->errorInfo();
        echo "<script>alert('Error al actualizar el perfil: " . $errorInfo[2] . "'); window.location.href='../Vista/principal.php';</script>";
    }
}
?>
