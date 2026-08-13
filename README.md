# SecureData Corp

Sistema de gestion de usuarios con autenticacion por correo electronico y control de acceso por roles.

## Requisitos

- PHP 8.0 o superior
- MySQL
- XAMPP, WAMP o similar
- Composer

## Instalacion

1. Clonar o copiar el proyecto en `htdocs` (XAMPP):

```
cd C:\xampp\htdocs
git clone https://github.com/J0sh2210/SecureData-corp.git SecureData-Corp
```

2. Instalar dependencias con Composer:

```
cd SecureData-Corp
composer install
```

3. Crear el archivo `.env` desde la plantilla:

```
copy .env.example .env
```

4. Configurar las variables de entorno en `.env`:

```
DB_HOST=localhost
DB_NAME=SecureDataCorp
DB_USER=root
DB_PASS=

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_password_de_aplicacion
MAIL_FROM_NAME="SecureData Corp"
APP_URL=http://localhost/SecureData-Corp
```

5. Importar la base de datos:

- Abrir phpMyAdmin (`http://localhost/phpmyadmin`)
- Crear una base de datos llamada `SecureDataCorp`
- Importar el archivo `sql/creardb.sql`

6. Configurar el envio de correo (SMTP):

- Si usas Gmail: activar verificacion en 2 pasos y generar una contraseña de aplicacion en https://myaccount.google.com/apppasswords
- Colocar la contraseña de aplicacion en `MAIL_PASSWORD` del `.env`

## Funcionamiento

### Flujo de inicio de sesion

1. El usuario ingresa a `public/index.html`
2. Ingresa su correo y contrasena
3. Si el token esta activado, recibe un enlace por correo para iniciar sesion
4. Si el token esta desactivado, ingresa directamente al dashboard
5. El admin puede activar/desactivar el token desde Configuracion

### Roles

| Rol | Permisos |
|-----|----------|
| Administrador | Ver, crear, editar y eliminar usuarios. Acceder a configuracion |
| Editor | Ver y editar usuarios |
| Lector | Solo ver usuarios |

### Configuracion

El administrador puede activar o desactivar la verificacion por correo electronico desde el menu **Configuracion** en el sidebar.

# Importante

La autenticacion esta deshabilitada para facil ingreso, para activarlo en el dashboard dirigirse a configuracion y activar


## Crear usuario de prueba

1. Abrir phpMyAdmin y ejecutar la siguiente consulta SQL:

```sql
-- Crear credenciales (contrasena: test123)
INSERT INTO Credencial (NombreUsuario, Correo, Contrasena)
VALUES ('testuser', 'tu_correo@gmail.com', '$2y$10$YF1J4qKjG4rHkT1eQxOeY.8z3Xw6a9b2c1d4e5f6g7h8i9j0k1l');

-- Crear usuario asociado
INSERT INTO Usuario (PrimerNombre, SegundoNombre, PrimerApellido, SegundoApellido, IdRol, IdCredencial)
VALUES ('Juan', 'Carlos', 'Perez', 'Lopez', 3, LAST_INSERT_ID());
```

2. O generar el hash de la contrasena con PHP:

```php
<?php echo password_hash('tu_contrasena', PASSWORD_DEFAULT); ?>
```

3. Reemplazar el hash en el INSERT de Credencial.

4. Roles disponibles:
   - `1` = Administrador
   - `2` = Editor
   - `3` = Lector

## Estructura del proyecto

```
SecureData-Corp/
├── api/
│   ├── auth/
│   │   ├── login.php              # Inicio de sesion
│   │   ├── logout.php             # Cierre de sesion
│   │   ├── verificar_token_email.php  # Verificacion de token
│   │   └── testcorreo.php         # Test de envio SMTP
│   ├── usuarios/
│   │   ├── listarUsuarios.php     # Listar todos los usuarios
│   │   ├── obtenerUsuario.php     # Obtener un usuario por ID
│   │   ├── crear.php              # Crear usuario
│   │   ├── editar.php             # Editar usuario
│   │   └── eliminar.php           # Eliminar usuario
│   └── settings/
│       ├── obtener.php            # Obtener configuracion
│       └── actualizar.php         # Actualizar configuracion
├── config/
│   ├── conexion.php               # Conexion PDO a MySQL
│   ├── env.php                    # Carga de variables de entorno
│   ├── correo.php                 # Configuracion SMTP
│   ├── roles.json                 # Permisos por rol
│   └── settings.json              # Configuracion del sistema
├── middleware/
│   └── AuthMiddleware.php         # Autenticacion y permisos
├── services/
│   ├── mail_service.php           # Envio de correos
│   └── token_service.php          # Gestion de tokens
├── public/
│   ├── index.html                 # Pagina de login
│   ├── dashboard.php              # Panel principal
│   ├── css/
│   │   └── styles.css             # Estilos
│   └── js/
│       └── app.js                 # Logica del dashboard
├── sql/
│   └── creardb.sql                # Script de base de datos
├── .env.example                   # Plantilla de entorno
├── composer.json                  # Dependencias PHP
└── README.md
```

## Endpoints API

### Auth

| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| POST | `api/auth/login.php` | Iniciar sesion |
| GET | `api/auth/verificar_token_email.php?token=...` | Verificar token de correo |
| GET | `api/auth/logout.php` | Cerrar sesion |

### Usuarios

| Metodo | Ruta | Permiso requerido |
|--------|------|-------------------|
| GET | `api/usuarios/listarUsuarios.php` | ver_usuarios |
| GET | `api/usuarios/obtenerUsuario.php?id=X` | ver_usuarios |
| POST | `api/usuarios/crear.php` | crear_usuario |
| POST | `api/usuarios/editar.php` | editar_usuario |
| POST | `api/usuarios/eliminar.php` | eliminar_usuario |

### Configuracion (solo admin)

| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| GET | `api/settings/obtener.php` | Obtener configuracion |
| POST | `api/settings/actualizar.php` | Actualizar configuracion |

### Demostracion de uso

1. Loguearse

<img width="1912" height="1011" alt="image" src="https://github.com/user-attachments/assets/3cc9dda4-cea3-4608-bf9c-5504552091ee" />

2. Envio del correo al iniciar sesion
    
<img width="1911" height="973" alt="image" src="https://github.com/user-attachments/assets/95addfe2-759d-4c1d-a88f-61a9b21fd246" />

3.Abrir link de inicio

<img width="1384" height="454" alt="image" src="https://github.com/user-attachments/assets/5933dad4-f167-43ab-a9e3-507e79ec5742" />

4. Dashboard
   
   <img width="1918" height="968" alt="image" src="https://github.com/user-attachments/assets/bab94ffe-914a-432c-bc72-629396068669" />

6. Ver datos

<img width="1919" height="976" alt="image" src="https://github.com/user-attachments/assets/6843b5eb-784f-4538-af8b-b60d19efb40e" />

   
7. Editar

<img width="1909" height="969" alt="image" src="https://github.com/user-attachments/assets/4b76831b-07d9-4728-ad9e-b542115c87f2" />
   
8. Eliminar

<img width="1919" height="967" alt="image" src="https://github.com/user-attachments/assets/50300b4e-c9c8-4291-8d14-1a8cb0aa18d7" />

    
10. Desactivar autenticacion

<img width="1905" height="940" alt="image" src="https://github.com/user-attachments/assets/4ec1f4f0-4699-45de-8e87-c960d5edd326" />

12. Crear Usuario

<img width="1919" height="961" alt="image" src="https://github.com/user-attachments/assets/d2e4d969-d877-41a3-b72a-543d208a641f" />







