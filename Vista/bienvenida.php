<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida</title>
    <script>
        function abrirBot() {
            document.getElementById("chatbot").style.display = "block";
        }
        function cerrarBot() {
            document.getElementById("chatbot").style.display = "none";
        }
    </script>
    <style>
        #chatbot {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 400px;
            background: white;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            padding: 10px;
        }
    </style>
</head>
<body>
    <h2>Bienvenido, <?php echo $_SESSION["usuario"]; ?>!</h2>
    <button onclick="abrirBot()">Iniciar Bot</button>
    <br><a href="logout.php">Cerrar sesión</a>

    <div id="chatbot">
        <button onclick="cerrarBot()">Cerrar</button>
        <iframe src="chatbot.php" width="100%" height="90%"></iframe>
    </div>
</body>
</html>
