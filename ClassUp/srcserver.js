import express from "express";
import cors from "cors";
import cookieParser from "cookie-parser";
import dotenv from "dotenv";
import path from "path";

app.use(express.static("public"));
import authRoutes from "./routes/auth.js";
import userRoutes from "./routes/users.js";
import eventRoutes from "./routes/events.js";

dotenv.config();
const app = express();

// Middlewares
app.use(express.json());
app.use(cookieParser());
app.use(cors({
  origin: process.env.CLIENT_ORIGIN?.split(",") || "http://localhost:5173",
  credentials: true
}));

// Archivos estáticos (fotos de perfil)
app.use("/uploads", express.static(path.resolve("src/uploads")));
app.use("/public", express.static(path.resolve("src/public")));

// Rutas
app.use("/api/auth", authRoutes);
app.use("/api/users", userRoutes);
app.use("/api/events", eventRoutes);

// 404
app.use((_, res) => res.status(404).json({ error: "Ruta no encontrada" }));

// Start
const PORT = process.env.PORT || 4000;
app.listen(PORT, () => console.log(`API escuchando en http://localhost:${PORT}`));
const db = require("./database");
const bcrypt = require("bcrypt");
const express = require("express");
const db = require("./database");
const bcrypt = require("bcrypt");

const app = express();
const PORT = 3000;

// Middleware para manejar JSON
app.use(express.json());

// =============================
// Ruta de prueba
// =============================
app.get("/", (req, res) => {
  res.send("Servidor funcionando 🚀");
});

// =============================
// Registro de usuario
// =============================
app.post("/register", async (req, res) => {
  const { username, email, password } = req.body;

  if (!username || !email || !password) {
    return res.status(400).json({ error: "Todos los campos son requeridos" });
  }

  try {
    const hashedPassword = await bcrypt.hash(password, 10);

    db.run(
      "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
      [username, email, hashedPassword],
      function (err) {
        if (err) {
          return res.status(400).json({ error: "Usuario o email ya existe" });
        }
        res.json({ success: true, userId: this.lastID });
      }
    );
  } catch (error) {
    res.status(500).json({ error: "Error al registrar usuario" });
  }
});

// =============================
// Login de usuario
// =============================
app.post("/login", (req, res) => {
  const { email, password } = req.body;

  db.get("SELECT * FROM users WHERE email = ?", [email], async (err, user) => {
    if (err) return res.status(500).json({ error: "Error en la base de datos" });
    if (!user) return res.status(400).json({ error: "Usuario no encontrado" });

    const match = await bcrypt.compare(password, user.password);
    if (!match) return res.status(400).json({ error: "Contraseña incorrecta" });

    res.json({ success: true, message: "Login exitoso", user });
  });
});

// =============================
// Obtener perfil
// =============================
app.get("/profile/:id", (req, res) => {
  const { id } = req.params;

  db.get("SELECT id, username, email, nombre, apellido, foto FROM users WHERE id = ?", [id], (err, user) => {
    if (err) return res.status(500).json({ error: "Error en la base de datos" });
    if (!user) return res.status(404).json({ error: "Usuario no encontrado" });

    res.json(user);
  });
});

// =============================
// Editar perfil
// =============================
app.put("/profile/:id", (req, res) => {
  const { id } = req.params;
  const { nombre, apellido, foto } = req.body;

  db.run(
    "UPDATE users SET nombre = ?, apellido = ?, foto = ? WHERE id = ?",
    [nombre, apellido, foto, id],
    function (err) {
      if (err) return res.status(500).json({ error: "Error al actualizar perfil" });
      if (this.changes === 0) return res.status(404).json({ error: "Usuario no encontrado" });

      res.json({ success: true, message: "Perfil actualizado correctamente" });
    }
  );
});

// =============================
// Iniciar servidor
// =============================
app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
