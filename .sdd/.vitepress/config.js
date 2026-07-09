export default {
  title: 'Portal SDD',
  description: 'Documentacion Local del Proyecto Conecta',
  srcExclude: [
    '**/node_modules/**',
    '**/__MACOSX/**',
    'wiki/migracion/.vitepress/**',
    'wiki/migracion/node_modules/**'
  ],
  rewrites: {
    'wiki/migracion/modulos/:name.md': 'modulos/:name.md',
    'wiki/migracion/metodologia.md': 'metodologia.md',
    'wiki/migracion/plan.md': 'plan.md'
  },
  themeConfig: {
    nav: [
      { text: 'Inicio', link: '/' },
      { text: 'Wiki', link: '/wiki/README' }
    ],
    sidebar: [
      {
        text: 'Documentacion General',
        collapsed: false,
        items: [
          {
            text: 'Guias y Documentos (Wiki)',
            collapsed: false,
            items: [
              { text: 'Guia de Inicio', link: '/wiki/README' },
            { text: 'guia-docker', link: '/wiki/guia-docker' }
            ]
          },
          {
            text: 'Estandares y Convenciones',
            collapsed: true,
            items: [
              { text: 'Guía y Estándar de Webhooks', link: '/wiki/estandares/webhooks' }
            ]
          },
          {
            text: 'Negocio y Flujos',
            collapsed: true,
            items: [
              { text: 'Estrategia de Dockerización Independiente de Frontends', link: '/wiki/negocio/dockerizacion-frontends' },
              { text: 'Documento de Negocio: Control Interno y Panel Lateral (Aside) de Estudiantes', link: '/wiki/negocio/flujo-gestion-estudiantes' },
              { text: 'Flujo de Webhooks y Procesamiento Asíncrono', link: '/wiki/negocio/flujo-webhooks' },
              { text: 'Reglas y Lógica de Negocio: Control de Beneficio Alimentario', link: '/wiki/negocio/reglas-negocio' },
              { text: 'Arquitectura de Separación Front/Back con Laravel 13', link: '/wiki/negocio/separacion-front-back' }
            ]
          }
        ]
      },
      {
        text: 'Migracion de Modulos (QA)',
        collapsed: false,
        items: [
          {
            text: 'Informacion del Proceso',
            collapsed: false,
            items: [
              { text: 'Resumen de Validacion', link: '/wiki/migracion/index' },
              { text: 'Metodologia', link: '/metodologia' },
              { text: 'Plan de Trabajo', link: '/plan' }
            ]
          }
        ]
      },
      {
        text: 'Flujo de Trabajo SDD',
        collapsed: true,
        items: [
          {
            text: 'Especificaciones (Specs)',
            collapsed: true,
            items: [
            { text: 'dockerizacion-frontends', link: '/specs/dockerizacion-frontends' },
            { text: 'frontend-monorepo', link: '/specs/frontend-monorepo' },
            { text: 'laravel-migration', link: '/specs/laravel-migration' },
            { text: 'redisenio-layout', link: '/specs/redisenio-layout' },
            { text: 'roles-asistencia', link: '/specs/roles-asistencia' }
            ]
          },
          {
            text: 'Planes Tecnicos (Plans)',
            collapsed: true,
            items: [
            { text: 'dockerizacion-frontends', link: '/plans/dockerizacion-frontends' },
            { text: 'frontend-monorepo', link: '/plans/frontend-monorepo' },
            { text: 'laravel-migration', link: '/plans/laravel-migration' },
            { text: 'redisenio-layout', link: '/plans/redisenio-layout' },
            { text: 'roles-asistencia', link: '/plans/roles-asistencia' }
            ]
          },
          {
            text: 'Tareas (Tasks)',
            collapsed: true,
            items: [
            { text: 'dockerizacion-frontends', link: '/tasks/dockerizacion-frontends' },
            { text: 'frontend-monorepo', link: '/tasks/frontend-monorepo' },
            { text: 'laravel-migration', link: '/tasks/laravel-migration' },
            { text: 'redisenio-layout', link: '/tasks/redisenio-layout' },
            { text: 'roles-asistencia', link: '/tasks/roles-asistencia' }
            ]
          }
        ]
      }
    ]
  }
}