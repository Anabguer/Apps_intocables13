# 📧 Configuración de Correos - Sistema de Recuperación de Contraseñas

## 🎯 Configuración desde el Panel de Administración

Puedes configurar todos los parámetros de correo desde **Administración → Configuración → Configuración de Notificaciones**.

### 📋 Campos Disponibles:

#### **Servidor SMTP:**
- **Servidor SMTP**: `smtp.gmail.com` (para Gmail)
- **Puerto SMTP**: `587` (puerto estándar)
- **Usuario SMTP**: Tu email completo
- **Contraseña SMTP**: Contraseña de aplicación (NO tu contraseña normal)

#### **Información del Remitente:**
- **Email de Envío**: El correo desde el que se enviarán los mensajes
- **Nombre del Remitente**: El nombre que aparecerá como remitente

## 🔧 Configuración para Gmail

### 1. **Habilitar Verificación en 2 Pasos:**
1. Ve a tu cuenta de Google
2. Seguridad → Verificación en 2 pasos
3. Actívala si no está activada

### 2. **Generar Contraseña de Aplicación:**
1. Ve a Seguridad → Contraseñas de aplicaciones
2. Selecciona "Correo" y "Otro (nombre personalizado)"
3. Escribe "Intocables PHP" como nombre
4. Copia la contraseña generada (16 caracteres)

### 3. **Configurar en el Panel:**
- **Servidor SMTP**: `smtp.gmail.com`
- **Puerto SMTP**: `587`
- **Usuario SMTP**: `tu-email@gmail.com`
- **Contraseña SMTP**: `la-contraseña-de-16-caracteres`
- **Email de Envío**: `tu-email@gmail.com`
- **Nombre del Remitente**: `INTOCABLES`

## 🔧 Configuración para Otros Proveedores

### **Outlook/Hotmail:**
- **Servidor SMTP**: `smtp-mail.outlook.com`
- **Puerto SMTP**: `587`

### **Yahoo:**
- **Servidor SMTP**: `smtp.mail.yahoo.com`
- **Puerto SMTP**: `587`

### **Servidor Propio:**
- Consulta con tu proveedor de hosting los datos SMTP

## 🚀 Modo Desarrollo vs Producción

### **Desarrollo (sin SMTP):**
- Si no configuras SMTP, el sistema mostrará el código en pantalla
- Perfecto para pruebas locales
- No requiere configuración adicional

### **Producción (con SMTP):**
- Configura todos los campos SMTP
- Los correos se enviarán automáticamente
- Los usuarios recibirán códigos por email

## 📧 Plantilla del Correo

El sistema genera correos profesionales con:
- **Diseño responsive** para móviles y desktop
- **Logo y colores** del sitio
- **Código destacado** en caja especial
- **Instrucciones claras** paso a paso
- **Advertencia de seguridad** sobre expiración

## ⚠️ Solución de Problemas

### **Error: "Failed to connect to mailserver"**
- Verifica que el servidor SMTP sea correcto
- Comprueba que el puerto sea el adecuado
- En XAMPP, puede que necesites configurar PHP

### **Error: "Authentication failed"**
- Verifica el usuario y contraseña SMTP
- Para Gmail, usa contraseña de aplicación, no la normal
- Asegúrate de que la verificación en 2 pasos esté activada

### **Los correos no llegan:**
- Revisa la carpeta de spam
- Verifica que el email de envío sea válido
- Comprueba que el servidor SMTP esté funcionando

## 🔒 Seguridad

- **Códigos de 6 dígitos** generados aleatoriamente
- **Expiración de 15 minutos** automática
- **Contraseñas hasheadas** con password_hash()
- **Validación estricta** de códigos y tiempo
- **Limpieza automática** de códigos usados

## 📱 Funcionamiento

1. **Usuario solicita recuperación** → Introduce email
2. **Sistema genera código** → 6 dígitos + timestamp
3. **Envío de correo** → Plantilla HTML profesional
4. **Usuario introduce código** → + nueva contraseña
5. **Validación** → Código correcto + no expirado
6. **Actualización** → Nueva contraseña + limpieza

¡El sistema está listo para usar! 🎉
