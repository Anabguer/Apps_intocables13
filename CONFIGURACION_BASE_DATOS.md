# Configuración de Base de Datos

## 📋 Descripción

La configuración de base de datos está separada del resto de configuraciones del sistema para mayor seguridad y flexibilidad.

## 🔧 Archivos de Configuración

### `includes/database.php` (Desarrollo)
- **Uso**: Configuración para desarrollo local (XAMPP)
- **Modificación**: Solo manualmente, NO desde el panel de administración
- **Contenido**: Host, usuario, contraseña y nombre de la base de datos local

### `includes/database.production.example.php` (Producción)
- **Uso**: Plantilla para configuración de producción
- **Modificación**: Copiar y personalizar para el servidor de producción
- **Contenido**: Ejemplo de configuración para servidor en la nube

## 🚀 Cómo Configurar para Producción

### 1. Preparar el Archivo de Producción
```bash
# Copiar el archivo de ejemplo
cp includes/database.production.example.php includes/database.php
```

### 2. Modificar los Valores
Editar `includes/database.php` con los datos de tu servidor:

```php
// Configuración para producción
define('DB_HOST', 'tu-servidor-bd.com');
define('DB_USER', 'tu_usuario_bd');
define('DB_PASSWORD', 'tu_password_seguro');
define('DB_NAME', 'intocables_production');
```

### 3. Subir a Producción
- Subir el archivo `includes/database.php` con la configuración correcta
- **NO subir** `includes/database.production.example.php`

## ⚠️ Importante

### Seguridad
- **NUNCA** incluir `database.php` en el control de versiones (Git)
- Los datos de conexión son sensibles y específicos del entorno
- Cada entorno (desarrollo/producción) debe tener su propia configuración

### Separación de Responsabilidades
- **Base de datos**: Configuración específica del entorno
- **Sistema**: Configuración modificable desde el panel de administración
- **Panel de admin**: Solo puede modificar configuraciones del sistema, NO de base de datos

## 📁 Estructura de Archivos

```
includes/
├── database.php                    # Configuración de BD (NO en Git)
├── database.production.example.php # Plantilla para producción
├── config.php                      # Configuración del sistema
└── auth.php                        # Autenticación
```

## 🔄 Flujo de Trabajo

1. **Desarrollo**: Usar `database.php` con configuración local
2. **Producción**: Crear `database.php` con configuración del servidor
3. **Panel Admin**: Solo modificar configuraciones del sistema en `config.php`
4. **Base de datos**: Modificar manualmente según el entorno
