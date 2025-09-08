document.addEventListener("DOMContentLoaded", () => {
  const calendar = document.getElementById("calendar");
  const eventList = document.getElementById("event-list");
  const addEventBtn = document.getElementById("add-event");
  const eventTitleInput = document.getElementById("event-title");
  const eventDateInput = document.getElementById("event-date");
  const eventTimeInput = document.getElementById("event-time");
  const eventDescInput = document.getElementById("event-desc");
  const monthYear = document.getElementById("month-year");
  const prevMonthBtn = document.getElementById("prev-month");
  const nextMonthBtn = document.getElementById("next-month");

  // Modal
  const modal = document.getElementById("event-modal");
  const modalClose = document.querySelector(".close");
  const modalEventList = document.getElementById("modal-event-list");

  let currentDate = new Date();

  // Cargar eventos desde LocalStorage
  let events = JSON.parse(localStorage.getItem("events")) || [];

  // Guardar en LocalStorage
  function saveEvents() {
    localStorage.setItem("events", JSON.stringify(events));
  }

  // Renderizar calendario
  function renderCalendar() {
    calendar.innerHTML = "";

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const options = { month: "long", year: "numeric" };
    monthYear.textContent = currentDate.toLocaleDateString("es-ES", options);

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const adjustedFirstDay = (firstDay === 0 ? 6 : firstDay - 1);

    for (let i = 0; i < adjustedFirstDay; i++) {
      const empty = document.createElement("div");
      calendar.appendChild(empty);
    }

    for (let i = 1; i <= daysInMonth; i++) {
      const day = document.createElement("div");
      day.classList.add("day");
      day.textContent = i;

      const fullDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;

      const dayEvents = events.filter(event => event.date === fullDate);
      if (dayEvents.length > 0) {
        day.classList.add("event-day");
        const dot = document.createElement("div");
        dot.classList.add("event-dot");
        day.appendChild(dot);

        day.addEventListener("click", () => openModal(fullDate));
      }

      calendar.appendChild(day);
    }
  }

  // Renderizar lista general
  function renderEvents() {
    eventList.innerHTML = "";
    events.forEach(event => {
      const li = document.createElement("li");
      li.innerHTML = `<strong>${event.date}</strong> ${event.time ? "⏰ " + event.time : ""} - ${event.title}`;
      if (event.desc) {
        const desc = document.createElement("div");
        desc.classList.add("event-details");
        desc.textContent = `📝 ${event.desc}`;
        li.appendChild(desc);
      }
      eventList.appendChild(li);
    });
    renderCalendar();
  }

  // Abrir modal con eventos de un día
  function openModal(date) {
    modal.style.display = "block";
    modalEventList.innerHTML = "";

    const dayEvents = events.filter((event, index) => {
      event.index = index;
      return event.date === date;
    });

    if (dayEvents.length > 0) {
      dayEvents.forEach(event => {
        const li = document.createElement("li");
        li.innerHTML = `<strong>${event.title}</strong> ${event.time ? "⏰ " + event.time : ""}`;
        
        if (event.desc) {
          const desc = document.createElement("div");
          desc.classList.add("event-details");
          desc.textContent = event.desc;
          li.appendChild(desc);
        }

        const actions = document.createElement("div");
        actions.classList.add("event-actions");

        const editBtn = document.createElement("button");
        editBtn.textContent = "Editar";
        editBtn.classList.add("event-btn", "edit-btn");
        editBtn.addEventListener("click", () => editEvent(event.index));

        const deleteBtn = document.createElement("button");
        deleteBtn.textContent = "Eliminar";
        deleteBtn.classList.add("event-btn", "delete-btn");
        deleteBtn.addEventListener("click", () => deleteEvent(event.index, date));

        actions.appendChild(editBtn);
        actions.appendChild(deleteBtn);
        li.appendChild(actions);

        modalEventList.appendChild(li);
      });
    } else {
      const li = document.createElement("li");
      li.textContent = "No hay eventos";
      modalEventList.appendChild(li);
    }
  }

  // Eliminar evento
  function deleteEvent(index, date) {
    events.splice(index, 1);
    saveEvents();
    renderEvents();
    openModal(date);
  }

  // Editar evento
  function editEvent(index) {
    const event = events[index];

    eventTitleInput.value = event.title;
    eventDateInput.value = event.date;
    eventTimeInput.value = event.time || "";
    eventDescInput.value = event.desc || "";

    addEventBtn.textContent = "Actualizar evento";

    addEventBtn.onclick = () => {
      const newTitle = eventTitleInput.value.trim();
      const newDate = eventDateInput.value;
      const newTime = eventTimeInput.value;
      const newDesc = eventDescInput.value.trim();

      if (newTitle && newDate) {
        events[index] = { title: newTitle, date: newDate, time: newTime, desc: newDesc };
        saveEvents();
        renderEvents();

        eventTitleInput.value = "";
        eventDateInput.value = "";
        eventTimeInput.value = "";
        eventDescInput.value = "";
        addEventBtn.textContent = "Agregar evento";
        addEventBtn.onclick = addEventHandler;
      }
    };
  }

  // Handler normal de agregar
  function addEventHandler() {
    const title = eventTitleInput.value.trim();
    const date = eventDateInput.value;
    const time = eventTimeInput.value;
    const desc = eventDescInput.value.trim();

    if (title && date) {
      events.push({ title, date, time, desc });
      saveEvents();
      renderEvents();

      eventTitleInput.value = "";
      eventDateInput.value = "";
      eventTimeInput.value = "";
      eventDescInput.value = "";
    }
  }

  addEventBtn.onclick = addEventHandler;

  // Cerrar modal
  modalClose.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  // Navegación
  prevMonthBtn.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  });

  nextMonthBtn.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  });

  // Inicializar
  renderEvents();
});
// Registro
document.getElementById("registerForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("regUsername").value;
  const email = document.getElementById("regEmail").value;
  const password = document.getElementById("regPassword").value;

  const res = await fetch("/register", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, email, password })
  });

  const data = await res.json();
  alert(JSON.stringify(data));
});

// Login
document.getElementById("loginForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;

  const res = await fetch("/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password })
  });

  const data = await res.json();
  if (data.success) {
    localStorage.setItem("userId", data.user.id);
    window.location.href = "profile.html";
  } else {
    alert(data.error);
  }
});

// Perfil
if (window.location.pathname.endsWith("profile.html")) {
  const userId = localStorage.getItem("userId");

  if (userId) {
    fetch(`/profile/${userId}`)
      .then(res => res.json())
      .then(user => {
        document.getElementById("profile").innerHTML = `
          <p><b>Usuario:</b> ${user.username}</p>
          <p><b>Email:</b> ${user.email}</p>
          <p><b>Nombre:</b> ${user.nombre || ""}</p>
          <p><b>Apellido:</b> ${user.apellido || ""}</p>
          <img src="${user.foto || ""}" width="100">
        `;
      });
  }

  document.getElementById("editForm")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nombre = document.getElementById("nombre").value;
    const apellido = document.getElementById("apellido").value;
    const foto = document.getElementById("foto").value;

    const res = await fetch(`/profile/${localStorage.getItem("userId")}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ nombre, apellido, foto })
    });

    const data = await res.json();
    alert(data.message);
    window.location.reload();
  });
}
