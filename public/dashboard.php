<?php
session_start();

if (empty($_SESSION["autenticado"])) {
    header("Location: index.html");
    exit;
}

$rol = $_SESSION["rol"];
$nombre = $_SESSION["nombre"];
$apellido = $_SESSION["apellido"];
$correo = $_SESSION["correo"];
$idUsuario = $_SESSION["idUsuario"];
$rolNombre = ["1" => "Administrador", "2" => "Editor", "3" => "Lector"][$rol] ?? "Desconocido";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SecureData Corp</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>SecureData Corp</h2>
                <span><?php echo htmlspecialchars($rolNombre); ?></span>
            </div>
            <ul class="sidebar-menu">
                <li><a class="active" onclick="navigateTo('usuarios')"><span>Usuarios</span></a></li>
                <?php if ($rol == 1): ?>
                    <li><a onclick="navigateTo('configuracion')"><span>Configuracion</span></a></li>
                <?php endif; ?>
                <li><a onclick="cerrarSesion()">Cerrar Sesion</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="topbar">
                <h1 id="page-title">Usuarios</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($nombre . " " . $apellido); ?></span>
                </div>
            </div>

            <div id="page-usuarios">
                <div class="card">
                    <div class="card-header">
                        <h3>Lista de Usuarios</h3>
                        <?php if ($rol == 1): ?>
                            <button class="btn btn-primary btn-sm" onclick="abrirModalCrear()">+ Crear Usuario</button>
                        <?php endif; ?>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-usuarios">
                                <tr><td colspan="7" class="loading">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="page-configuracion" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h3>Configuracion del Sistema</h3>
                    </div>
                    <div style="padding: 10px 0;">
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:15px 0; border-bottom:1px solid #f0f2f5;">
                            <div>
                                <strong>Requerir token de correo para iniciar sesion</strong>
                                <p style="font-size:13px; color:#999; margin-top:4px;">Si esta desactivado, los usuarios podran iniciar sesion solo con correo y contrasena sin verificar por correo.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="toggle-token" onchange="toggleTokenRequired()">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-usuario" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modal-titulo">Crear Usuario</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id-usuario">
                <input type="hidden" id="edit-id-credencial">
                <div class="form-group">
                    <label>Primer Nombre</label>
                    <input type="text" id="campo-primer-nombre" required>
                </div>
                <div class="form-group">
                    <label>Segundo Nombre</label>
                    <input type="text" id="campo-segundo-nombre">
                </div>
                <div class="form-group">
                    <label>Primer Apellido</label>
                    <input type="text" id="campo-primer-apellido" required>
                </div>
                <div class="form-group">
                    <label>Segundo Apellido</label>
                    <input type="text" id="campo-segundo-apellido">
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" id="campo-nombre-usuario" required>
                </div>
                <div class="form-group" id="group-correo">
                    <label>Correo</label>
                    <input type="email" id="campo-correo" required>
                </div>
                <div class="form-group" id="group-contrasena">
                    <label>Contrasena</label>
                    <input type="password" id="campo-contrasena">
                </div>
                <div class="form-group" id="group-rol">
                    <label>Rol</label>
                    <select id="campo-rol">
                        <option value="1">Administrador</option>
                        <option value="2">Editor</option>
                        <option value="3">Lector</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarUsuario()">Guardar</button>
            </div>
        </div>
    </div>

    <div id="modal-confirmar" class="modal-overlay">
        <div class="modal" style="max-width: 350px;">
            <div class="modal-header">
                <h3>Confirmar</h3>
                <button class="modal-close" onclick="cerrarModalConfirm()">&times;</button>
            </div>
            <div class="modal-body text-center">
                <p id="confirmar-mensaje">Estas seguro de eliminar este usuario?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" onclick="cerrarModalConfirm()">Cancelar</button>
                <button class="btn btn-danger btn-sm" id="btn-confirmar-eliminar">Eliminar</button>
            </div>
        </div>
    </div>

    <div id="modal-ver" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Detalle del Usuario</h3>
                <button class="modal-close" onclick="cerrarModalVer()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>ID</label>
                    <input type="text" id="ver-id" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Primer Nombre</label>
                    <input type="text" id="ver-primer-nombre" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Segundo Nombre</label>
                    <input type="text" id="ver-segundo-nombre" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Primer Apellido</label>
                    <input type="text" id="ver-primer-apellido" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Segundo Apellido</label>
                    <input type="text" id="ver-segundo-apellido" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" id="ver-nombre-usuario" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Correo</label>
                    <input type="text" id="ver-correo" readonly style="background:#f0f2f5;">
                </div>
                <div class="form-group">
                    <label>Rol</label>
                    <input type="text" id="ver-rol" readonly style="background:#f0f2f5;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" onclick="cerrarModalVer()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>window.ROLL_USUARIO = <?php echo $rol; ?>;</script>
    <script src="js/app.js"></script>
</body>
</html>
