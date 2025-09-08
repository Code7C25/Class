import jwt from "jsonwebtoken";
import dotenv from "dotenv";
dotenv.config();

export function signToken(payload) {
  return jwt.sign(payload, process.env.JWT_SECRET, { expiresIn: "7d" });
}

export function authRequired(req, res, next) {
  const token = req.cookies?.token;
  if (!token) return res.status(401).json({ error: "No autenticado" });
  try {
    const data = jwt.verify(token, process.env.JWT_SECRET);
    req.user = data; // { id, nickname }
    next();
  } catch {
    return res.status(401).json({ error: "Token inválido o expirado" });
  }
}
