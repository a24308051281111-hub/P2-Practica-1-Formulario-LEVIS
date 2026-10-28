<header class="cabecera">
  <div class="marca">
    <span class="logo">LEVI'S</span>
    <span class="lema">Tienda en línea</span>
  </div>
  <div class="alumna">
    <strong>Lia Bretado Quiroz</strong>
    <span>Grupo 5-J</span>
  </div>
</header>
<?php if (isset($_SESSION["usuario_id"])): ?>
<nav class="barra">
  <span>Hola, <?= e($_SESSION["nombre"]) ?></span>
  <a href="logout.php">Cerrar sesión</a>
</nav>
<?php endif; ?>
