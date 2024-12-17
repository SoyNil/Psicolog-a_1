<?php
header('Content-Type: application/json');

// Conexión a la base de datos
require_once 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = $_POST['usuarioId'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $ocupacion = $_POST['ocupacion'];

    // Validar que no haya campos vacíos
    if (empty($usuarioId) || empty($nombre) || empty($apellido) || empty($usuario) || empty($correo) || empty($ocupacion)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    try {
        // Consulta SQL para actualizar el usuario
        $sql = "UPDATE registro SET 
                Nombre = :nombre, 
                Apellido = :apellido, 
                Usuario = :usuario, 
                Correo = :correo, 
                Contrasena = :contrasena, 
                Ocupacion = :ocupacion
                WHERE ID = :usuarioId";
        
        // Preparar la consulta
        $stmt = $pdo->prepare($sql);
        
        // Bind de los parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':contrasena', $contrasena);
        $stmt->bindParam(':ocupacion', $ocupacion);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Usuario modificado exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al modificar el usuario.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
