// Calcula el total según la prenda elegida y la cantidad
const cantidad = document.getElementById("cantidad");
const total = document.getElementById("total");
const opciones = document.querySelectorAll('input[name="producto"]');

function actualizarTotal() {
  const elegida = document.querySelector('input[name="producto"]:checked');
  const precio = elegida ? parseFloat(elegida.dataset.precio) : 0;
  const n = parseInt(cantidad.value, 10) || 0;
  total.textContent = "$" + (precio * n).toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

opciones.forEach(o => o.addEventListener("change", actualizarTotal));
cantidad.addEventListener("input", actualizarTotal);
actualizarTotal();
