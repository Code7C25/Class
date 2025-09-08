const usuarios = [
    "Ana Gómez", "Carlos Torres", "Daniela Ruiz", "Esteban López",
    "Fernanda Díaz", "Gabriel Silva", "Helena Castro", "Iván Morales",
    "Jazmín Ortega", "Lucía Fernández", "Mateo Ramírez", "Noelia Vázquez",
    "Óscar Herrera", "Paula Medina"
  ];
  
  const input = document.getElementById("search-input");
  const suggestionList = document.getElementById("suggestions");
  
  input.addEventListener("input", () => {
    const value = input.value.toLowerCase().trim();
  
    if (value === "") {
      suggestionList.classList.add("hidden");
      suggestionList.innerHTML = "";
      return;
    }
  
    const resultados = usuarios.filter(usuario =>
      usuario.toLowerCase().includes(value)
    );
  
    if (resultados.length === 0) {
      suggestionList.innerHTML = "<li>No se encontraron resultados</li>";
    } else {
      suggestionList.innerHTML = resultados.map(usuario => `<li>${usuario}</li>`).join("");
    }
  
    suggestionList.classList.remove("hidden");
  });
  
  suggestionList.addEventListener("click", (e) => {
    if (e.target.tagName === "LI") {
      input.value = e.target.textContent;
      suggestionList.classList.add("hidden");
    }
  });
  