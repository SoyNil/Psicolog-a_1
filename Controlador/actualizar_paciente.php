<?php
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Revisar si se está actualizando un switch (Atendido o Historial_Clinico)
    if (isset($_POST['id'], $_POST['columna'], $_POST['valor'])) {
        $id = intval($_POST['id']);
        $columna = $_POST['columna']; // Puede ser 'Atendido' o 'Historial_Clinico'
        $valor = intval($_POST['valor']);

        // Validar la columna
        if (in_array($columna, ['Atendido', 'Historial_Clinico'])) {
            try {
                $query = "UPDATE pacientes SET $columna = :valor WHERE ID = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':valor', $valor, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo "Columna '$columna' actualizada correctamente.";
                } else {
                    echo "Error al actualizar la columna '$columna'.";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Columna inválida.";
        }
        exit;
    }

    $id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : null;
    $dni = isset($_POST['dni']) ? $_POST['dni'] : null;
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : null;
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : null;
    $descuento = isset($_POST['descuento']) ? floatval($_POST['descuento']) : null;
    $usuario_id = isset($_POST['usuario']) ? intval($_POST['usuario']) : null;

    if ($id && $dni && $nombre && $fecha && $precio !== null) {
        try {
            $query = "UPDATE pacientes SET DNI = :dni, Nombre = :nombre, Fecha = :fecha, Precio = :precio";

            // Solo actualiza los campos extra si están definidos
            if ($descuento !== null) {
                $query .= ", Descuento = :descuento";
            }
            if ($usuario_id !== null) {
                $query .= ", Usuario_ID = :usuario_id";
            }

            $query .= " WHERE ID = :id";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':dni', $dni);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($descuento !== null) {
                $stmt->bindParam(':descuento', $descuento);
            }
            if ($usuario_id !== null) {
                $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            }

            if ($stmt->execute()) {
                echo "Paciente actualizado correctamente.";
            } else {
                echo "Error al actualizar el paciente.";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Datos inválidos.";
    }
}
?>
