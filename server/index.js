import express from 'express'
import cors from 'cors'
import { initDb, dbRun, dbGet, dbAll } from './database.js'

const app = express()
app.use(cors())
app.use(express.json())

const PORT = 3001

// Helper function to format date/time in Colombia time (or local)
const getLocalDateString = () => {
  const date = new Date()
  // YYYY-MM-DD
  return date.toISOString().split('T')[0]
}

const getLocalTimeString = () => {
  const date = new Date()
  return date.toTimeString().split(' ')[0]
}

// Memory logs for the test webhook receiver
// Relación: Almacena en memoria local los payloads JSON recibidos por el endpoint de pruebas local.
// Esto permite simular un sistema externo receptor y consultar las notificaciones recibidas en /api/test/webhook-receiver/logs.
const webhookLogs = []

// Helper function to trigger webhooks
// Relación: Esta función es el motor del Webhook. Se llama en cada evento importante de negocio:
// 1. En /api/estudiantes/registro (evento 'estudiante.registrado')
// 2. En /api/admin/estudiantes/aprobar (evento 'estudiante.aprobado')
// 3. En /api/asistencia (evento 'asistencia.registrada')
// 4. En updateSuspensionStatus() cuando el alumno es suspendido automáticamente (evento 'estudiante.suspendido')
async function triggerWebhook(evento, data) {
  try {
    // Consulta en la base de datos (tabla 'webhooks') si hay URLs suscritas a este evento o al comodín '*'
    const webhooks = await dbAll(
      'SELECT id, url, evento FROM webhooks WHERE evento = ? OR evento = "*"',
      [evento]
    )

    const payload = {
      evento,
      timestamp: new Date().toISOString(),
      data
    }

    for (const wh of webhooks) {
      console.log(`[WEBHOOK] Triggering event "${evento}" to URL: ${wh.url}`)
      // Envia la petición HTTP POST de forma asíncrona.
      // Se utiliza "fire-and-forget" (sin await de fetch) para responder de inmediato al cliente de la API
      // principal, evitando que la lentitud o caída del receptor de webhooks afecte el rendimiento del sistema escolar.
      fetch(wh.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then((res) => {
        if (!res.ok) {
          console.error(`[WEBHOOK ERROR] URL ${wh.url} responded with status ${res.status}`)
        } else {
          console.log(`[WEBHOOK SUCCESS] URL ${wh.url} responded with status ${res.status}`)
        }
      })
      .catch((err) => {
        console.error(`[WEBHOOK ERROR] Failed to send webhook to URL ${wh.url}:`, err.message)
      })
    }
  } catch (err) {
    console.error('[WEBHOOK SYSTEM ERROR] Error selecting webhooks:', err.message)
  }
}


// Business Logic Helper: Check and apply suspensions
// If there are at least 3 service days (dates in attendance table) and the student has missed the last 3 consecutive ones without justification, suspend them.
async function updateSuspensionStatus(documento) {
  // Get student profile
  const student = await dbGet('SELECT * FROM estudiantes WHERE documento = ?', [documento])
  if (!student || student.estado !== 'Activo') return

  // Get the last 3 distinct dates when the cafeteria registered attendance (service days)
  const serviceDays = await dbAll(`
    SELECT DISTINCT fecha FROM asistencia 
    ORDER BY fecha DESC LIMIT 3
  `)

  // If there have not been at least 3 service days yet, they cannot be suspended for 3 absences
  if (serviceDays.length < 3) return

  let consecutiveAbsences = 0
  for (const day of serviceDays) {
    const dateStr = day.fecha
    
    // Check if student attended on this date
    const attendance = await dbGet(
      'SELECT id FROM asistencia WHERE documento = ? AND fecha = ?', 
      [documento, dateStr]
    )

    if (!attendance) {
      // Check if student has an approved justification for this date
      const justification = await dbGet(
        'SELECT id FROM justificaciones WHERE documento = ? AND fecha_inasistencia = ?',
        [documento, dateStr]
      )
      
      if (!justification) {
        consecutiveAbsences++
      } else {
        // If a day is justified, it breaks the consecutive chain of unexcused absences
        break
      }
    } else {
      // If student attended, the consecutive chain of absences is broken
      break
    }
  }

  if (consecutiveAbsences >= 3) {
    await dbRun("UPDATE estudiantes SET estado = 'Suspendido' WHERE documento = ?", [documento])
    console.log(`[BUSINESS LOGIC] Student ${documento} was suspended due to 3 consecutive absences.`)
    triggerWebhook('estudiante.suspendido', {
      documento,
      nombres: student.nombres,
      apellidos: student.apellidos,
      grupo: student.grupo,
      estado: 'Suspendido',
      motivo: '3 inasistencias consecutivas'
    })
  }
}

// Check suspensions for all active students
async function checkAllSuspensions() {
  const activeStudents = await dbAll("SELECT documento FROM estudiantes WHERE estado = 'Activo'")
  for (const s of activeStudents) {
    await updateSuspensionStatus(s.documento)
  }
}

// --- API ENDPOINTS ---

// 1. Validate if a document belongs to the institution
app.post('/api/estudiantes/validar', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento de identidad es obligatorio.' })
  }
  try {
    const student = await dbGet('SELECT * FROM institucion_estudiantes WHERE documento = ?', [documento])
    if (!student) {
      return res.status(404).json({ error: 'El documento no pertenece a ningún estudiante matriculado en la institución.' })
    }
    
    // Check if they already applied
    const applied = await dbGet('SELECT estado FROM estudiantes WHERE documento = ?', [documento])
    if (applied) {
      return res.status(400).json({ 
        error: `Este estudiante ya se encuentra registrado con estado: ${applied.estado}.` 
      })
    }

    res.json(student)
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 2. Register new student profile
app.post('/api/estudiantes/registro', async (req, res) => {
  const { documento, nombres, apellidos, grupo } = req.body
  if (!documento || !nombres || !apellidos || !grupo ||
      !documento.trim() || !nombres.trim() || !apellidos.trim() || !grupo.trim()) {
    return res.status(400).json({ error: 'Todos los campos (documento, nombres, apellidos, grupo) son obligatorios.' })
  }
  try {
    // Double check institutional list
    const valid = await dbGet('SELECT documento FROM institucion_estudiantes WHERE documento = ?', [documento])
    if (!valid) {
      return res.status(400).json({ error: 'El estudiante no pertenece a la institución.' })
    }

    // Double check if already registered in estudiantes
    const alreadyRegistered = await dbGet('SELECT estado FROM estudiantes WHERE documento = ?', [documento])
    if (alreadyRegistered) {
      return res.status(400).json({ error: `Este estudiante ya se encuentra registrado con estado: ${alreadyRegistered.estado}.` })
    }

    const created = getLocalDateString()
    await dbRun(
      `INSERT INTO estudiantes (documento, nombres, apellidos, grupo, estado, creado_en)
       VALUES (?, ?, ?, ?, 'Pendiente', ?)`,
      [documento, nombres, apellidos, grupo, created]
    )

    triggerWebhook('estudiante.registrado', {
      documento,
      nombres,
      apellidos,
      grupo,
      estado: 'Pendiente',
      creado_en: created
    })

    res.json({ message: 'Perfil creado exitosamente. En espera de aprobación por la coordinadora.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 3. Get student profile by document
app.get('/api/estudiantes/perfil/:doc', async (req, res) => {
  const { doc } = req.params
  try {
    const student = await dbGet('SELECT * FROM estudiantes WHERE documento = ?', [doc])
    if (!student) {
      return res.status(404).json({ error: 'Perfil no encontrado.' })
    }
    res.json(student)
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 4. Mark attendance (for Active students)
app.post('/api/asistencia', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento es obligatorio.' })
  }
  const today = getLocalDateString()
  const now = getLocalTimeString()

  try {
    const student = await dbGet('SELECT * FROM estudiantes WHERE documento = ?', [documento])
    if (!student) {
      return res.status(404).json({ error: 'Estudiante no registrado.' })
    }

    if (student.estado !== 'Activo') {
      return res.status(403).json({ 
        error: `No puedes marcar asistencia. Tu estado actual es: ${student.estado}.` 
      })
    }

    // Check if already registered today
    const alreadyRegistered = await dbGet(
      'SELECT id FROM asistencia WHERE documento = ? AND fecha = ?',
      [documento, today]
    )
    if (alreadyRegistered) {
      return res.status(400).json({ error: 'Ya has registrado tu asistencia el día de hoy.' })
    }

    // Register attendance
    await dbRun('INSERT INTO asistencia (documento, fecha, hora) VALUES (?, ?, ?)', [documento, today, now])

    // Generate unique verification code
    const ticketCode = `EVE-${today.replace(/-/g, '')}-${documento}`
    await dbRun(
      'INSERT INTO comprobantes (documento, codigo_comprobante, fecha, hora) VALUES (?, ?, ?, ?)',
      [documento, ticketCode, today, now]
    )

    triggerWebhook('asistencia.registrada', {
      documento,
      nombres: student.nombres,
      apellidos: student.apellidos,
      grupo: student.grupo,
      fecha: today,
      hora: now,
      codigo_comprobante: ticketCode
    })

    res.json({
      message: 'Asistencia registrada con éxito.',
      comprobante: {
        documento,
        nombre: `${student.nombres} ${student.apellidos}`,
        grupo: student.grupo,
        codigo: ticketCode,
        fecha: today,
        hora: now
      }
    })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 5. Get current day receipt/comprobante
app.get('/api/comprobante/:doc', async (req, res) => {
  const { doc } = req.params
  const today = getLocalDateString()
  try {
    const student = await dbGet('SELECT * FROM estudiantes WHERE documento = ?', [doc])
    if (!student) {
      return res.status(404).json({ error: 'Estudiante no registrado.' })
    }

    const receipt = await dbGet(
      'SELECT * FROM comprobantes WHERE documento = ? AND fecha = ?',
      [doc, today]
    )

    if (!receipt) {
      return res.status(404).json({ error: 'No se encontró un comprobante de asistencia para el día de hoy.' })
    }

    res.json({
      documento: doc,
      nombre: `${student.nombres} ${student.apellidos}`,
      grupo: student.grupo,
      codigo: receipt.codigo_comprobante,
      fecha: receipt.fecha,
      hora: receipt.hora
    })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 6. Justify absence
app.post('/api/justificaciones', async (req, res) => {
  const { documento, fecha_inasistencia, motivo } = req.body
  if (!documento || !fecha_inasistencia || !motivo || 
      !documento.trim() || !fecha_inasistencia.trim() || !motivo.trim()) {
    return res.status(400).json({ error: 'Todos los campos (documento, fecha_inasistencia, motivo) son obligatorios.' })
  }
  try {
    const student = await dbGet('SELECT documento FROM estudiantes WHERE documento = ?', [documento])
    if (!student) {
      return res.status(404).json({ error: 'Estudiante no registrado.' })
    }

    const created = getLocalDateString()
    await dbRun(
      `INSERT INTO justificaciones (documento, fecha_inasistencia, motivo, creado_en)
       VALUES (?, ?, ?, ?)`,
      [documento, fecha_inasistencia, motivo, created]
    )

    res.json({ message: 'Justificación enviada con éxito. La coordinadora la revisará.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 7. Student voluntarily opts out of the benefit
app.post('/api/estudiantes/renunciar', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento es obligatorio.' })
  }
  try {
    const student = await dbGet('SELECT documento FROM estudiantes WHERE documento = ?', [documento])
    if (!student) {
      return res.status(404).json({ error: 'Estudiante no registrado.' })
    }

    await dbRun("UPDATE estudiantes SET estado = 'Inactivo' WHERE documento = ?", [documento])
    res.json({ message: 'Has renunciado al beneficio exitosamente.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})


// --- ADMIN ENDPOINTS (Coordinadora) ---

// 8. List all students (Coordinadora view)
app.get('/api/admin/estudiantes', async (req, res) => {
  try {
    const students = await dbAll('SELECT * FROM estudiantes ORDER BY creado_en DESC')
    res.json(students)
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 9. Approve student profile
app.post('/api/admin/estudiantes/aprobar', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento es obligatorio.' })
  }
  try {
    await dbRun("UPDATE estudiantes SET estado = 'Activo' WHERE documento = ?", [documento])
    const student = await dbGet('SELECT * FROM estudiantes WHERE documento = ?', [documento])
    if (student) {
      triggerWebhook('estudiante.aprobado', {
        documento,
        nombres: student.nombres,
        apellidos: student.apellidos,
        grupo: student.grupo,
        estado: 'Activo'
      })
    }
    res.json({ message: 'Estudiante aprobado para el beneficio.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 10. Reject student profile
app.post('/api/admin/estudiantes/rechazar', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento es obligatorio.' })
  }
  try {
    await dbRun("UPDATE estudiantes SET estado = 'Inactivo' WHERE documento = ?", [documento])
    res.json({ message: 'Solicitud del estudiante rechazada.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 11. Delete student profile (HU07)
app.post('/api/admin/estudiantes/eliminar', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento es obligatorio.' })
  }
  try {
    // Delete all linked records to avoid SQLite constraint fails
    await dbRun('DELETE FROM asistencia WHERE documento = ?', [documento])
    await dbRun('DELETE FROM comprobantes WHERE documento = ?', [documento])
    await dbRun('DELETE FROM justificaciones WHERE documento = ?', [documento])
    await dbRun('DELETE FROM estudiantes WHERE documento = ?', [documento])
    res.json({ message: 'Estudiante eliminado del beneficio y base de datos.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 12. Reactivate suspended student (Reingreso)
app.post('/api/admin/estudiantes/reingresar', async (req, res) => {
  const { documento } = req.body
  if (!documento || typeof documento !== 'string' || !documento.trim()) {
    return res.status(400).json({ error: 'El documento es obligatorio.' })
  }
  try {
    await dbRun("UPDATE estudiantes SET estado = 'Activo' WHERE documento = ?", [documento])
    res.json({ message: 'Estudiante reactivado (Reingreso aprobado).' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 13. Daily attendance list
app.get('/api/admin/asistencia/diaria', async (req, res) => {
  const date = req.query.fecha || getLocalDateString()
  try {
    const list = await dbAll(`
      SELECT a.id, a.fecha, a.hora, e.documento, e.nombres, e.apellidos, e.grupo
      FROM asistencia a
      JOIN estudiantes e ON a.documento = e.documento
      WHERE a.fecha = ?
      ORDER BY a.hora DESC
    `, [date])
    res.json(list)
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 14. Weekly attendance count report
app.get('/api/admin/asistencia/semanal', async (req, res) => {
  try {
    // Get list of last 7 distinct dates where cafeteria served
    const days = await dbAll(`
      SELECT DISTINCT fecha FROM asistencia
      ORDER BY fecha DESC LIMIT 7
    `)
    const dateList = days.map(d => d.fecha)

    if (dateList.length === 0) {
      return res.json({ dateList: [], report: [] })
    }

    const report = await dbAll(`
      SELECT e.documento, e.nombres, e.apellidos, e.grupo,
             COUNT(a.id) as total_asistencias
      FROM estudiantes e
      LEFT JOIN asistencia a ON e.documento = a.documento AND a.fecha IN (${dateList.map(() => '?').join(',')})
      WHERE e.estado = 'Activo' OR e.estado = 'Suspendido'
      GROUP BY e.documento
      ORDER BY total_asistencias DESC
    `, dateList)

    res.json({ dateList, report })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 15. Get list of justifications
app.get('/api/admin/justificaciones', async (req, res) => {
  try {
    const list = await dbAll(`
      SELECT j.*, e.nombres, e.apellidos, e.grupo, e.estado
      FROM justificaciones j
      JOIN estudiantes e ON j.documento = e.documento
      ORDER BY j.creado_en DESC
    `)
    res.json(list)
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// 16. Simulation Helper: Simulate past cafeteria service days and attendance logs
// To test the 3-day absence rule without waiting for 3 real days.
app.post('/api/admin/simular-dia', async (req, res) => {
  const { fecha, asistentes } = req.body // fecha: 'YYYY-MM-DD', asistentes: ['doc1', 'doc2']
  if (!fecha || !fecha.trim() || !Array.isArray(asistentes)) {
    return res.status(400).json({ error: 'La fecha y la lista de asistentes (arreglo) son obligatorios.' })
  }
  try {
    // 1. Insert attendance logs for the specified date
    for (const doc of asistentes) {
      // Check if student exists
      const exists = await dbGet('SELECT estado FROM estudiantes WHERE documento = ?', [doc])
      if (exists && exists.estado === 'Activo') {
        // Check if attendance already exists
        const attendanceExists = await dbGet(
          'SELECT id FROM asistencia WHERE documento = ? AND fecha = ?', 
          [doc, fecha]
        )
        if (!attendanceExists) {
          await dbRun('INSERT INTO asistencia (documento, fecha, hora) VALUES (?, ?, ?)', [doc, fecha, '12:00:00'])
        }
      }
    }

    // 2. Run checkAllSuspensions to evaluate the 3-day absence rule
    await checkAllSuspensions()

    res.json({ message: `Día de servicio ${fecha} simulado y suspensiones recalculadas con éxito.` })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// --- WEBHOOK ADMIN ENDPOINTS ---

// Get all webhooks
app.get('/api/webhooks', async (req, res) => {
  try {
    const list = await dbAll('SELECT * FROM webhooks ORDER BY creado_en DESC')
    res.json(list)
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// Register a new webhook
app.post('/api/webhooks', async (req, res) => {
  const { url, evento } = req.body
  if (!url || typeof url !== 'string' || !url.trim() || !evento || typeof evento !== 'string' || !evento.trim()) {
    return res.status(400).json({ error: 'La URL y el evento son obligatorios.' })
  }
  try {
    // Basic URL validation
    new URL(url)
  } catch (err) {
    return res.status(400).json({ error: 'La URL proporcionada no es válida.' })
  }

  try {
    const created = new Date().toISOString()
    await dbRun(
      'INSERT INTO webhooks (url, evento, creado_en) VALUES (?, ?, ?)',
      [url, evento, created]
    )
    res.status(201).json({ message: 'Webhook registrado con éxito.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// Delete a webhook
app.delete('/api/webhooks/:id', async (req, res) => {
  const { id } = req.params
  try {
    await dbRun('DELETE FROM webhooks WHERE id = ?', [id])
    res.json({ message: 'Webhook eliminado con éxito.' })
  } catch (err) {
    res.status(500).json({ error: err.message })
  }
})

// Webhook Receiver endpoint for testing
app.post('/api/test/webhook-receiver', (req, res) => {
  const payload = req.body
  console.log(`[TEST RECEIVER] Webhook received! Event: ${payload.evento}`)
  webhookLogs.unshift({
    receivedAt: new Date().toISOString(),
    payload
  })
  // Keep only the last 50 logs to avoid unbounded memory growth
  if (webhookLogs.length > 50) {
    webhookLogs.pop()
  }
  res.json({ success: true, message: 'Webhook received successfully.' })
})

// Endpoint to fetch the logs of received webhooks
app.get('/api/test/webhook-receiver/logs', (req, res) => {
  res.json(webhookLogs)
})

// Start server
app.listen(PORT, async () => {
  await initDb()
  console.log(`[SERVER] Running on http://localhost:${PORT}`)
})
