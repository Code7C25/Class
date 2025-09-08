import sqlite3 from "sqlite3";
import { open } from "sqlite";
import dotenv from "dotenv";
dotenv.config();

const dbPromise = open({
  filename: process.env.DB_FILE || "./calendario.db",
  driver: sqlite3.Database
});

export async function getDB() {
  const db = await dbPromise;

  // Crear tablas si no existen
  await db.exec(`
    PRAGMA foreign_keys = ON;

    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nickname TEXT UNIQUE NOT NULL,
      nombre TEXT,
      apellido TEXT,
      mail TEXT UNIQUE NOT NULL,
      tel TEXT,
      password_hash TEXT NOT NULL,
      foto TEXT, -- ruta del archivo subido
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS events (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      titulo TEXT NOT NULL,
      fecha TEXT NOT NULL,        -- YYYY-MM-DD
      hora TEXT,                  -- HH:MM
      descripcion TEXT,
      imagen TEXT,                -- opcional
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    CREATE INDEX IF NOT EXISTS idx_events_user_date ON events(user_id, fecha);
  `);

  return db;
}
