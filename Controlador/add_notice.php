<?php
session_start();
include("../Controlador/conexion.php");

header('Content-Type: application/json');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['Usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
    exit;
}

// Obtén los datos enviados desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notice']) || trim($data['notice']) === '') {
    echo json_encode(['success' => false, 'error' => 'El aviso no puede estar vacío.']);
    exit;
}

$notice = trim($data['notice']);
$usuario_id = $_SESSION['ID']; // ID del usuario autenticado
$fecha = date("Y-m-d H:i:s");

// Inserta el aviso en la base de datos
$query = "INSERT INTO avisos (Usuario_ID, aviso, fecha) VALUES (:usuario_id, :aviso, :fecha)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->bindParam(':aviso', $notice);
$stmt->bindParam(':fecha', $fecha);

if ($stmt->execute()) {
    // Respuesta JSON con los datos del aviso recién creado
    echo json_encode([
        'success' => true,
        'notice' => [
            'usuario_nombre' => $_SESSION['Usuario'],
            'fecha' => $fecha,
            'aviso' => $notice
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al insertar el aviso en la base de datos.']);
}
?>
