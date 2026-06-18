import mysql from 'mysql2/promise'

// Pool de conexiones global que se inicializa en initDb
let pool = null

// Promisified database helpers para mantener compatibilidad con las consultas existentes
export const dbRun = async (sql, params = []) => {
  if (!pool) throw new Error('[DB] Base de datos no inicializada.')
  const [result] = await pool.execute(sql, params)
  return {
    lastID: result.insertId,
    changes: result.affectedRows
  }
}

export const dbGet = async (sql, params = []) => {
  if (!pool) throw new Error('[DB] Base de datos no inicializada.')
  const [rows] = await pool.execute(sql, params)
  return rows[0] || null
}

export const dbAll = async (sql, params = []) => {
  if (!pool) throw new Error('[DB] Base de datos no inicializada.')
  const [rows] = await pool.execute(sql, params)
  return rows
}

// Inicialización de la base de datos con reintentos para soportar el arranque de Docker
export const initDb = async () => {
  const connectionConfig = {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || 'root',
    port: parseInt(process.env.DB_PORT || '3306', 10),
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
  }

  const dbName = process.env.DB_NAME || 'beneficio_alimentario'

  let connection
  let attempts = 0
  const maxAttempts = 15

  // Bucle de reintentos para esperar a que MySQL esté listo para recibir conexiones (Docker up)
  while (attempts < maxAttempts) {
    try {
      connection = await mysql.createConnection(connectionConfig)
      console.log('[DB] Conectado exitosamente al servidor MySQL.')
      break
    } catch (err) {
      attempts++
      console.log(`[DB] Esperando inicio de MySQL... Intento ${attempts}/${maxAttempts}. Error: ${err.message}`)
      await new Promise(resolve => setTimeout(resolve, 2000))
    }
  }

  if (!connection) {
    throw new Error('[DB] No se pudo establecer conexión con MySQL después de múltiples reintentos.')
  }

  // Crear base de datos si no existe
  await connection.query(`CREATE DATABASE IF NOT EXISTS \`${dbName}\``)
  await connection.end()

  // Crear e inicializar el pool de conexiones
  pool = mysql.createPool({
    ...connectionConfig,
    database: dbName
  })

  // 1. Tabla para estudiantes matriculados pre-cargados institucionalmente
  await dbRun(`
    CREATE TABLE IF NOT EXISTS institucion_estudiantes (
      documento VARCHAR(50) PRIMARY KEY,
      nombre_completo VARCHAR(255) NOT NULL,
      grupo VARCHAR(50) NOT NULL
    )
  `)

  // 2. Tabla para beneficiarios registrados
  await dbRun(`
    CREATE TABLE IF NOT EXISTS estudiantes (
      documento VARCHAR(50) PRIMARY KEY,
      nombres VARCHAR(100) NOT NULL,
      apellidos VARCHAR(100) NOT NULL,
      grupo VARCHAR(50) NOT NULL,
      estado VARCHAR(20) NOT NULL, -- 'Pendiente', 'Activo', 'Inactivo', 'Suspendido'
      creado_en VARCHAR(20) NOT NULL,
      FOREIGN KEY (documento) REFERENCES institucion_estudiantes(documento)
    )
  `)

  // 3. Tabla para el registro diario de asistencia
  await dbRun(`
    CREATE TABLE IF NOT EXISTS asistencia (
      id INT AUTO_INCREMENT PRIMARY KEY,
      documento VARCHAR(50) NOT NULL,
      fecha VARCHAR(20) NOT NULL, -- YYYY-MM-DD
      hora VARCHAR(20) NOT NULL,  -- HH:MM:SS
      FOREIGN KEY (documento) REFERENCES estudiantes(documento)
    )
  `)

  // 4. Tabla para tickets comprobantes de asistencia
  await dbRun(`
    CREATE TABLE IF NOT EXISTS comprobantes (
      id INT AUTO_INCREMENT PRIMARY KEY,
      documento VARCHAR(50) NOT NULL,
      codigo_comprobante VARCHAR(100) UNIQUE NOT NULL,
      fecha VARCHAR(20) NOT NULL, -- YYYY-MM-DD
      hora VARCHAR(20) NOT NULL,  -- HH:MM:SS
      FOREIGN KEY (documento) REFERENCES estudiantes(documento)
    )
  `)

  // 5. Tabla para justificaciones/excusas de faltas
  await dbRun(`
    CREATE TABLE IF NOT EXISTS justificaciones (
      id INT AUTO_INCREMENT PRIMARY KEY,
      documento VARCHAR(50) NOT NULL,
      fecha_inasistencia VARCHAR(20) NOT NULL, -- YYYY-MM-DD
      motivo TEXT NOT NULL,
      creado_en VARCHAR(20) NOT NULL,
      FOREIGN KEY (documento) REFERENCES estudiantes(documento)
    )
  `)

  // 6. Tabla para suscripciones de Webhooks
  await dbRun(`
    CREATE TABLE IF NOT EXISTS webhooks (
      id INT AUTO_INCREMENT PRIMARY KEY,
      url VARCHAR(255) NOT NULL,
      evento VARCHAR(100) NOT NULL,
      creado_en VARCHAR(100) NOT NULL
    )
  `)

  // Pre-cargar datos semilla (seed data) en institucion_estudiantes
  const [rows] = await pool.query('SELECT COUNT(*) as count FROM institucion_estudiantes')
  if (rows[0].count === 0) {
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
    console.log('[DB] Datos semilla cargados con éxito en MySQL.')
  }
}

export default pool
