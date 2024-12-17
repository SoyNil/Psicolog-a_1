<?php
include("conexion.php");

try {
    // Verificar si el ID del usuario ha sido enviado
    if (isset($_GET['id'])) {
        $usuario_id = $_GET['id'];

        // Recuperar datos del usuario específico
        $query = "SELECT ID, Nombre, Usuario, Apellido, Correo, Contrasena, Ocupacion FROM registro WHERE ID = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el usuario existe
        if ($usuario) {
            echo json_encode($usuario); // Devuelve los datos del usuario
        } else {
            echo json_encode(['error' => 'Usuario no encontrado.']); // Error si no encuentra al usuario
        }
    } else {
        // Si no se proporciona un ID, se devuelven todos los psicólogos (ocupación = 2)
        $query = "SELECT ID, Nombre, Usuario, Apellido, Correo, Ocupacion FROM registro WHERE Ocupacion = 2";
        $stmt = $pdo->query($query);
        $psicologos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si hay psicólogos, devuelve los datos; si no, un arreglo vacío
        if ($psicologos) {
            echo json_encode($psicologos);
        } else {
            echo json_encode([]); // Si no hay psicólogos, devuelve un arreglo vacío
        }
    }
} catch (Exception $e) {
    // En caso de error en la consulta, devuelve el mensaje de error
    echo json_encode(['error' => $e->getMessage()]);
}
?>
