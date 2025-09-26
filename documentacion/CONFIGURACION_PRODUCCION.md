# 🚀 Configuración para Producción - Intocables PHP

## 📋 Checklist de Producción

### 1. **Configuración de Base de Datos**
- ✅ **Archivo de configuración**: Crear `includes/database.php` con datos reales
- ✅ **Backup de datos**: Exportar base de datos de desarrollo
- ✅ **Importar en producción**: Restaurar datos en el servidor

### 2. **Configuración de Correos**
- ✅ **SMTP real**: Configurar servidor SMTP del hosting
- ✅ **Contraseña de aplicación**: Para Gmail o similar
- ✅ **Probar envío**: Verificar que los correos llegan

### 3. **Configuración de Archivos**
- ✅ **Permisos de carpetas**: `img/`, `uploads/`, etc.
- ✅ **Tamaño máximo**: Ajustar según hosting
- ✅ **Extensiones permitidas**: Verificar compatibilidad

### 4. **Configuración de Seguridad**
- ✅ **HTTPS**: Certificado SSL activo
- ✅ **Contraseñas fuertes**: Cambiar contraseñas por defecto
- ✅ **Permisos de archivos**: Archivos de configuración protegidos

## 🔧 Pasos Detallados

### **Paso 1: Base de Datos**

#### **Crear archivo de producción:**
```php
// includes/database.php
<?php
// CONFIGURACIÓN DE PRODUCCIÓN - NO MODIFICAR
define('DB_HOST', 'localhost'); // O la IP del servidor
define('DB_USER', 'usuario_produccion');
define('DB_PASSWORD', 'contraseña_segura');
define('DB_NAME', 'intocables_produccion');

function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        die("Error de conexión a la base de datos");
    }
}
?>
```

#### **Exportar datos de desarrollo:**
```sql
-- Exportar estructura y datos
mysqldump -u root -p intocables > backup_desarrollo.sql
```

#### **Importar en producción:**
```sql
-- En el servidor de producción
mysql -u usuario_produccion -p intocables_produccion < backup_desarrollo.sql
```

### **Paso 2: Configuración de Correos**

#### **En el panel de administración:**
1. **Ve a Administración → Configuración → Notificaciones**
2. **Configura los campos:**
   - **Servidor SMTP**: `smtp.tu-hosting.com` (o Gmail)
   - **Puerto SMTP**: `587` o `465`
   - **Usuario SMTP**: `noreply@tudominio.com`
   - **Contraseña SMTP**: `contraseña_segura`
   - **Email de Envío**: `noreply@tudominio.com`
   - **Nombre del Remitente**: `INTOCABLES`

#### **Para Gmail (recomendado):**
1. **Activar verificación en 2 pasos**
2. **Generar contraseña de aplicación**
3. **Usar datos de Gmail**

### **Paso 3: Configuración de Archivos**

#### **Permisos de carpetas:**
```bash
# En el servidor (Linux)
chmod 755 img/
chmod 755 img/alessandro/
chmod 755 uploads/
chmod 644 includes/database.php
chmod 644 includes/config.php
```

#### **Archivo .htaccess (si es necesario):**
```apache
# Proteger archivos de configuración
<Files "database.php">
    Order Allow,Deny
    Deny from all
</Files>

# Configuración de PHP
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
```

### **Paso 4: Configuración de Seguridad**

#### **Cambiar contraseñas por defecto:**
1. **Admin**: Cambiar contraseña del administrador
2. **Base de datos**: Usar contraseñas seguras
3. **SMTP**: Contraseñas de aplicación

#### **Configurar HTTPS:**
- **Certificado SSL**: Activar en el hosting
- **Redirección HTTP → HTTPS**: Configurar en .htaccess
- **URLs actualizadas**: Cambiar `http://` por `https://`

### **Paso 5: Configuración del Hosting**

#### **Requisitos del servidor:**
- **PHP 7.4+** (recomendado 8.0+)
- **MySQL 5.7+** o **MariaDB 10.3+**
- **Extensiones PHP**: PDO, mbstring, fileinfo
- **Espacio en disco**: Mínimo 1GB
- **Memoria PHP**: Mínimo 128MB

#### **Configuración PHP:**
```ini
; php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

## 🔍 Verificación Post-Despliegue

### **1. Probar funcionalidades básicas:**
- ✅ **Login**: Verificar que funciona
- ✅ **Recuperación de contraseña**: Probar envío de correos
- ✅ **Subida de archivos**: Probar subida de fotos/videos
- ✅ **Panel de administración**: Verificar acceso

### **2. Probar correos:**
- ✅ **Solicitar recuperación**: Introducir email
- ✅ **Verificar recepción**: Comprobar bandeja de entrada
- ✅ **Probar código**: Introducir código recibido

### **3. Verificar rendimiento:**
- ✅ **Tiempo de carga**: Páginas cargan rápido
- ✅ **Subida de archivos**: Funciona correctamente
- ✅ **Base de datos**: Consultas rápidas

## 🚨 Problemas Comunes y Soluciones

### **Error: "No se puede conectar a la base de datos"**
- Verificar datos en `includes/database.php`
- Comprobar que la base de datos existe
- Verificar permisos del usuario

### **Error: "No se pueden enviar correos"**
- Verificar configuración SMTP
- Comprobar contraseña de aplicación
- Verificar que el hosting permite envío de correos

### **Error: "No se pueden subir archivos"**
- Verificar permisos de carpetas
- Comprobar límites de PHP
- Verificar espacio en disco

### **Error: "Página no encontrada"**
- Verificar configuración de .htaccess
- Comprobar que mod_rewrite está activo
- Verificar rutas en la configuración

## 📞 Soporte Post-Despliegue

### **Archivos importantes:**
- `includes/database.php` - Configuración de BD
- `includes/config.php` - Configuración del sistema
- `.htaccess` - Configuración de Apache
- `CONFIGURACION_CORREOS.md` - Guía de correos

### **Logs a revisar:**
- **Error logs del servidor**: Para errores PHP
- **Logs de correo**: Para problemas de envío
- **Logs de acceso**: Para problemas de permisos

¡Con esta configuración tendrás el sistema funcionando perfectamente en producción! 🎉
