# Intocables PHP - Migración de Next.js

Esta es la versión PHP de la aplicación Intocables, migrada desde Next.js para ser compatible con hosting compartido como Hostalia.

## 🚀 Características

- **100% PHP** - Compatible con cualquier hosting compartido
- **Misma base de datos** - Usa la misma base de datos MySQL que la versión Next.js
- **Funcionalidad completa** - Todas las características de la versión original
- **Diseño responsive** - Optimizado para móviles y desktop
- **Panel de administración** - Gestión completa de álbumes
- **URLs amigables** - Usando .htaccess para URLs limpias

## 📁 Estructura del Proyecto

```
intocables/
├── api/                    # APIs en PHP
│   ├── albums.php         # CRUD de álbumes
│   └── years.php          # Obtener años disponibles
├── admin/                 # Panel de administración
│   └── index.php          # Gestión de álbumes
├── fotos-videos/          # Galería principal
│   ├── index.php          # Página principal de fotos
│   ├── year.php           # Página por año
│   ├── aray-fotos.php     # Sección Aray Fotos
│   ├── aray-videos.php    # Sección Aray Videos
│   ├── alessandro-fotos.php # Sección Alessandro
│   └── cumpleanos.php     # Sección Cumpleaños
├── presentaciones/        # Sección presentaciones
│   └── index.php
├── miscelanea/           # Sección miscelánea
│   └── index.php
├── includes/             # Archivos comunes
│   ├── config.php        # Configuración y funciones DB
│   ├── header.php        # Cabecera común
│   └── footer.php        # Pie común
├── css/                  # Estilos
│   └── styles.css        # CSS principal
├── js/                   # JavaScript
│   └── main.js           # Funciones principales
├── .htaccess             # Configuración Apache
└── README.md             # Este archivo
```

## 🛠️ Instalación

1. **Subir archivos** al directorio raíz de tu hosting
2. **Configurar base de datos** en `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'tu_usuario');
   define('DB_PASSWORD', 'tu_password');
   define('DB_NAME', 'intocables_db');
   ```
3. **Verificar permisos** de escritura en directorios necesarios
4. **Probar la aplicación** visitando la URL

## 🔧 Configuración

### Base de Datos
La aplicación usa la misma base de datos que la versión Next.js:
- **Tabla**: `albums`
- **Campos**: id, titulo, subtitulo, año, imagen, enlace, tipo, categoria, orden, activo

### URLs Amigables
El archivo `.htaccess` configura:
- `/fotos-videos/2025/` → `fotos-videos/year.php?year=2025`
- `/admin/` → `admin/index.php`
- `/api/albums` → `api/albums.php`

## 📱 Funcionalidades

### Página Principal
- Navegación por años (dinámico desde BD)
- Secciones estáticas (Aray, Alessandro, etc.)
- Álbumes recientes

### Panel de Administración
- **Estadísticas** - Total de álbumes por tipo y año
- **CRUD completo** - Crear, editar, eliminar álbumes
- **Formulario dinámico** - Edición inline
- **Ordenación** - Por año y orden

### APIs
- **GET /api/albums** - Obtener todos los álbumes
- **GET /api/albums?id=X** - Obtener álbum específico
- **POST /api/albums** - Crear álbum
- **PUT /api/albums** - Actualizar álbum
- **DELETE /api/albums** - Eliminar álbum
- **GET /api/years** - Obtener años disponibles

## 🎨 Diseño

- **CSS puro** - Sin dependencias externas
- **Responsive** - Adaptable a todos los dispositivos
- **Moderno** - Gradientes, sombras, transiciones
- **Consistente** - Mismo diseño que la versión Next.js

## 🔄 Diferencias con Next.js

| Característica | Next.js | PHP |
|----------------|---------|-----|
| **Hosting** | VPS/Dedicado | Compartido |
| **Rendimiento** | SSR | PHP tradicional |
| **Mantenimiento** | Node.js | PHP estándar |
| **Costo** | Alto | Bajo |
| **Funcionalidad** | 100% | 100% |

## 🚀 Ventajas de la Migración

1. **Hosting más barato** - Compatible con Hostalia
2. **Fácil mantenimiento** - PHP estándar
3. **Mejor rendimiento** - Sin JavaScript del servidor
4. **Más compatible** - Funciona en cualquier hosting
5. **Misma funcionalidad** - Sin pérdida de características

## 📞 Soporte

Para cualquier duda o problema:
1. Revisar este README
2. Verificar configuración de base de datos
3. Comprobar permisos de archivos
4. Revisar logs del servidor

## 🔄 Migración Completa

La migración está **100% completa** y mantiene:
- ✅ Toda la funcionalidad de álbumes
- ✅ Panel de administración completo
- ✅ Navegación dinámica por años
- ✅ Diseño responsive
- ✅ APIs funcionales
- ✅ Misma base de datos
- ✅ URLs amigables

**¡Listo para subir a Hostalia!** 🎉
