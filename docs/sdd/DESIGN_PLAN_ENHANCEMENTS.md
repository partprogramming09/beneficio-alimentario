# 🏛️ DESIGN PLAN: Ajustes Técnicos, Nomenclatura y Componentes UI

> **Proyecto:** Sistema de Beneficio Alimentario - I.E. Enrique Vélez Escobar  
> **Fecha:** 2026-07-30  

---

## 🏗️ 1. Ajustes en Backend (`App\Modules`)

### `StudentService::renounceBenefit($documento)`
```php
public function renounceBenefit(string $documento): array
{
    $institucion = InstitucionEstudiante::find($documento);
    if (!$institucion) {
        throw new Exception("El documento no se encuentra matriculado en la institución.");
    }

    $estudiante = Estudiante::find($documento);
    if ($estudiante) {
        if ($estudiante->estado === 'Inactivo') {
            throw new Exception("El beneficio ya se encuentra en estado inactivo.");
        }
        $estudiante->update(['estado' => 'Inactivo']);
    } else {
        $parsed = (new StudentManagementService($this->webhookService))->formatNames($institucion->nombre_completo);
        $estudiante = Estudiante::create([
            'documento' => $documento,
            'nombres' => $parsed['nombres'],
            'apellidos' => $parsed['apellidos'],
            'grupo' => $institucion->grupo,
            'estado' => 'Inactivo',
        ]);
    }

    return [
        'message' => 'Has renunciado voluntariamente al beneficio alimentario. El cupo ha sido liberado.',
        'estudiante' => $estudiante,
    ];
}
```

### `StudentManagementService::submitJustification($data)`
```php
public function submitJustification(array $data): Justificacion
{
    $documento = $data['documento'];
    $institucion = InstitucionEstudiante::find($documento);

    if (!$institucion) {
        throw new Exception("El documento no se encuentra matriculado en la institución.");
    }

    $estudiante = Estudiante::find($documento);
    if (!$estudiante) {
        $parsed = $this->formatNames($institucion->nombre_completo);
        $estudiante = Estudiante::create([
            'documento' => $documento,
            'nombres' => $parsed['nombres'],
            'apellidos' => $parsed['apellidos'],
            'grupo' => $institucion->grupo,
            'estado' => 'Suspendido',
        ]);
    }

    return Justificacion::create([
        'documento' => $documento,
        'fecha_inasistencia' => $data['fecha_inasistencia'],
        'motivo' => $data['motivo'],
        'estado' => 'Pendiente',
    ]);
}
```

### `StudentManagementService::updateStudent($data)`
Soportar el parámetro opcional `estado` (`Activo`, `Suspendido`, `Inactivo`, `Sin Registrar`) en la actualización. Si se recibe `Sin Registrar`, elimina o desvincula la fila de `estudiantes` de forma segura.

---

## 🎨 2. Favicon e Identidad Visual

En `frontend-coordinadora/index.html` y `frontend-estudiante/index.html`:
```html
<link rel="icon" type="image/png" href="/escudo.png" />
<title>Coordinación — Comedor Escolar | I.E. Enrique Vélez Escobar</title>
```

---

## 📱 3. Rediseño de Modal y Pestañas en Frontend Coordinadora

1. **Selector de Estado en Modal de Edición**:
   Incluir selector desplegable con opciones:
   - `Activo` (Inscrito y operando en comedor)
   - `Suspendido` (Bloqueado por 3 inasistencias)
   - `Inactivo` (Desactivado / Renunció al cupo)
   - `Sin Registrar` (Quitar cupo / Estado inicial)

2. **Nombres de Pestañas en Dashboard**:
   - `pendientes`: **Inscritos Hoy**
   - `listado`: **Gestión de Estudiantes**
   - `cursos`: **Matrícula Cursos & Grupos**
   - `asistencia`: **Asistencia Diaria**
   - `reportes`: **Reporte Semanal Acumulado**
   - `reactivaciones`: **Excusas & Reactivaciones**
   - `simulador`: **Simulador de Asistencia & Suspensiones**
