// perfil.js

document.addEventListener("DOMContentLoaded", () => {
    const eventList = document.getElementById("event-list");
  
    const eventTitleInput = document.getElementById("event-title");
    const eventDateInput = document.getElementById("event-date");
    const eventTimeInput = document.getElementById("event-time");
    const eventDescInput = document.getElementById("event-desc");
    const updateBtn = document.getElementById("update-event");
  
    let events = JSON.parse(localStorage.getItem("events")) || [];
    let editingIndex = null;
  
    // Renderizar todos los eventos guardados
    function renderEvents() {
      eventList.innerHTML = "";
  
      if (events.length === 0) {
        eventList.innerHTML = "<li>No hay recordatorios guardados</li>";
        return;
      }
  
      events.forEach((event, index) => {
        const li = document.createElement("li");
        li.innerHTML = `
          <strong>${event.date}</strong> ${event.time ? "⏰ " + event.time : ""} - ${event.title}
          ${event.desc ? `<div class="event-details">📝 ${event.desc}</div>` : ""}
        `;
  
        const actions = document.createElement("div");
        actions.classList.add("event-actions");
  
        const editBtn = document.createElement("button");
        editBtn.textContent = "Editar";
        editBtn.classList.add("event-btn", "edit-btn");
        editBtn.addEventListener("click", () => startEditing(index));
  
        const deleteBtn = document.createElement("button");
        deleteBtn.textContent = "Eliminar";
        deleteBtn.classList.add("event-btn", "delete-btn");
        deleteBtn.addEventListener("click", () => deleteEvent(index));
  
        actions.appendChild(editBtn);
        actions.appendChild(deleteBtn);
        li.appendChild(actions);
  
        eventList.appendChild(li);
      });
    }
  
    function startEditing(index) {
      const event = events[index];
  
      eventTitleInput.value = event.title;
      eventDateInput.value = event.date;
      eventTimeInput.value = event.time || "";
      eventDescInput.value = event.desc || "";
  
      editingIndex = index;
      updateBtn.classList.remove("hidden");
    }
  
    function deleteEvent(index) {
      if (confirm("¿Seguro que deseas eliminar este recordatorio?")) {
        events.splice(index, 1);
        saveAndRender();
      }
    }
  
    updateBtn.addEventListener("click", () => {
      if (editingIndex === null) return;
  
      const updatedTitle = eventTitleInput.value.trim();
      const updatedDate = eventDateInput.value;
      const updatedTime = eventTimeInput.value;
      const updatedDesc = eventDescInput.value.trim();
  
      if (!updatedTitle || !updatedDate) {
        alert("Título y fecha son obligatorios.");
        return;
      }
  
      events[editingIndex] = {
        title: updatedTitle,
        date: updatedDate,
        time: updatedTime,
        desc: updatedDesc
      };
  
      editingIndex = null;
      clearForm();
      saveAndRender();
      alert("Recordatorio actualizado ✅");
    });
  
    function saveAndRender() {
      localStorage.setItem("events", JSON.stringify(events));
      renderEvents();
    }
  
    function clearForm() {
      eventTitleInput.value = "";
      eventDateInput.value = "";
      eventTimeInput.value = "";
      eventDescInput.value = "";
      updateBtn.classList.add("hidden");
    }
  
    // Iniciar
    renderEvents();
  });
  