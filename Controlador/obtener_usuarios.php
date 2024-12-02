<?php
include("conexion.php");

try {
    $query = "SELECT ID, Usuario FROM registro";
    $stmt = $pdo->query($query);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
