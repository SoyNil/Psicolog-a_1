<?php
// Habilitar la visualización de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asegurarse de que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Incluir archivo de conexión
    include("conexion.php");

    // Obtener los datos de la solicitud
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Verificar si se proporcionó el ID del paciente
    if (isset($inputData['id'])) {
        // Obtener el ID del paciente
        $id = $inputData['id'];

        // Crear la consulta SQL para eliminar al paciente
        $sql = "DELETE FROM pacientes WHERE ID = :id";

        // Preparar la sentencia SQL usando PDO
        try {
            // Preparar la consulta
            $stmt = $pdo->prepare($sql);

            // Ejecutar la sentencia, vinculando el ID del paciente
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Si la eliminación fue exitosa, enviar una respuesta JSON
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Paciente eliminado con éxito.']);
            } else {
                // Si la eliminación falló
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al eliminar al paciente.']);
            }
        } catch (PDOException $e) {
            // Si ocurre un error al preparar o ejecutar la consulta
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al procesar la consulta: ' . $e->getMessage()]);
        }
    } else {
        // Si no se proporcionó un ID válido
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se proporcionó un ID válido.']);
    }
} else {
    // Si la solicitud no es POST
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
