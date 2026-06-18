import sqlite3 from 'sqlite3'
import path from 'path'
import { fileURLToPath } from 'url'
import fs from 'fs'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

const dbPath = path.resolve(__dirname, '../database.sqlite')

// Backup system as planned
try {
  if (fs.existsSync(dbPath)) {
    fs.copyFileSync(dbPath, dbPath + '.bak')
    console.log('[DB] Backup created successfully.')
  }
} catch (err) {
  console.error('[DB] Failed to create backup:', err)
}

const db = new sqlite3.Database(dbPath, (err) => {
  if (err) {
    console.error('[DB] Error opening database:', err)
  } else {
    console.log('[DB] Connected to SQLite database.')
  }
})

// Promisify database runs and queries
export const dbRun = (sql, params = []) => {
  return new Promise((resolve, reject) => {
    db.run(sql, params, function (err) {
      if (err) reject(err)
      else resolve(this)
    })
  })
}

export const dbGet = (sql, params = []) => {
  return new Promise((resolve, reject) => {
    db.get(sql, params, (err, row) => {
      if (err) reject(err)
      else resolve(row)
    })
  })
}

export const dbAll = (sql, params = []) => {
  return new Promise((resolve, reject) => {
    db.all(sql, params, (err, rows) => {
      if (err) reject(err)
      else resolve(rows)
    })
  })
}

export const initDb = async () => {
  // 1. Table for preloaded matriculated students
  await dbRun(`
    CREATE TABLE IF NOT EXISTS institucion_estudiantes (
      documento TEXT PRIMARY KEY,
      nombre_completo TEXT NOT NULL,
      grupo TEXT NOT NULL
    )
  `)

  // 2. Table for benefit applicants
  await dbRun(`
    CREATE TABLE IF NOT EXISTS estudiantes (
      documento TEXT PRIMARY KEY,
      nombres TEXT NOT NULL,
      apellidos TEXT NOT NULL,
      grupo TEXT NOT NULL,
      estado TEXT NOT NULL, -- 'Pendiente', 'Activo', 'Inactivo', 'Suspendido'
      creado_en TEXT NOT NULL,
      FOREIGN KEY (documento) REFERENCES institucion_estudiantes(documento)
    )
  `)

  // 3. Table for attendance log
  await dbRun(`
    CREATE TABLE IF NOT EXISTS asistencia (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      documento TEXT NOT NULL,
      fecha TEXT NOT NULL, -- YYYY-MM-DD
      hora TEXT NOT NULL,  -- HH:MM:SS
      FOREIGN KEY (documento) REFERENCES estudiantes(documento)
    )
  `)

  // 4. Table for verification receipts (comprobantes)
  await dbRun(`
    CREATE TABLE IF NOT EXISTS comprobantes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      documento TEXT NOT NULL,
      codigo_comprobante TEXT UNIQUE NOT NULL,
      fecha TEXT NOT NULL, -- YYYY-MM-DD
      hora TEXT NOT NULL,  -- HH:MM:SS
      FOREIGN KEY (documento) REFERENCES estudiantes(documento)
    )
  `)

  // 5. Table for justifications
  await dbRun(`
    CREATE TABLE IF NOT EXISTS justificaciones (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      documento TEXT NOT NULL,
      fecha_inasistencia TEXT NOT NULL, -- YYYY-MM-DD
      motivo TEXT NOT NULL,
      creado_en TEXT NOT NULL,
      FOREIGN KEY (documento) REFERENCES estudiantes(documento)
    )
  `)

  // 6. Table for webhook subscriptions
  // Relación: Almacena las URLs de sistemas externos y el tipo de evento al que se suscriben.
  // Esta tabla es consultada en server/index.js por la función triggerWebhook() cada vez que ocurre un evento de negocio.
  await dbRun(`
    CREATE TABLE IF NOT EXISTS webhooks (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      url TEXT NOT NULL, -- Endpoint externo que recibirá el POST JSON
      evento TEXT NOT NULL, -- Ej: 'estudiante.suspendido', 'asistencia.registrada', o '*' (todos)
      creado_en TEXT NOT NULL
    )
  `)

  // Preload seed data for institucion_estudiantes
  const count = await dbGet('SELECT COUNT(*) as count FROM institucion_estudiantes')
  if (count.count === 0) {
    const seedStudents = [
      { doc: '1001', name: 'Juan Pérez Montoya', grp: '11-A' },
      { doc: '1002', name: 'María Gómez Restrepo', grp: '11-A' },
      { doc: '1003', name: 'Andrés Montoya Holguín', grp: '10-B' },
      { doc: '1004', name: 'Camila Restrepo Echeverri', grp: '10-B' },
      { doc: '1005', name: 'Mateo Holguín Múnera', grp: '9-C' },
      { doc: '1006', name: 'Sofía Echeverri Vélez', grp: '9-C' },
      { doc: '1007', name: 'Alejandro Múnera Arias', grp: '8-A' },
      { doc: '1008', name: 'Isabella Vélez Osorio', grp: '8-A' },
      { doc: '1009', name: 'Samuel Arias Giraldo', grp: '7-B' },
      { doc: '1010', name: 'Valeria Osorio Castro', grp: '7-B' }
    ]

    const insertStmt = 'INSERT INTO institucion_estudiantes (documento, nombre_completo, grupo) VALUES (?, ?, ?)'
    for (const student of seedStudents) {
      await dbRun(insertStmt, [student.doc, student.name, student.grp])
    }
    console.log('[DB] Seed data loaded successfully.')
  }
}

export default db
