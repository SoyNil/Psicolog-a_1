<?php
session_start();
include("../Controlador/conexion.php"); // Asegúrate de que la ruta sea correcta

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['Usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado."]);
    exit;
}

// Consulta SQL para obtener todos los avisos
$query = "SELECT avisos.*, registro.Usuario AS usuario_nombre  
          FROM avisos 
          LEFT JOIN registro ON avisos.Usuario_ID = registro.ID
          ORDER BY avisos.fecha DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Obtener los resultados
$avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se encontraron avisos
if (!$avisos) {
    echo json_encode(["error" => "No se encontraron avisos."]);
    exit;
}

// Devolver los avisos en formato JSON
header('Content-Type: application/json');
echo json_encode($avisos);
?>
