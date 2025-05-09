<?php
session_start();
include "../Controlador/conexion.php"; // Incluir la conexión PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = trim($_POST["mensaje"]);

    if (empty($mensaje)) {
        echo "Error: No se puede enviar un mensaje vacío.";
        exit();
    }

    // Obtener nombres de tablas y columnas de la base de datos
    $tablas = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($fila = $stmt->fetch(PDO::FETCH_NUM)) {
        $tablas[] = $fila[0];
    }

    $columnas = [];
    foreach ($tablas as $tabla) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $tabla");
        $stmt->execute();
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columnas[$tabla][] = $fila['Field'];
        }
    }

    // 📌 Detectar pregunta sobre cantidad de usuarios
    if (preg_match("/\b(usuarios|cantidad de usuarios|cuántos usuarios)\b/i", $mensaje)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM registro");
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Actualmente hay " . $fila['total'] . " usuarios registrados.";
        exit();
    }

    // 📌 Detectar pregunta sobre correos
    if (preg_match("/\b(correo de ([\w]+))\b/i", $mensaje, $matches)) {
        $nombre = $matches[2];
        $stmt = $pdo->prepare("SELECT Correo FROM registro WHERE Nombre = ?");
        $stmt->execute([$nombre]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila) {
            echo "El correo de $nombre es: " . $fila['Correo'];
        } else {
            echo "No se encontró el correo de $nombre.";
        }
        exit();
    }

    // 📌 Enviar mensaje a Together AI si no se reconoce la pregunta
    $together_api_key = "d285b1cfe48c461f457e1a3d0241826e4f17d04bc98e5dead7d48c9107912a4e";
    $together_api_url = "https://api.together.xyz/v1/chat/completions";
    
    $data = [
        "model" => "meta-llama/Llama-3.3-70B-Instruct-Turbo-Free",
        "messages" => [
            ["role" => "system", "content" => "Responde preguntas sobre la base de datos local. Aquí están los nombres de las tablas y columnas disponibles: " . implode(", ", array_merge($tablas, array_merge(...array_values($columnas))))],
            ["role" => "user", "content" => $mensaje]
        ],
        "temperature" => 0.5,
        "max_tokens" => 100
    ];

    $ch = curl_init($together_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $together_api_key"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        echo "Error: Código HTTP " . $http_code . " - Respuesta de Together AI: " . $response;
        exit();
    }

    $result = json_decode($response, true);
    $respuesta = $result["choices"][0]["message"]["content"] ?? "Error en la respuesta del bot.";
    echo trim(explode("\n", $respuesta)[0]);
    exit();
}
?>
<link rel="stylesheet" href="../Modelo/estilosChatBot.css">
<!-- 📌 INTERFAZ DEL CHATBOT -->
<div class="chat-container">
    <div class="chat-header">IllaBot</div>
    <div id="chat" class="chat-body"></div>
    <div class="chat-input">
        <input type="text" id="mensaje" placeholder="Escribe un mensaje..." onkeypress="if(event.key === 'Enter') enviarMensaje();">
        <button onclick="enviarMensaje()">Enviar</button>
    </div>
</div>

<script>
function enviarMensaje() {
    var mensaje = document.getElementById("mensaje").value.trim();
    if (mensaje === "") {
        alert("No puedes enviar un mensaje vacío.");
        return;
    }

    var chatBody = document.getElementById("chat");
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "chatbot.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            chatBody.innerHTML += "<p class='user'><strong>Tú:</strong> " + mensaje + "</p>";
            if (xhr.status == 200) {
                chatBody.innerHTML += "<p><strong>Bot:</strong> " + xhr.responseText + "</p>";
            } else {
                chatBody.innerHTML += "<p><strong>Bot:</strong> Error en la respuesta.</p>";
            }
            document.getElementById("mensaje").value = "";
            chatBody.scrollTop = chatBody.scrollHeight; // Auto scroll hacia abajo
        }
    };
    xhr.send("mensaje=" + encodeURIComponent(mensaje));
}
</script>
