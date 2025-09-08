import { Router } from "express";
import { getDB } from "../db.js";
import { authRequired } from "../middleware/auth.js";

const router = Router();

// Eventos del usuario autenticado (opcionalmente por fecha)
router.get("/", authRequired, async (req, res) => {
  try {
    const { from, to, date } = req.query;
    const db = await getDB();

    let rows = [];
    if (date) {
      rows = await db.all(
        `SELECT * FROM events WHERE user_id = ? AND fecha = ? ORDER BY hora ASC NULLS LAST, id ASC`,
        [req.user.id, date]
      );
    } else if (from && to) {
      rows = await db.all(
        `SELECT * FROM events WHERE user_id = ? AND fecha BETWEEN ? AND ? ORDER BY fecha ASC, hora ASC NULLS LAST`,
        [req.user.id, from, to]
      );
    } else {
      rows = await db.all(
        `SELECT * FROM events WHERE user_id = ? ORDER BY fecha DESC, hora ASC NULLS LAST`,
        [req.user.id]
      );
    }
    res.json(rows);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al listar eventos" });
  }
});

// Crear evento
router.post("/", authRequired, async (req, res) => {
  try {
    const { titulo, fecha, hora, descripcion } = req.body;
    if (!titulo || !fecha) return res.status(400).json({ error: "titulo y fecha son obligatorios" });

    const db = await getDB();
    const result = await db.run(
      `INSERT INTO events (user_id, titulo, fecha, hora, descripcion) VALUES (?, ?, ?, ?, ?)`,
      [req.user.id, titulo, fecha, hora || null, descripcion || ""]
    );
    const evt = await db.get(`SELECT * FROM events WHERE id = ?`, [result.lastID]);
    res.status(201).json(evt);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al crear evento" });
  }
});

// Actualizar evento
router.put("/:id", authRequired, async (req, res) => {
  try {
    const { id } = req.params;
    const { titulo, fecha, hora, descripcion } = req.body;

    const db = await getDB();
    const exists = await db.get(`SELECT * FROM events WHERE id = ? AND user_id = ?`, [id, req.user.id]);
    if (!exists) return res.status(404).json({ error: "Evento no encontrado" });

    await db.run(
      `UPDATE events SET titulo = ?, fecha = ?, hora = ?, descripcion = ? WHERE id = ?`,
      [titulo || exists.titulo, fecha || exists.fecha, hora ?? exists.hora, descripcion ?? exists.descripcion, id]
    );
    const updated = await db.get(`SELECT * FROM events WHERE id = ?`, [id]);
    res.json(updated);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al actualizar evento" });
  }
});

// Eliminar evento
router.delete("/:id", authRequired, async (req, res) => {
  try {
    const { id } = req.params;
    const db = await getDB();
    const exists = await db.get(`SELECT id FROM events WHERE id = ? AND user_id = ?`, [id, req.user.id]);
    if (!exists) return res.status(404).json({ error: "Evento no encontrado" });

    await db.run(`DELETE FROM events WHERE id = ?`, [id]);
    res.json({ ok: true });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al eliminar evento" });
  }
});

// Eventos públicos por nickname (para ver perfil ajeno)
router.get("/user/:nickname", async (req, res) => {
  try {
    const db = await getDB();
    const user = await db.get(`SELECT id FROM users WHERE nickname = ?`, [req.params.nickname]);
    if (!user) return res.status(404).json({ error: "Usuario no encontrado" });

    const rows = await db.all(
      `SELECT id, titulo, fecha, hora, descripcion, created_at
       FROM events WHERE user_id = ? ORDER BY fecha DESC, hora ASC NULLS LAST`,
      [user.id]
    );
    res.json(rows);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error al listar eventos del usuario" });
  }
});

export default router;
