# 📋 SPEC: Auto-Creación y Resoluciòn Flexible de Estudiantes al Editar / Cambiar Estado

## 🎯 Objetivo
Eliminar el error *"Estudiante con documento X no encontrado en la lista de la institución ni en beneficiarios"*, permitiendo que cuando la Coordinadora edite un estudiante o ingrese/cambie un documento (como el documento `1001163`), el sistema resuelva el documento de forma flexible y auto-registre al estudiante en la institución si aún no existía.

---

## 📜 Reglas de Negocio

1. **Resolución Flexible de Documento:**
   - Buscar primero el estudiante por `documento_original`.
   - Si no existe por `documento_original`, buscar por el nuevo `documento`.
2. **Auto-Aprovisionamiento Institucional:**
   - Si el estudiante no existe ni en `institucion_estudiantes` ni en `estudiantes`, auto-crear la entrada institucional en `institucion_estudiantes` utilizando el nombre completo y grupo especificados.
3. **Gestión de Estados:**
   - Aplicar el cambio de estado (`Activo`, `Suspendido`, `Inactivo`, `Sin Registrar`) sobre el perfil de beneficiario `estudiantes`.
   - Si el estado seleccionado es `Activo`, `Suspendido` o `Inactivo`, crear o actualizar el registro correspondiente en `estudiantes`.
4. **Respuesta Transaccional:**
   - Devolver un mensaje de éxito indicando que los datos del estudiante han sido guardados/actualizados correctamente.

---

## 🔍 Requisitos Técnicos

### Backend (`backend/app/Modules/Admin/Services/StudentManagementService.php`)
- Actualizar `updateStudent()` para incorporar el flujo de resolución secundaria por `documento` y auto-aprovisionamiento cuando el registro no existe de forma previa.

### Tests (`backend/tests/Feature/DiningHallTest.php`)
- Agregar prueba PHPUnit para verificar la auto-creación y actualización de un documento completamente nuevo (ej. `1001163`).
