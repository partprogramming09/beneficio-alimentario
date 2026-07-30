# 🏗️ DESIGN PLAN: Auto-Creación y Resolución de Estudiantes en Actualización

## 🛠️ Plan de Modificaciones

### 1. `backend/app/Modules/Admin/Services/StudentManagementService.php`
- Modificar `updateStudent()`:
```php
$docOriginal = trim((string)($data['documento_original'] ?? $data['documento'] ?? ''));
$docNuevo = trim((string)($data['documento'] ?? ''));

$institucion = InstitucionEstudiante::where('documento', $docOriginal)->first();
$estudiante = Estudiante::where('documento', $docOriginal)->first();

if (!$institucion && !$estudiante && $docOriginal !== $docNuevo) {
    $institucion = InstitucionEstudiante::where('documento', $docNuevo)->first();
    $estudiante = Estudiante::where('documento', $docNuevo)->first();
}

if (!$institucion && !$estudiante) {
    $targetDoc = !empty($docNuevo) ? $docNuevo : $docOriginal;
    $institucion = InstitucionEstudiante::create([
        'documento' => $targetDoc,
        'nombre_completo' => $nombreCompleto,
        'grupo' => $grupo,
    ]);
    $docOriginal = $targetDoc;
}
```

### 2. Pruebas Backend (`backend/tests/Feature/DiningHallTest.php`)
- Agregar test `test_update_student_auto_creates_new_document_record` para probar la actualización con documento `1001163`.
