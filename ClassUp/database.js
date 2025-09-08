const sqlite3 = require("sqlite3").verbose();
const db = new sqlite3.Database("./classup.db");

// Crear tabla de usuarios si no existe
db.serialize(() => {
  db.run(`CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    email TEXT UNIQUE,
    password TEXT,
    nombre TEXT,
    apellido TEXT,
    foto TEXT
  )`);
});

module.exports = db;
