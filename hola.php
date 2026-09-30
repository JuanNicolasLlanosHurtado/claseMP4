<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['usuario'];

    // Validamos que el campo no esté vacío
    if (!empty($nombre_usuario)) {
        // Redirigimos a bienvenida.php pasando el nombre codificado en la URL
        header("Location: redirect.php?nombre=" . urlencode($nombre_usuario));
        exit(); // Detiene la ejecución del script actual tras la redirección
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>

    <h1>Formulario de Acceso</h1>

    <form action="" method="POST">
        <label for="usuario">Escribe tu nombre:</label>
        <input type="text" id="usuario" name="usuario" required>
        <button type="submit">Ingresar</button>
    </form>

</body>
</html>