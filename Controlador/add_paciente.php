<?php
// Conexión a la base de datos
include("conexion.php");

// Verificar que el formulario se envió correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener los datos enviados desde el formulario
        $dni = $_POST['dni'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $precio = $_POST['precio'] ?? null;
        $descuento = $_POST['descuento'] ?? 0; // Si no se envía, se establece en 0 por defecto
        $usuario_id = $_POST['usuario'] ?? null; // ID del psicólogo seleccionado

        // Validar que el psicólogo fue seleccionado
        if (!$usuario_id) {
            echo "Error: Debes seleccionar un psicólogo.";
            exit;
        }

        // Validar los datos
        if (!$dni || !$nombre || !$fecha || !$precio) {
            echo "Error: Por favor, complete todos los campos.";
            exit;
        }

        // Calcular el precio con descuento
        $precio_descuento = $precio - ($precio * $descuento / 100);

        // Insertar el paciente en la base de datos
        $query = "INSERT INTO pacientes (DNI, Nombre, Fecha, Precio, Descuento, Precio_Descuento, Usuario_ID) 
                  VALUES (:dni, :nombre, :fecha, :precio, :descuento, :precio_descuento, :usuario_id)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':fecha' => $fecha,
            ':precio' => $precio,
            ':descuento' => $descuento,
            ':precio_descuento' => $precio_descuento,
            ':usuario_id' => $usuario_id
        ]);

        // Responder con éxito
        echo "Paciente agregado correctamente.";
    } catch (PDOException $e) {
        // Manejar errores de base de datos
        echo "Error al agregar el paciente: " . $e->getMessage();
    }
} else {
    echo "Error: Método no permitido.";
}
