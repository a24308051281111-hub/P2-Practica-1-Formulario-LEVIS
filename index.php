<?php
require "config.php";

// Solo usuarios con sesión iniciada
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
$uid = $_SESSION["usuario_id"];
$mensaje = "";
$tipo = "";
$tallas = ["XS", "S", "M", "L", "XL"];

// Guardar pedido
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $producto  = (int)($_POST["producto"] ?? 0);
    $talla     = $_POST["talla"] ?? "";
    $cantidad  = (int)($_POST["cantidad"] ?? 0);
    $direccion = trim($_POST["direccion"] ?? "");
    $notas     = trim($_POST["notas"] ?? "");

    $p = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
    $p->bind_param("i", $producto);
    $p->execute();
    $fila = $p->get_result()->fetch_assoc();

    if (!$fila || !in_array($talla, $tallas, true) || $cantidad < 1 || $cantidad > 20 || $direccion === "") {
        $mensaje = "Elige una prenda, talla, cantidad (1–20) y escribe la dirección.";
        $tipo = "error";
    } else {
        $ins = $conexion->prepare(
            "INSERT INTO pedidos (usuario_id, producto_id, talla, cantidad, precio_unitario, direccion, notas)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param("iisidss", $uid, $producto, $talla, $cantidad, $fila["precio"], $direccion, $notas);
        $ins->execute();
        $mensaje = "Pedido guardado.";
        $tipo = "exito";
    }
}

$productos = $conexion->query("SELECT id, nombre, descripcion, precio FROM productos ORDER BY id");

$q = $conexion->prepare(
    "SELECT p.creado, pr.nombre, p.talla, p.cantidad, p.precio_unitario
     FROM pedidos p JOIN productos pr ON pr.id = p.producto_id
     WHERE p.usuario_id = ? ORDER BY p.id DESC"
);
$q->bind_param("i", $uid);
$q->execute();
$pedidos = $q->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Levi's · Tienda en línea</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
  <?php include "encabezado.php"; ?>
  <main>
    <section class="tarjeta">
      <h2>Haz tu pedido</h2>
      <?php if ($mensaje): ?><p class="aviso <?= $tipo ?>"><?= e($mensaje) ?></p><?php endif; ?>
      <form method="post">
        <p class="paso">1. Elige tu prenda</p>
        <div class="catalogo">
          <?php while ($r = $productos->fetch_assoc()): ?>
            <label class="prenda">
              <input type="radio" name="producto" value="<?= $r["id"] ?>" data-precio="<?= $r["precio"] ?>" required>
              <span class="prenda-caja">
                <strong><?= e($r["nombre"]) ?></strong>
                <small><?= e($r["descripcion"]) ?></small>
                <span class="precio">$<?= number_format($r["precio"], 2) ?></span>
              </span>
            </label>
          <?php endwhile; ?>
        </div>

        <p class="paso">2. Talla y cantidad</p>
        <div class="dos">
          <div>
            <label for="talla">Talla</label>
            <select id="talla" name="talla" required>
              <?php foreach ($tallas as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="cantidad">Cantidad</label>
            <input id="cantidad" name="cantidad" type="number" min="1" max="20" value="1" required>
          </div>
        </div>

        <p class="paso">3. Entrega</p>
        <label for="direccion">Dirección de entrega</label>
        <input id="direccion" name="direccion" maxlength="250" required>
        <label for="notas">Notas (opcional)</label>
        <textarea id="notas" name="notas" rows="2" maxlength="500"></textarea>

        <div class="total">Total: <strong id="total">$0.00</strong></div>
        <button class="boton" type="submit">Guardar pedido</button>
      </form>
    </section>

    <section class="tarjeta">
      <h2>Mis pedidos</h2>
      <?php if (!$pedidos): ?>
        <p class="tenue">Aún no tienes pedidos. Elige una prenda para crear el primero.</p>
      <?php else: ?>
        <div class="tabla">
          <table>
            <thead><tr><th>Fecha</th><th>Prenda</th><th>Talla</th><th>Cant.</th><th>Total</th></tr></thead>
            <tbody>
              <?php foreach ($pedidos as $p): ?>
                <tr>
                  <td><?= e(substr($p["creado"], 0, 16)) ?></td>
                  <td><?= e($p["nombre"]) ?></td>
                  <td><?= e($p["talla"]) ?></td>
                  <td><?= (int)$p["cantidad"] ?></td>
                  <td>$<?= number_format($p["precio_unitario"] * $p["cantidad"], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>
  <script src="js/app.js"></script>
</body>
</html>
