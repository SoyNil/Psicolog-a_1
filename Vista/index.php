<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de inicio</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <link rel="stylesheet" href="../Modelo/estilos.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <section class="form-main">
        <div class="form-conte">
            <div class="box">
                <h2>INICIAR SESION</h2>           
                <form method="POST">
                <div class="input-box">
                    <label for="Nombre">Nombre:</label><br>
                    <input type="hidden" name="valornombre" value="si" />
                    <input class="input-control" type="text" id="Nombre" name="Nombre" placeholder="Ingrese su nombre" required>
                </div>  
                <div class="input-box">
                    <label for="Usuario">Nombre de usuario:</label><br>
                    <input type="hidden" name="valorusuario" value="si" />
                    <input class="input-control" type="text" id="Usuario" name="Usuario" placeholder="Ingrese su nombre de usuario" required>
                </div>                       
                <div class="input-box">
                    <label for="Correo">Correo Electrónico:</label><br>
                    <input class="input-control" type="email" id="Correo" name="Correo" placeholder="Ingrese su correo electrónico" required>
                </div>                      
                <div class="input-box">
                    <label for="Contrasena">Contraseña:</label><br>
                    <div class="password-container">
                        <input class="input-control" type="password" id="Contrasena" name="Contrasena" placeholder="Ingrese su contraseña" required>
                        <button type="button" id="togglePassword" class="toggle-password" style="display: none;">
                            <i class="fas fa-eye"></i> <!-- Ícono de ojo de Font Awesome -->
                        </button>
                    </div>
                </div>
                    <input class="btn" type="submit" value="Iniciar Sesion"><br><br>
                    <p><label>¿No tienes una cuenta? Cree una aquí</label></p>
                    <a href="registro.php"><input class="btn" type="button" value="Registrarse"></a><br><br>   
                </form><div id="mostrar_mensaje"></div>
            </div>
        </div>
    </section>
<?php
include("../Controlador/conexion.php");
session_start();
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nombre = trim($_POST["Nombre"]);
    $usuario = trim($_POST["Usuario"]);
    $correo = trim($_POST["Correo"]);
    $contra = trim($_POST["Contrasena"]);
    $query = "SELECT ID,Correo,Nombre,Contrasena,Usuario, Ocupacion FROM registro WHERE Nombre=:Nombre and Contrasena=:Contrasena and Correo = :Correo and Usuario = :Usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":Contrasena", $contra);
    $stmt->bindValue(":Correo", $correo);
    $stmt->bindValue(":Nombre", $nombre);
    $stmt->bindValue(":Usuario", $usuario);
    $stmt->execute();
    if($stmt->rowCount() > 0){
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['Usuario'] = $user['Usuario'];
        $_SESSION['Ocupacion'] = $user['Ocupacion'];
        $_SESSION['ID'] = $user['ID'];
        header("Location: principal.php");
    }else{?>
        <script>
            var parametros=
            {
                "mensaje":"error",
            };
            $.ajax({
                data: parametros,
                url:'../Controlador/error.php',
                type:'POST',
                beforesend:function()
                {
                    $('#mostrar_mensaje').html("Mensaje antes de enviar");
                },
                success:function(mensaje)
                {
                    $('#mostrar_mensaje').html(mensaje);
                }
            });
        </script><?php
    }
}
?>
    <script>
    // Obtener los elementos
    const passwordField = document.getElementById('Contrasena');
    const togglePasswordButton = document.getElementById('togglePassword');

    // Mostrar u ocultar el ícono según si hay texto en el input
    passwordField.addEventListener('input', function () {
        if (passwordField.value.length > 0) {
            togglePasswordButton.style.display = 'block';  // Mostrar el ícono si hay texto
        } else {
            togglePasswordButton.style.display = 'none';   // Ocultar el ícono si no hay texto
        }
    });

    // Función para alternar la visibilidad de la contraseña
    togglePasswordButton.addEventListener('click', function () {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;

        // Cambiar el ícono del botón
        const icon = this.querySelector('i');
        if (type === 'password') {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }); 
    </script>
</body>
</html>