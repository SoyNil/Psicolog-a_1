<?php
include("../Controlador/conexion.php");

$query = "SELECT * FROM registro WHERE verificado = 0 AND fecha_registro <= NOW() - INTERVAL 7 DAY";
$stmt = $pdo->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch()) {
    // Eliminar usuario que no ha verificado su cuenta en 7 días
    $queryDelete = "DELETE FROM registro WHERE id = :id";
    $stmtDelete = $pdo->prepare($queryDelete);
    $stmtDelete->bindValue(":id", $row['id']);
    $stmtDelete->execute();
}
?>
