<?php
include("../Controlador/conexion.php");

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar el token en la base de datos
    $query = "SELECT ID FROM registro WHERE token = :token";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":token", $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Actualizar el estado del usuario a verificado
        $query = "UPDATE registro SET token = NULL, verificado = 1 WHERE token = :token";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(":token", $token);
        $stmt->execute();

        echo "Cuenta verificada con éxito. Ahora puedes iniciar sesión.";
    } else {
        echo "Token inválido o cuenta ya verificada.";
    }
} else {
    echo "No se recibió ningún token.";
}
?>
