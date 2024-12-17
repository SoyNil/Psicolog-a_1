<?php
header('Content-Type: application/json');

// Conexión a la base de datos
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = $_POST['usuarioId'];

    if (empty($usuarioId)) {
        echo json_encode(['status' => 'error', 'message' => 'No se recibió el ID del usuario.']);
        exit;
    }

    try {
        // Eliminar los registros dependientes en la tabla 'pacientes'
        $sqlDeletePacientes = "DELETE FROM pacientes WHERE Usuario_ID = :usuarioId";
        $stmt = $pdo->prepare($sqlDeletePacientes);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        // Ahora eliminar el usuario de la tabla 'registro'
        $sqlDeleteUsuario = "DELETE FROM registro WHERE ID = :usuarioId";
        $stmt = $pdo->prepare($sqlDeleteUsuario);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado exitosamente.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
