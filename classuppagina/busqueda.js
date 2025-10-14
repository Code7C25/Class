// Simulamos una "base de datos" de usuarios (podés modificar o ampliar)
const usuariosSimulados = [
  { nombre: "Lucía Pérez", usuario: "lucia_pz", foto: "https://i.pravatar.cc/150?img=1" },
  { nombre: "Mariano López", usuario: "marianl", foto: "https://i.pravatar.cc/150?img=2" },
  { nombre: "Carla Romero", usuario: "carlarom", foto: "https://i.pravatar.cc/150?img=3" },
  { nombre: "Gonzalo Díaz", usuario: "gon_dz", foto: "https://i.pravatar.cc/150?img=4" },
  { nombre: "Sofía Herrera", usuario: "sofih", foto: "https://i.pravatar.cc/150?img=5" }
];

const input = document.getElementById("search-input");
const resultados = document.getElementById("resultados");

input.addEventListener("input", () => {
  const query = input.value.toLowerCase().trim();
  resultados.innerHTML = "";

  if (query === "") {
    resultados.innerHTML = `<p class="empty-msg">Empieza a escribir para buscar usuarios...</p>`;
    return;
  }

  const filtrados = usuariosSimulados.filter(u =>
    u.nombre.toLowerCase().includes(query) || u.usuario.toLowerCase().includes(query)
  );

  if (filtrados.length === 0) {
    resultados.innerHTML = `<p class="empty-msg">No se encontraron usuarios 😕</p>`;
    return;
  }

  filtrados.forEach(u => {
    const div = document.createElement("div");
    div.classList.add("usuario-card");
    div.innerHTML = `
      <div class="usuario-info">
        <img src="${u.foto}" class="usuario-foto" alt="Foto de ${u.nombre}">
        <div>
          <p class="usuario-nombre">${u.nombre}</p>
          <p>@${u.usuario}</p>
        </div>
      </div>
      <button class="add-btn">Agregar</button>
    `;

    div.querySelector(".add-btn").addEventListener("click", () => agregarAmigo(u));
    resultados.appendChild(div);
  });
});

function agregarAmigo(usuario) {
  let amigos = JSON.parse(localStorage.getItem("amigos")) || [];
  const existe = amigos.some(a => a.usuario === usuario.usuario);

  if (existe) {
    alert(`${usuario.nombre} ya está en tu lista de amigos.`);
    return;
  }

  amigos.push(usuario);
  localStorage.setItem("amigos", JSON.stringify(amigos));
  alert(`${usuario.nombre} se agregó a tus amigos 👥`);
}
