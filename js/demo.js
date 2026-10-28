const PRODUCTOS = [
  { id: 1, nombre: "Jeans 501 Original", descripcion: "Corte recto clásico en mezclilla azul", precio: 1299 },
  { id: 2, nombre: "Jeans 511 Slim", descripcion: "Corte ajustado con tela elástica", precio: 1199 },
  { id: 3, nombre: "Playera Logo", descripcion: "Algodón suave con logotipo al frente", precio: 499 },
  { id: 4, nombre: "Chamarra Trucker", descripcion: "Chamarra de mezclilla con botones metálicos", precio: 1899 },
  { id: 5, nombre: "Camisa de Mezclilla", descripcion: "Manga larga con bolsillos frontales", precio: 999 },
  { id: 6, nombre: "Sudadera con Capucha", descripcion: "Felpa interior y bolsillo canguro", precio: 1099 }
];
const TALLAS = ["XS", "S", "M", "L", "XL"];

const $ = id => document.getElementById(id);
const leer = (k, d) => { try { return JSON.parse(localStorage.getItem(k)) ?? d; } catch { return d; } };
const guardar = (k, v) => localStorage.setItem(k, JSON.stringify(v));
const dinero = n => "$" + n.toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

async function cifrar(texto) {
  const b = await crypto.subtle.digest("SHA-256", new TextEncoder().encode(texto));
  return [...new Uint8Array(b)].map(x => x.toString(16).padStart(2, "0")).join("");
}

function aviso(texto, tipo) {
  const el = $("aviso");
  el.textContent = texto;
  el.className = "aviso " + tipo;
  el.hidden = false;
}

const pagina = document.body.dataset.pagina;

if (pagina === "registro") {
  $("formulario").addEventListener("submit", async ev => {
    ev.preventDefault();
    const nombre = $("nombre").value.trim();
    const correo = $("correo").value.trim().toLowerCase();
    const clave = $("clave").value;
    const usuarios = leer("usuarios", []);
    if (usuarios.some(u => u.correo === correo)) return aviso("Ese correo ya está registrado.", "error");
    usuarios.push({ nombre, correo, clave: await cifrar(clave) });
    guardar("usuarios", usuarios);
    guardar("sesion", { nombre, correo });
    location.href = "index.html";
  });
}

if (pagina === "login") {
  $("formulario").addEventListener("submit", async ev => {
    ev.preventDefault();
    const correo = $("correo").value.trim().toLowerCase();
    const clave = await cifrar($("clave").value);
    const u = leer("usuarios", []).find(x => x.correo === correo && x.clave === clave);
    if (!u) return aviso("Correo o contraseña incorrectos.", "error");
    guardar("sesion", { nombre: u.nombre, correo: u.correo });
    location.href = "index.html";
  });
}

if (pagina === "tienda") {
  const sesion = leer("sesion", null);
  if (!sesion) {
    location.href = "login.html";
  } else {
    $("saludo").textContent = "Hola, " + sesion.nombre;
    $("salir").addEventListener("click", () => {
      localStorage.removeItem("sesion");
      location.href = "login.html";
    });

    $("catalogo").innerHTML = PRODUCTOS.map(p => `
      <label class="prenda">
        <input type="radio" name="producto" value="${p.id}" required>
        <span class="prenda-caja">
          <strong>${p.nombre}</strong>
          <small>${p.descripcion}</small>
          <span class="precio">${dinero(p.precio)}</span>
        </span>
      </label>`).join("");
    $("talla").innerHTML = TALLAS.map(t => `<option value="${t}">${t}</option>`).join("");

    const actualizarTotal = () => {
      const marcado = document.querySelector('input[name="producto"]:checked');
      const p = marcado ? PRODUCTOS.find(x => x.id === Number(marcado.value)) : null;
      $("total").textContent = dinero((p ? p.precio : 0) * (parseInt($("cantidad").value, 10) || 0));
    };
    document.querySelectorAll('input[name="producto"]').forEach(o => o.addEventListener("change", actualizarTotal));
    $("cantidad").addEventListener("input", actualizarTotal);
    actualizarTotal();

    const mostrarPedidos = () => {
      const mios = leer("pedidos", []).filter(p => p.correo === sesion.correo).reverse();
      $("vacio").hidden = mios.length > 0;
      $("tabla").hidden = mios.length === 0;
      const cuerpo = $("pedidos");
      cuerpo.replaceChildren(...mios.map(p => {
        const tr = document.createElement("tr");
        [p.fecha, p.prenda, p.talla, p.cantidad, dinero(p.total)].forEach(v => {
          const td = document.createElement("td");
          td.textContent = v;
          tr.append(td);
        });
        return tr;
      }));
    };
    mostrarPedidos();

    $("formulario").addEventListener("submit", ev => {
      ev.preventDefault();
      const marcado = document.querySelector('input[name="producto"]:checked');
      const p = PRODUCTOS.find(x => x.id === Number(marcado.value));
      const cantidad = parseInt($("cantidad").value, 10);
      const pedidos = leer("pedidos", []);
      pedidos.push({
        correo: sesion.correo,
        fecha: new Date().toLocaleString("es-MX", { dateStyle: "short", timeStyle: "short" }),
        prenda: p.nombre,
        talla: $("talla").value,
        cantidad,
        total: p.precio * cantidad,
        direccion: $("direccion").value.trim(),
        notas: $("notas").value.trim()
      });
      guardar("pedidos", pedidos);
      aviso("Pedido guardado.", "exito");
      $("formulario").reset();
      actualizarTotal();
      mostrarPedidos();
    });
  }
}
