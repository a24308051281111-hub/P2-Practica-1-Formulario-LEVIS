<?php
require "config.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = strtolower(trim($_POST["correo"] ?? ""));
    $clave  = $_POST["clave"] ?? "";

    if ($nombre === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Escribe un nombre y un correo válido.";
    } elseif (strlen($clave) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $existe = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $existe->bind_param("s", $correo);
        $existe->execute();
        if ($existe->get_result()->num_rows > 0) {
            $error = "Ese correo ya está registrado.";
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $ins = $conexion->prepare("INSERT INTO usuarios (nombre, correo, clave) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $nombre, $correo, $hash);
            $ins->execute();
            $_SESSION["usuario_id"] = $conexion->insert_id;
            $_SESSION["nombre"] = $nombre;
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear cuenta · Levi's</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
  <?php include "encabezado.php"; ?>
  <main class="angosto">
    <section class="tarjeta">
      <h2>Crear cuenta</h2>
      <?php if ($error): ?><p class="aviso error"><?= e($error) ?></p><?php endif; ?>
      <form method="post">
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" required value="<?= e($_POST["nombre"] ?? "") ?>">
        <label for="correo">Correo</label>
        <input id="correo" name="correo" type="email" required value="<?= e($_POST["correo"] ?? "") ?>">
        <label for="clave">Contraseña</label>
        <input id="clave" name="clave" type="password" minlength="6" required>
        <button class="boton" type="submit">Crear cuenta</button>
      </form>
      <p class="tenue">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </section>
  </main>
</body>
</html>
