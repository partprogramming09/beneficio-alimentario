import { spawn } from 'child_process'
import http from 'http'
import fs from 'fs'
import path from 'path'

// Clean database files before test execution to have a clean slate
try {
  const dbPath = path.resolve('database.sqlite')
  const dbBakPath = path.resolve('database.sqlite.bak')
  if (fs.existsSync(dbPath)) {
    fs.unlinkSync(dbPath)
    console.log('[TEST WEBHOOKS] Database file deleted to start fresh.')
  }
  if (fs.existsSync(dbBakPath)) {
    fs.unlinkSync(dbBakPath)
  }
} catch (err) {
  console.warn('[TEST WEBHOOKS] Could not clean database files:', err.message)
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
  console.log('====== INICIANDO PRUEBAS DE WEBHOOKS DEL SISTEMA ======\n')
  
  // 1. Start backend server in a separate process
  console.log('[TEST WEBHOOKS] Iniciando servidor Express API...')
  const serverProc = spawn('node', ['server/index.js'], { stdio: 'pipe' })
  
  let serverReady = false
  serverProc.stdout.on('data', (data) => {
    const msg = data.toString()
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
    // 2. Registrar el webhook local
    console.log('[TEST WEBHOOKS] 1. Registrar webhook de prueba')
    const resReg = await request('POST', '/api/webhooks', {
      url: 'http://localhost:3001/api/test/webhook-receiver',
      evento: '*'
    })
    assert(resReg.status === 201, 'Registro de webhook responde con 201.')

    // 3. Listar webhooks para validar que se guardó
    const resList = await request('GET', '/api/webhooks')
    assert(resList.body.length === 1, 'Se listó un webhook activo.')
    assert(resList.body[0].url === 'http://localhost:3001/api/test/webhook-receiver', 'La URL del webhook es correcta.')

    // 4. Registrar un estudiante (desencadena: estudiante.registrado)
    console.log('\n[TEST WEBHOOKS] 2. Registrar estudiante para gatillar "estudiante.registrado"')
    const resStudentReg = await request('POST', '/api/estudiantes/registro', {
      documento: '1001',
      nombres: 'Juan',
      apellidos: 'Perez Montoya',
      grupo: '11-A'
    })
    assert(resStudentReg.status === 200, 'Registro de estudiante exitoso.')
    
    // Esperar a que el webhook asíncrono se procese
    await sleep(500)

    // Validar recepción del webhook en los logs del receptor
    let resLogs = await request('GET', '/api/test/webhook-receiver/logs')
    assert(resLogs.body.length === 1, 'Receptor de pruebas recibió 1 webhook.')
    assert(resLogs.body[0].payload.evento === 'estudiante.registrado', 'El evento del webhook es "estudiante.registrado".')
    assert(resLogs.body[0].payload.data.documento === '1001', 'El payload contiene el documento del estudiante (1001).')

    // 5. Aprobar estudiante (desencadena: estudiante.aprobado)
    console.log('\n[TEST WEBHOOKS] 3. Aprobar estudiante para gatillar "estudiante.aprobado"')
    const resApprove = await request('POST', '/api/admin/estudiantes/aprobar', { documento: '1001' })
    assert(resApprove.status === 200, 'Aprobación de estudiante exitosa.')
    
    await sleep(500)

    resLogs = await request('GET', '/api/test/webhook-receiver/logs')
    assert(resLogs.body.length === 2, 'Receptor de pruebas recibió el segundo webhook (total 2).')
    assert(resLogs.body[0].payload.evento === 'estudiante.aprobado', 'El evento del webhook es "estudiante.aprobado".')
    assert(resLogs.body[0].payload.data.estado === 'Activo', 'El estado del estudiante es "Activo".')

    // 6. Marcar asistencia (desencadena: asistencia.registrada)
    console.log('\n[TEST WEBHOOKS] 4. Marcar asistencia para gatillar "asistencia.registrada"')
    const resAsistencia = await request('POST', '/api/asistencia', { documento: '1001' })
    assert(resAsistencia.status === 200, 'Registro de asistencia exitoso.')

    await sleep(500)

    resLogs = await request('GET', '/api/test/webhook-receiver/logs')
    assert(resLogs.body.length === 3, 'Receptor de pruebas recibió el tercer webhook (total 3).')
    assert(resLogs.body[0].payload.evento === 'asistencia.registrada', 'El evento del webhook es "asistencia.registrada".')
    assert(resLogs.body[0].payload.data.codigo_comprobante.startsWith('EVE-'), 'El comprobante inicia con "EVE-".')

    // 7. Simular 3 inasistencias para provocar la suspensión (desencadena: estudiante.suspendido)
    console.log('\n[TEST WEBHOOKS] 5. Simular 3 inasistencias para gatillar "estudiante.suspendido"')
    // Registrar y aprobar a 1002 para poder simular sus asistencias
    await request('POST', '/api/estudiantes/registro', {
      documento: '1002',
      nombres: 'Maria',
      apellidos: 'Gomez',
      grupo: '11-A'
    })
    await request('POST', '/api/admin/estudiantes/aprobar', { documento: '1002' })

    // Para inasistencias de 1001, simulamos días futuros donde solo asiste 1002
    await request('POST', '/api/admin/simular-dia', { fecha: '2026-07-01', asistentes: ['1002'] })
    await request('POST', '/api/admin/simular-dia', { fecha: '2026-07-02', asistentes: ['1002'] })
    await request('POST', '/api/admin/simular-dia', { fecha: '2026-07-03', asistentes: ['1002'] })

    await sleep(500)

    resLogs = await request('GET', '/api/test/webhook-receiver/logs')
    // Debe haber un webhook para estudiante.suspendido (total 6 webhooks registrados en total)
    assert(resLogs.body.length >= 6, 'Receptor recibió el webhook de suspensión.')
    const suspensionLog = resLogs.body.find(log => log.payload.evento === 'estudiante.suspendido')
    assert(suspensionLog !== undefined, 'Se encontró el log de suspensión en el receptor.')
    assert(suspensionLog.payload.data.estado === 'Suspendido', 'El estado del estudiante notificado es "Suspendido".')

  } catch (err) {
    console.error('❌ Error crítico en ejecución de pruebas de Webhooks:', err)
  } finally {
    // Shutdown server
    console.log('\n[TEST WEBHOOKS] Apagando el servidor Express API...')
    serverProc.kill()
    await sleep(500)
    console.log('====== RESUMEN DE RESULTADOS DE PRUEBAS DE WEBHOOKS ======')
    console.log(`  Pruebas ejecutadas: ${totalTests}`)
    console.log(`  Pruebas exitosas  : ${passedTests}`)
    
    if (passedTests === totalTests) {
      console.log('\n🎉 ¡TODAS LAS PRUEBAS DE WEBHOOKS SE COMPLETARON CON ÉXITO!')
      process.exit(0)
    } else {
      console.error('\n❌ Hubo fallos en las pruebas de integración de Webhooks. Revisa el registro anterior.')
      process.exit(1)
    }
  }
}

runTests()
