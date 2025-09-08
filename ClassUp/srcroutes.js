import { Router } from "express";
import bcrypt from "bcrypt";
import { getDB } from "../db.js";
import { signToken } from "../middleware/auth.js";

const router = Router();

// Registro
router.post("/register", async (req, res) => {
  try {
    const { nickname, nombre, apellido, mail, tel, password } = req.body;
    if (!nickname || !mail || !password) {
      return res.status(400).json({ error: "nickname, mail y password son obligatorios" });
    }

    const db = await getDB();
    const exists = await db.get("SELECT id FROM users WHERE nickname = ? OR mail = ?", [nickname, mail]);
    if (exists) return res.status(409).json({ error: "Nickname o mail ya registrados" });

    const password_hash = await bcrypt.hash(password, 10);
    const result = await db.run(
      `INSERT INTO users (nickname, nombre, apellido, mail, tel, password_hash)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [nickname, nombre || "", apellido || "", mail, tel || "", password_hash]
    );

    const token = signToken({ id: result.lastID, nickname });
    res
      .cookie("token", token, { httpOnly: true, sameSite: "lax", secure: false })
      .status(201)
      .json({ id: result.lastID, nickname, mail });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error en registro" });
  }
});

// Login
router.post("/login", async (req, res) => {
  try {
    const { mailOrNick, password } = req.body;
    if (!mailOrNick || !password) return res.status(400).json({ error: "Faltan credenciales" });

    const db = await getDB();
    const user = await db.get(
      "SELECT * FROM users WHERE mail = ? OR nickname = ?",
      [mailOrNick, mailOrNick]
    );
    if (!user) return res.status(401).json({ error: "Usuario no encontrado" });

    const ok = await bcrypt.compare(password, user.password_hash);
    if (!ok) return res.status(401).json({ error: "Credenciales inválidas" });

    const token = signToken({ id: user.id, nickname: user.nickname });
    res
      .cookie("token", token, { httpOnly: true, sameSite: "lax", secure: false })
      .json({ id: user.id, nickname: user.nickname, mail: user.mail, foto: user.foto });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Error en login" });
  }
});

// Me
router.get("/me", async (req, res) => {
  try {
    const token = req.cookies?.token;
    if (!token) return res.json(null);
    const payload = JSON.parse(
      Buffer.from(token.split(".")[1] || "", "base64").toString("utf8")
    );
    res.json({ id: payload.id, nickname: payload.nickname });
  } catch {
    res.json(null);
  }
});

// Logout
router.post("/logout", (req, res) => {
  res.clearCookie("token").json({ ok: true });
});

export default router;
