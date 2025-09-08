import { Router } from "express";
import multer from "multer";
import path from "path";
import fs from "fs";
import { getDB } from "../db.js";
import { authRequired } from "../middleware/auth.js";

const router = Router();

// carpeta de subidas
const uploadDir = path.resolve("src/uploads");
if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir, { recursive: true });

// configuración multer
const storage = multer.diskStorage({
  destination: (_, __, cb) => cb(null, uploadDir),
  filename: (_, file, cb) => {
    const unique = Date.now() + "-" + Math.round(Math.random() * 1e9);
    const ext = path.extname(file.originalname);
    cb(null, unique + ext);
  }
});
const upload = multer({ storage });

// Obtener perfil público por nickname
router.get("/:nickname", async (req, res) => {
  try {
    const db = await getDB();
    const user = await db.get(
      `SELECT id, nickname, nombre, apellido, mail, tel, foto, created_at
       FROM users WHERE nickname = ?`,
      [req.params.nickname]
    );
    if (!user) return res.status(404).json({ error: "Usuario no encontrado" });
    res.json(user);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al obtener perfil" });
  }
});

// Editar perfil propio
router.put("/me", authRequired, async (req, res) => {
  try {
    const { nombre, apellido, tel } = req.body;
    const db = await getDB();
    await db.run(
      `UPDATE users SET nombre = ?, apellido = ?, tel = ? WHERE id = ?`,
      [nombre || "", apellido || "", tel || "", req.user.id]
    );
    const updated = await db.get(
      `SELECT id, nickname, nombre, apellido, mail, tel, foto FROM users WHERE id = ?`,
      [req.user.id]
    );
    res.json(updated);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al actualizar perfil" });
  }
});

// Subir/actualizar foto de perfil
router.post("/me/photo", authRequired, upload.single("foto"), async (req, res) => {
  try {
    const relativePath = `/uploads/${req.file.filename}`;
    const db = await getDB();
    await db.run(`UPDATE users SET foto = ? WHERE id = ?`, [relativePath, req.user.id]);
    res.json({ foto: relativePath });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al subir foto" });
  }
});

export default router;
