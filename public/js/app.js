const ROL = window.ROLL_USUARIO;
const API_BASE = '../api/usuarios';
const API_SETTINGS = '../api/settings';

function navigateTo(page) {
    document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
    event.target.closest('a').classList.add('active');

    document.getElementById('page-usuarios').style.display = 'none';
    const pageConfig = document.getElementById('page-configuracion');
    if (pageConfig) pageConfig.style.display = 'none';

    if (page === 'usuarios') {
        document.getElementById('page-title').textContent = 'Usuarios';
        document.getElementById('page-usuarios').style.display = 'block';
        cargarUsuarios();
    } else if (page === 'configuracion') {
        document.getElementById('page-title').textContent = 'Configuracion';
        document.getElementById('page-configuracion').style.display = 'block';
        cargarSettings();
    }
}

async function cargarSettings() {
    try {
        const resp = await fetch(`${API_SETTINGS}/obtener.php`);
        const datos = await resp.json();
        if (datos.success) {
            document.getElementById('toggle-token').checked = datos.data.token_required;
        }
    } catch (e) {
        console.error('Error al cargar configuracion');
    }
}

async function toggleTokenRequired() {
    const checked = document.getElementById('toggle-token').checked;
    try {
        const resp = await fetch(`${API_SETTINGS}/actualizar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token_required: checked })
        });
        const datos = await resp.json();
        if (!datos.success) {
            alert(datos.message);
            document.getElementById('toggle-token').checked = !checked;
        }
    } catch (e) {
        alert('Error al actualizar configuracion');
        document.getElementById('toggle-token').checked = !checked;
    }
}

function cerrarSesion() {
    window.location.href = '../api/auth/logout.php';
}

async function cargarUsuarios() {
    const tbody = document.getElementById('tabla-usuarios');
    tbody.innerHTML = '<tr><td colspan="7" class="loading">Cargando...</td></tr>';

    try {
        const resp = await fetch(`${API_BASE}/listarUsuarios.php`);
        const datos = await resp.json();

        if (!datos.success || !datos.data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No hay usuarios</td></tr>';
            return;
        }

        tbody.innerHTML = datos.data.map(u => `
            <tr>
                <td>${u.IdUsuario}</td>
                <td>${escapeHtml(u.PrimerNombre)}</td>
                <td>${escapeHtml(u.PrimerApellido)}</td>
                <td>${escapeHtml(u.NombreUsuario)}</td>
                <td>${escapeHtml(u.Correo)}</td>
                <td><span class="badge badge-${getBadgeRol(u.Rol)}">${escapeHtml(u.Rol)}</span></td>
                <td class="actions">
                    <button class="btn btn-info btn-sm" onclick="verUsuario(${u.IdUsuario})">Ver</button>
                    ${ROL === 1 ? `<button class="btn btn-warning btn-sm" onclick="editarUsuario(${u.IdUsuario})">Editar</button>` : ''}
                    ${ROL === 2 ? `<button class="btn btn-warning btn-sm" onclick="editarUsuario(${u.IdUsuario})">Editar</button>` : ''}
                    ${ROL === 1 ? `<button class="btn btn-danger btn-sm" onclick="confirmarEliminar(${u.IdUsuario})">Eliminar</button>` : ''}
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-state">Error al cargar usuarios</td></tr>';
    }
}

async function verUsuario(id) {
    try {
        const resp = await fetch(`${API_BASE}/obtenerUsuario.php?id=${id}`);
        const datos = await resp.json();

        if (!datos.success) {
            alert(datos.message);
            return;
        }

        const u = datos.usuario;
        document.getElementById('ver-id').value = u.IdUsuario;
        document.getElementById('ver-primer-nombre').value = u.PrimerNombre || '';
        document.getElementById('ver-segundo-nombre').value = u.SegundoNombre || '';
        document.getElementById('ver-primer-apellido').value = u.PrimerApellido || '';
        document.getElementById('ver-segundo-apellido').value = u.SegundoApellido || '';
        document.getElementById('ver-nombre-usuario').value = u.NombreUsuario || '';
        document.getElementById('ver-correo').value = u.Correo || '';
        document.getElementById('ver-rol').value = u.Rol || '';
        document.getElementById('modal-ver').classList.add('active');
    } catch (e) {
        alert('Error al obtener usuario');
    }
}

function cerrarModalVer() {
    document.getElementById('modal-ver').classList.remove('active');
}

function abrirModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Crear Usuario';
    document.getElementById('edit-id-usuario').value = '';
    document.getElementById('edit-id-credencial').value = '';
    document.getElementById('campo-primer-nombre').value = '';
    document.getElementById('campo-segundo-nombre').value = '';
    document.getElementById('campo-primer-apellido').value = '';
    document.getElementById('campo-segundo-apellido').value = '';
    document.getElementById('campo-nombre-usuario').value = '';
    document.getElementById('campo-correo').value = '';
    document.getElementById('campo-contrasena').value = '';
    document.getElementById('campo-rol').value = '3';
    document.getElementById('group-correo').style.display = 'block';
    document.getElementById('group-contrasena').style.display = 'block';
    document.getElementById('group-rol').style.display = 'block';
    document.getElementById('modal-usuario').classList.add('active');
}

async function editarUsuario(id) {
    try {
        const resp = await fetch(`${API_BASE}/obtenerUsuario.php?id=${id}`);
        const datos = await resp.json();

        if (!datos.success) {
            alert(datos.message);
            return;
        }

        const u = datos.usuario;
        document.getElementById('modal-titulo').textContent = 'Editar Usuario';
        document.getElementById('edit-id-usuario').value = u.IdUsuario;
        document.getElementById('edit-id-credencial').value = u.IdCredencial;
        document.getElementById('campo-primer-nombre').value = u.PrimerNombre;
        document.getElementById('campo-segundo-nombre').value = u.SegundoNombre || '';
        document.getElementById('campo-primer-apellido').value = u.PrimerApellido;
        document.getElementById('campo-segundo-apellido').value = u.SegundoApellido || '';
        document.getElementById('campo-nombre-usuario').value = u.NombreUsuario;
        document.getElementById('group-correo').style.display = 'none';
        document.getElementById('group-contrasena').style.display = 'none';
        document.getElementById('group-rol').style.display = 'none';
        document.getElementById('modal-usuario').classList.add('active');
    } catch (e) {
        alert('Error al obtener usuario');
    }
}

async function guardarUsuario() {
    const id = document.getElementById('edit-id-usuario').value;
    const esEdicion = id !== '';

    const datos = {
        PrimerNombre: document.getElementById('campo-primer-nombre').value.trim(),
        SegundoNombre: document.getElementById('campo-segundo-nombre').value.trim(),
        PrimerApellido: document.getElementById('campo-primer-apellido').value.trim(),
        SegundoApellido: document.getElementById('campo-segundo-apellido').value.trim(),
        NombreUsuario: document.getElementById('campo-nombre-usuario').value.trim()
    };

    if (esEdicion) {
        datos.IdUsuario = parseInt(id);
        datos.IdCredencial = parseInt(document.getElementById('edit-id-credencial').value);
    } else {
        datos.Correo = document.getElementById('campo-correo').value.trim();
        datos.contrasena = document.getElementById('campo-contrasena').value;
        datos.IdRol = parseInt(document.getElementById('campo-rol').value);

        if (!datos.Correo || !datos.contrasena) {
            alert('Correo y contrasena son obligatorios');
            return;
        }
    }

    if (!datos.PrimerNombre || !datos.PrimerApellido || !datos.NombreUsuario) {
        alert('Los campos con * son obligatorios');
        return;
    }

    const endpoint = esEdicion ? 'editar.php' : 'crear.php';

    try {
        const resp = await fetch(`${API_BASE}/${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        const resultado = await resp.json();

        if (resultado.success) {
            cerrarModal();
            cargarUsuarios();
        } else {
            alert(resultado.message);
        }
    } catch (e) {
        alert('Error al guardar usuario');
    }
}

function confirmarEliminar(id) {
    document.getElementById('modal-confirmar').classList.add('active');
    document.getElementById('btn-confirmar-eliminar').onclick = () => eliminarUsuario(id);
}

async function eliminarUsuario(id) {
    try {
        const resp = await fetch(`${API_BASE}/eliminar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ IdUsuario: id })
        });
        const resultado = await resp.json();

        cerrarModalConfirm();

        if (resultado.success) {
            cargarUsuarios();
        } else {
            alert(resultado.message);
        }
    } catch (e) {
        alert('Error al eliminar usuario');
    }
}

function cerrarModal() {
    document.getElementById('modal-usuario').classList.remove('active');
}

function cerrarModalConfirm() {
    document.getElementById('modal-confirmar').classList.remove('active');
}

function getBadgeRol(rol) {
    const map = { 'administrador': 'danger', 'editor': 'warning', 'lector': 'info' };
    return map[rol.toLowerCase()] || 'secondary';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});
