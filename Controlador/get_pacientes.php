<?php
session_start();
include("../Controlador/conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['Usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado."]);
    exit;
}

$usuario_id = $_SESSION['ID'];
$ocupacion = $_SESSION['Ocupacion']; // Obtener la ocupación del usuario desde la sesión
$range = isset($_GET['range']) ? $_GET['range'] : 'all'; // Para el filtro por rango de fechas, si existe

// Variables para rango personalizado de fechas
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

try {
    if ($ocupacion == 1) {
        // Si es administrador, obtiene todos los pacientes
        if ($range == 'week') {
            $dateLimit = date('Y-m-d', strtotime('-7 days'));
            $query = "SELECT * FROM pacientes WHERE fecha >= :dateLimit";
        } elseif ($range == 'month') {
            $dateLimit = date('Y-m-d', strtotime('-1 month'));
            $query = "SELECT * FROM pacientes WHERE fecha >= :dateLimit";
        } elseif ($range == 'year') {
            $dateLimit = date('Y-m-d', strtotime('-1 year'));
            $query = "SELECT * FROM pacientes WHERE fecha >= :dateLimit";
        } elseif ($startDate && $endDate) {
            $query = "SELECT * FROM pacientes WHERE fecha BETWEEN :start_date AND :end_date";
        } else {
            $query = "SELECT * FROM pacientes";
        }
    } else {
        // Si no es administrador, solo obtiene los pacientes vinculados a su ID
        if ($range == 'week') {
            $dateLimit = date('Y-m-d', strtotime('-7 days'));
            $query = "SELECT * FROM pacientes WHERE Usuario_ID = :usuario_id AND fecha >= :dateLimit";
        } elseif ($range == 'month') {
            $dateLimit = date('Y-m-d', strtotime('-1 month'));
            $query = "SELECT * FROM pacientes WHERE Usuario_ID = :usuario_id AND fecha >= :dateLimit";
        } elseif ($range == 'year') {
            $dateLimit = date('Y-m-d', strtotime('-1 year'));
            $query = "SELECT * FROM pacientes WHERE Usuario_ID = :usuario_id AND fecha >= :dateLimit";
        } elseif ($startDate && $endDate) {
            $query = "SELECT * FROM pacientes WHERE Usuario_ID = :usuario_id AND fecha BETWEEN :start_date AND :end_date";
        } else {
            $query = "SELECT * FROM pacientes WHERE Usuario_ID = :usuario_id";
        }
    }

    $stmt = $pdo->prepare($query);

    // Asignar parámetros según corresponda
    if ($ocupacion != 1) {
        $stmt->bindParam(':usuario_id', $usuario_id);
    }

    if (isset($dateLimit)) {
        $stmt->bindParam(':dateLimit', $dateLimit);
    } elseif ($startDate && $endDate) {
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
    }

    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($pacientes);
} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener los datos: " . $e->getMessage()]);
}
?>
