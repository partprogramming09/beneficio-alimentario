import { spawn } from 'child_process'
import http from 'http'
import fs from 'fs'
import path from 'path'

// Clean database files before test execution
try {
  const dbPath = path.resolve('database.sqlite')
  const dbBakPath = path.resolve('database.sqlite.bak')
  if (fs.existsSync(dbPath)) {
    fs.unlinkSync(dbPath)
    console.log('[TEST] Database file deleted to start fresh.')
  }
  if (fs.existsSync(dbBakPath)) {
    fs.unlinkSync(dbBakPath)
  }
} catch (err) {
  console.warn('[TEST] Could not clean database files:', err.message)
}

const BACKEND_URL = 'http://localhost:3001'

// Helper for HTTP requests
const request = (method, path, body = null) => {
  return new Promise((resolve, reject) => {
    const url = new URL(path, BACKEND_URL)
    const options = {
      method,
      hostname: url.hostname,
      port: url.port,
      path: url.pathname + url.search,
      headers: {
        'Content-Type': 'application/json'
      }
    }

    const req = http.request(options, (res) => {
      let data = ''
      res.on('data', (chunk) => { data += chunk })
      res.on('end', () => {
        try {
          const parsed = JSON.parse(data)
          resolve({ status: res.statusCode, body: parsed })
        } catch {
          resolve({ status: res.statusCode, body: data })
        }
      })
    })

    req.on('error', (err) => reject(err))

    if (body) {
      req.write(JSON.stringify(body))
    }
    req.end()
  })
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

async function runTests() {
  console.log('====== INICIANDO PRUEBAS DE INTEGRACIÓN DEL SISTEMA ======\n')
  
  // 1. Start backend server in a separate process
  console.log('[TEST] Iniciando servidor Express API...')
  const serverProc = spawn('node', ['server/index.js'], { stdio: 'pipe' })
  
  let serverReady = false
  serverProc.stdout.on('data', (data) => {
    const msg = data.toString()
    // console.log(`[SERVER LOG] ${msg.trim()}`)
    if (msg.includes('[SERVER] Running')) {
      serverReady = true
    }
  })

  serverProc.stderr.on('data', (data) => {
    console.error(`[SERVER ERR] ${data.toString().trim()}`)
  })

  // Wait for server to boot
  let attempts = 0
  while (!serverReady && attempts < 10) {
    await sleep(500)
    attempts++
  }

  if (!serverReady) {
    console.error('❌ Error: El servidor no se inició en el tiempo esperado.')
    serverProc.kill()
    process.exit(1)
  }
  console.log('✔️ Servidor iniciado correctamente en puerto 3001.\n')

  let passedTests = 0
  let totalTests = 0

  const assert = (condition, message) => {
    totalTests++
    if (condition) {
      passedTests++
      console.log(`  ✔️ PASADO: ${message}`)
    } else {
      console.error(`  ❌ FALLADO: ${message}`)
    }
  }

  try {
    // --- TEST 1: Validar documento institucional ---
    console.log('[TEST 1] Validar estudiante en la lista de la institución')
    const resValValid = await request('POST', '/api/estudiantes/validar', { documento: '1001' })
    assert(resValValid.status === 200 && resValValid.body.nombre_completo.includes('Juan'), 'Documento institucional válido (1001) verificado con éxito.')

    const resValInvalid = await request('POST', '/api/estudiantes/validar', { documento: '9999' })
    assert(resValInvalid.status === 404, 'Documento inválido (9999) rechazado correctamente.')

    // --- TEST 2: Registrar perfil (HU02) ---
    console.log('\n[TEST 2] Creación de perfil de estudiante (HU02)')
    const resReg = await request('POST', '/api/estudiantes/registro', {
      documento: '1001',
      nombres: 'Juan Carlos',
      apellidos: 'Pérez Montoya',
      grupo: '11-A'
    })
    assert(resReg.status === 200 && resReg.body.message.includes('espera de aprobación'), 'Registro inicial del estudiante completado en estado Pendiente.')

    const resProfile = await request('GET', '/api/estudiantes/perfil/1001')
    assert(resProfile.status === 200 && resProfile.body.estado === 'Pendiente', 'El estudiante se creó con estado "Pendiente".')

    // --- TEST 3: Marcar asistencia con perfil Pendiente (Debe fallar) ---
    console.log('\n[TEST 3] Validar que estudiante Pendiente no puede marcar asistencia')
    const resAsisFail = await request('POST', '/api/asistencia', { documento: '1001' })
    assert(resAsisFail.status === 403 && resAsisFail.body.error.includes('Pendiente'), 'Asistencia bloqueada correctamente para perfil no activo.')

    // --- TEST 4: Aprobación por la coordinadora (AC-2) ---
    console.log('\n[TEST 4] Aprobación de perfil por la Coordinadora (Panel admin)')
    const resApprove = await request('POST', '/api/admin/estudiantes/aprobar', { documento: '1001' })
    assert(resApprove.status === 200, 'Coordinadora aprueba la solicitud de beneficio del estudiante.')

    const resProfileActive = await request('GET', '/api/estudiantes/perfil/1001')
    assert(resProfileActive.status === 200 && resProfileActive.body.estado === 'Activo', 'Estado del estudiante actualizado exitosamente a "Activo".')

    // --- TEST 5: Marcar asistencia activa y generar comprobante (HU03 & HU04) ---
    console.log('\n[TEST 5] Registro de asistencia y comprobante (HU03 & HU04)')
    const resAsis = await request('POST', '/api/asistencia', { documento: '1001' })
    assert(resAsis.status === 200 && resAsis.body.comprobante.codigo.startsWith('EVE-'), 'Asistencia registrada con éxito y comprobante de ticket generado.')

    const resAsisDouble = await request('POST', '/api/asistencia', { documento: '1001' })
    assert(resAsisDouble.status === 400 && resAsisDouble.body.error.includes('Ya has registrado'), 'Intento de registro de asistencia doble el mismo día bloqueado correctamente.')

    // --- TEST 6: Recuperar comprobante (HU04 Scenario 2) ---
    console.log('\n[TEST 6] Recuperar comprobante actual del estudiante')
    const resRec = await request('GET', '/api/comprobante/1001')
    assert(resRec.status === 200 && resRec.body.codigo.includes('1001'), 'Comprobante del día recuperado exitosamente para el estudiante.')

    // --- TEST 7: Regla de inasistencias y suspensión automática (AC-5) ---
    console.log('\n[TEST 7] Simulación de inasistencias consecutivas y suspensión automática (HU07)')
    // We register attendance for OTHER students on days 1, 2, 3. Leaving 1001 out (absent).
    // Preload another student to simulate attendance
    await request('POST', '/api/estudiantes/registro', { documento: '1002', nombres: 'Maria', apellidos: 'Gomez', grupo: '11-A' })
    await request('POST', '/api/admin/estudiantes/aprobar', { documento: '1002' })

    console.log('  -> Simulando día 1 de servicio (Asiste 1002, Falta 1001)')
    await request('POST', '/api/admin/simular-dia', { fecha: '2026-07-01', asistentes: ['1002'] })
    
    console.log('  -> Simulando día 2 de servicio (Asiste 1002, Falta 1001)')
    await request('POST', '/api/admin/simular-dia', { fecha: '2026-07-02', asistentes: ['1002'] })
    
    // Check state before 3rd day
    const resProfileActive2 = await request('GET', '/api/estudiantes/perfil/1001')
    assert(resProfileActive2.body.estado === 'Activo', 'Estudiante sigue "Activo" tras 2 faltas consecutivas.')

    console.log('  -> Simulando día 3 de servicio (Asiste 1002, Falta 1001)')
    await request('POST', '/api/admin/simular-dia', { fecha: '2026-07-03', asistentes: ['1002'] })

    // Check state after 3rd day - should be Suspended
    const resProfileSuspended = await request('GET', '/api/estudiantes/perfil/1001')
    assert(resProfileSuspended.body.estado === 'Suspendido', 'Estudiante es suspendido automáticamente tras 3 inasistencias consecutivas.')

    // Attempt attendance while suspended
    const resAsisSusp = await request('POST', '/api/asistencia', { documento: '1001' })
    assert(resAsisSusp.status === 403, 'Registro de asistencia bloqueado correctamente para estudiante suspendido.')

    // --- TEST 8: Carga de justificante y reactivación (HU08 & HU09) ---
    console.log('\n[TEST 8] Justificar falta y reactivar beneficio (HU08 & HU09)')
    const resJustify = await request('POST', '/api/justificaciones', {
      documento: '1001',
      fecha_inasistencia: '2026-07-02',
      motivo: 'Cita médica odontológica'
    })
    assert(resJustify.status === 200, 'Estudiante justifica inasistencia con éxito.')

    const resReingreso = await request('POST', '/api/admin/estudiantes/reingresar', { documento: '1001' })
    assert(resReingreso.status === 200, 'Coordinadora aprueba justificante y reactiva al estudiante (Reingreso exitoso).')

    const resProfileActive3 = await request('GET', '/api/estudiantes/perfil/1001')
    assert(resProfileActive3.body.estado === 'Activo', 'Estado restablecido a "Activo" después de reingreso.')

    // --- TEST 9: Renuncia voluntaria (HU10) ---
    console.log('\n[TEST 9] Renuncia voluntaria al beneficio (HU10)')
    const resRenounce = await request('POST', '/api/estudiantes/renunciar', { documento: '1001' })
    assert(resRenounce.status === 200, 'Estudiante renuncia voluntariamente al beneficio.')

    const resProfileRenounced = await request('GET', '/api/estudiantes/perfil/1001')
    assert(resProfileRenounced.body.estado === 'Inactivo', 'Estado del perfil actualizado correctamente a "Inactivo".')

  } catch (err) {
    console.error('❌ Error crítico en ejecución de pruebas:', err)
  } finally {
    // Shutdown server
    console.log('\n[TEST] Apagando el servidor Express API...')
    serverProc.kill()
    await sleep(500)
    console.log('====== RESUMEN DE RESULTADOS DE PRUEBAS ======')
    console.log(`  Pruebas ejecutadas: ${totalTests}`)
    console.log(`  Pruebas exitosas  : ${passedTests}`)
    
    if (passedTests === totalTests) {
      console.log('\n🎉 ¡TODAS LAS PRUEBAS SE COMPLETARON CON ÉXITO! El sistema funciona de acuerdo con todas las HUs y reglas de negocio del backlog.')
      process.exit(0)
    } else {
      console.error('\n❌ Hubo fallos en las pruebas de integración. Revisa el registro anterior.')
      process.exit(1)
    }
  }
}

runTests()
