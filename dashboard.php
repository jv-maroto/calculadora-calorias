<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FitTracker</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #fafafa;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .header {
            background: white;
            border-bottom: 1px solid #e5e5e5;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 16px;
            color: #666;
        }

        .module-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 2rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .module-card:hover {
            border-color: #1a1a1a;
        }

        .module-icon {
            font-size: 48px;
            margin-bottom: 1rem;
        }

        .module-card h2 {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .module-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 1rem;
        }

        .stat-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            font-size: 12px;
            color: #666;
            margin: 4px;
        }

        .logout-btn {
            display: inline-block;
            padding: 12px 24px;
            background: white;
            border: 1px solid #e5e5e5;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
        }

        .logout-btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        @media (max-width: 768px) {
            .module-grid {
                grid-template-columns: 1fr !important;
            }

            .calendario {
                padding: 1rem;
            }

            .calendario-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .calendario-header h3 {
                font-size: 1rem;
            }

            .ver-recordatorios-btn {
                padding: 0.5rem 0.75rem;
                font-size: 11px;
            }

            .fecha-actual-grande {
                padding: 1.5rem 0 1rem;
            }

            .mes-anio {
                font-size: 0.75rem;
            }

            .dia-numero-grande {
                font-size: 3rem;
            }

            .dia-semana {
                font-size: 0.875rem;
            }

            .actividades-hoy {
                max-height: 300px;
            }

            .actividad-item {
                padding: 0.75rem;
                gap: 0.75rem;
            }

            .actividad-indicator {
                width: 3px;
                height: 30px;
            }

            .actividad-titulo {
                font-size: 0.875rem;
            }

            .actividad-descripcion {
                font-size: 0.75rem;
            }

            .actividad-tipo {
                font-size: 0.625rem;
                padding: 3px 6px;
            }

            .expandir-btn {
                padding: 0.65rem 1.25rem;
                font-size: 0.8125rem;
            }

            .calendario-nav {
                justify-content: center;
                width: 100%;
            }

            .nav-btn {
                padding: 0.6rem 0.8rem;
                font-size: 14px;
                flex: 0 0 auto;
            }

            .mes-actual {
                min-width: 100px;
                font-size: 14px;
            }

            .calendario-grid {
                gap: 0.25rem;
            }

            .dia-header {
                font-size: 11px;
                padding: 0.4rem;
            }

            .dia-celda {
                min-height: 60px;
                padding: 0.4rem;
            }

            .dia-numero {
                font-size: 12px;
            }

            .evento-badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            .notificacion-item {
                padding: 0.75rem;
                font-size: 13px;
            }
        }

        /* Calendario estilo Apple */
        .calendario {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
        }

        .calendario-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .calendario-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
        }

        .fecha-actual-grande {
            text-align: center;
            padding: 1.5rem 0 1rem;
            border-bottom: 1px solid #e5e5e5;
            margin-bottom: 1rem;
        }

        .mes-anio {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .dia-numero-grande {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .dia-semana {
            font-size: 0.875rem;
            color: #666;
        }

        .actividades-hoy {
            max-height: 300px;
            overflow-y: auto;
        }

        .actividad-item {
            padding: 0.75rem;
            border: 1px solid #e5e5e5;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .actividad-item:hover {
            border-color: #1a1a1a;
            background: #fafafa;
        }

        .actividad-item.completado {
            background: #f0fdf4;
            border-color: #16a34a;
        }

        .actividad-indicator {
            width: 3px;
            height: 30px;
            flex-shrink: 0;
        }

        .actividad-indicator.peso {
            background: #1a1a1a;
        }

        .actividad-indicator.entrenamiento {
            background: #666;
        }

        .actividad-indicator.otro {
            background: #999;
        }

        .actividad-content {
            flex: 1;
        }

        .actividad-titulo {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .actividad-item.completado .actividad-titulo {
            color: #16a34a;
        }

        .actividad-descripcion {
            font-size: 0.75rem;
            color: #666;
        }

        .actividad-tipo {
            font-size: 0.6875rem;
            padding: 3px 6px;
            background: #fafafa;
            border: 1px solid #e5e5e5;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sin-actividades {
            text-align: center;
            padding: 2rem 1rem;
            color: #999;
            font-size: 0.875rem;
        }

        .btn-nuevo-recordatorio {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #1a1a1a;
            border: 1px solid #1a1a1a;
            color: white;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-nuevo-recordatorio:hover {
            background: #000;
        }

        /* Botón ver todos los recordatorios */
        .ver-recordatorios-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e5e5e5;
            color: #666;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            margin-left: 0.5rem;
        }

        .ver-recordatorios-btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        /* Notificaciones laterales */
        .notificaciones-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            width: 320px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .notificacion {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notificacion-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .notificacion-titulo {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .notificacion-descripcion {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 0.75rem;
        }

        .notificacion-acciones {
            display: flex;
            gap: 0.5rem;
        }

        .notif-btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #e5e5e5;
            background: white;
            cursor: pointer;
            transition: all 0.15s;
        }

        .notif-btn:hover {
            border-color: #1a1a1a;
        }

        .notif-btn.completar {
            background: #1a1a1a;
            color: white;
            border-color: #1a1a1a;
        }

        .btn-cerrar {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
            padding: 0;
        }

        .btn-cerrar:hover {
            color: #1a1a1a;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
        }

        .modal-header {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e5e5;
            font-size: 0.875rem;
        }

        .form-control:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid #e5e5e5;
            background: white;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn:hover {
            border-color: #1a1a1a;
        }

        .btn-primary {
            background: #1a1a1a;
            color: white;
            border-color: #1a1a1a;
        }

        .btn-primary:hover {
            background: #000;
        }

        @media (max-width: 768px) {
            .notificaciones-container {
                width: calc(100% - 2rem);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>FitTracker</h1>
        <p>Hola, <?php echo htmlspecialchars($nombre); ?> 👋</p>
    </div>

    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Módulos principales -->
        <div class="module-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem;">

            <!-- Módulo GYM -->
            <div class="module-card" onclick="location.href='gym_hub.php'">
                <h2>GYM</h2>
                <p>Rutinas, ejercicios y análisis de progreso</p>
                <div>
                    <span class="stat-badge">Rutinas</span>
                    <span class="stat-badge">Progreso</span>
                    <span class="stat-badge">Ejercicios</span>
                    <span class="stat-badge">Volumen</span>
                </div>
            </div>

            <!-- Módulo DIET -->
            <div class="module-card" onclick="location.href='diet_hub.php'">
                <h2>DIET</h2>
                <p>Nutrición, calorías y seguimiento de peso</p>
                <div>
                    <span class="stat-badge">Calculadora</span>
                    <span class="stat-badge">Reverse Diet</span>
                    <span class="stat-badge">Peso</span>
                    <span class="stat-badge">Gráficas</span>
                </div>
            </div>

        </div>

        <!-- Calendario -->
        <div class="calendario" style="margin-bottom: 2rem;">
            <div class="calendario-header">
                <h3>Hoy</h3>
                <div class="header-actions">
                    <button class="btn-nuevo-recordatorio" onclick="abrirModalNuevoRecordatorio()">
                        + Nuevo
                    </button>
                    <button class="ver-recordatorios-btn" onclick="abrirModalRecordatorios()">Ver Todos</button>
                </div>
            </div>

            <!-- Vista del día actual -->
            <div id="vista-hoy"></div>
        </div>

        <!-- Botón de cerrar sesión -->
        <div style="text-align: center;">
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>

    </div>

    <!-- Notificaciones laterales -->
    <div class="notificaciones-container" id="notificaciones-container"></div>

    <!-- Modal para agregar evento -->
    <div class="modal" id="modal-evento">
        <div class="modal-content">
            <div class="modal-header">Agregar Evento</div>
            <form id="form-evento">
                <div class="form-group">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="evento-fecha" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select class="form-control" id="evento-tipo" required>
                        <option value="peso">Registro de Peso</option>
                        <option value="entrenamiento">Entrenamiento</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" class="form-control" id="evento-titulo" placeholder="Ej: Push, Pull, Legs" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" id="evento-descripcion" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="evento-recordatorio">
                        <span class="form-label" style="margin: 0;">Es un recordatorio</span>
                    </label>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para ver todos los recordatorios -->
    <div class="modal" id="modal-recordatorios">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                Todos los Recordatorios
                <button class="btn-cerrar" onclick="cerrarModalRecordatorios()" style="float: right; margin-top: -0.5rem;">&times;</button>
            </div>
            <div id="lista-recordatorios-completa" style="max-height: 400px; overflow-y: auto;">
                <!-- Se llena dinámicamente -->
            </div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button class="btn" onclick="cerrarModalRecordatorios()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        let eventos = [];

        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const diasCompletos = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        async function cargarEventos() {
            try {
                const hoy = new Date();
                const mesActual = hoy.getMonth();
                const anioActual = hoy.getFullYear();
                const response = await fetch(`api_calendario.php?action=obtener_eventos&mes=${mesActual + 1}&anio=${anioActual}`);
                const data = await response.json();
                if (data.success) {
                    eventos = data.eventos;
                    renderizarVistaHoy();
                }
            } catch (error) {
                console.error('Error al cargar eventos:', error);
            }
        }

        function abrirModalNuevoRecordatorio() {
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('evento-fecha').value = hoy;
            document.getElementById('evento-recordatorio').checked = true;
            document.getElementById('modal-evento').classList.add('active');
        }

        function renderizarVistaHoy() {
            const hoy = new Date();
            const fechaHoy = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
            const actividadesHoy = eventos.filter(e => e.fecha === fechaHoy);

            let html = `
                <div class="fecha-actual-grande">
                    <div class="mes-anio">${meses[hoy.getMonth()]} ${hoy.getFullYear()}</div>
                    <div class="dia-numero-grande">${hoy.getDate()}</div>
                    <div class="dia-semana">${diasCompletos[hoy.getDay()]}</div>
                </div>
            `;

            if (actividadesHoy.length === 0) {
                html += `<div class="sin-actividades">No hay actividades programadas para hoy</div>`;
            } else {
                html += '<div class="actividades-hoy">';
                actividadesHoy.forEach(actividad => {
                    const completado = actividad.completado == 1 ? 'completado' : '';
                    const checkmark = actividad.completado == 1 ? '✓ ' : '';
                    html += `
                        <div class="actividad-item ${completado}" onclick="abrirModal('${fechaHoy}')">
                            <div class="actividad-indicator ${actividad.tipo}"></div>
                            <div class="actividad-content">
                                <div class="actividad-titulo">${checkmark}${actividad.titulo}</div>
                                ${actividad.descripcion ? `<div class="actividad-descripcion">${actividad.descripcion}</div>` : ''}
                            </div>
                            <div class="actividad-tipo">${actividad.tipo}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            document.getElementById('vista-hoy').innerHTML = html;
        }


        function cerrarModal() {
            document.getElementById('modal-evento').classList.remove('active');
            document.getElementById('form-evento').reset();
        }

        document.getElementById('form-evento').addEventListener('submit', async (e) => {
            e.preventDefault();

            const datos = {
                fecha: document.getElementById('evento-fecha').value,
                tipo: document.getElementById('evento-tipo').value,
                titulo: document.getElementById('evento-titulo').value,
                descripcion: document.getElementById('evento-descripcion').value,
                es_recordatorio: document.getElementById('evento-recordatorio').checked ? 1 : 0
            };

            try {
                const response = await fetch('api_calendario.php?action=crear_evento', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await response.json();
                if (result.success) {
                    cerrarModal();
                    cargarEventos();
                    if (datos.es_recordatorio && datos.fecha === new Date().toISOString().split('T')[0]) {
                        cargarRecordatorios();
                    }
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error al guardar el evento');
            }
        });

        async function cargarRecordatorios() {
            try {
                const response = await fetch('api_calendario.php?action=obtener_recordatorios_hoy');
                const data = await response.json();
                if (data.success) {
                    mostrarNotificaciones(data.recordatorios);
                }
            } catch (error) {
                console.error('Error al cargar recordatorios:', error);
            }
        }

        function mostrarNotificaciones(recordatorios) {
            const container = document.getElementById('notificaciones-container');
            container.innerHTML = '';

            recordatorios.forEach(recordatorio => {
                const notif = document.createElement('div');
                notif.className = 'notificacion';
                notif.innerHTML = `
                    <div class="notificacion-header">
                        <div class="notificacion-titulo">${recordatorio.titulo}</div>
                        <button class="btn-cerrar" onclick="eliminarRecordatorio(${recordatorio.id})">&times;</button>
                    </div>
                    <div class="notificacion-descripcion">${recordatorio.descripcion || 'Hoy toca ' + recordatorio.titulo}</div>
                    <div class="notificacion-acciones">
                        <button class="notif-btn" onclick="eliminarRecordatorio(${recordatorio.id})">Omitir</button>
                        <button class="notif-btn completar" onclick="completarRecordatorio(${recordatorio.id})">Completar</button>
                    </div>
                `;
                container.appendChild(notif);
            });
        }

        async function completarRecordatorio(id) {
            try {
                const response = await fetch('api_calendario.php?action=marcar_completado', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await response.json();
                if (result.success) {
                    cargarRecordatorios();
                    cargarEventos();
                    renderizarVistaHoy();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function eliminarRecordatorio(id) {
            try {
                const response = await fetch('api_calendario.php?action=eliminar_evento', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await response.json();
                if (result.success) {
                    cargarRecordatorios();
                    cargarEventos();
                    renderizarVistaHoy();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Modal de todos los recordatorios
        async function abrirModalRecordatorios() {
            try {
                const response = await fetch('api_calendario.php?action=obtener_todos_recordatorios');
                const data = await response.json();

                if (data.success) {
                    const container = document.getElementById('lista-recordatorios-completa');
                    container.innerHTML = '';

                    if (data.recordatorios.length === 0) {
                        container.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">No hay recordatorios programados</p>';
                    } else {
                        // Agrupar por fecha
                        const porFecha = {};
                        data.recordatorios.forEach(r => {
                            if (!porFecha[r.fecha]) {
                                porFecha[r.fecha] = [];
                            }
                            porFecha[r.fecha].push(r);
                        });

                        // Renderizar por fecha
                        Object.keys(porFecha).sort().reverse().forEach(fecha => {
                            const fechaObj = new Date(fecha + 'T00:00:00');
                            const fechaFormateada = fechaObj.toLocaleDateString('es-ES', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                            let html = `<div style="margin-bottom: 1.5rem; border-bottom: 1px solid #e5e5e5; padding-bottom: 1rem;">
                                <h4 style="font-size: 0.875rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.75rem;">${fechaFormateada}</h4>`;

                            porFecha[fecha].forEach(rec => {
                                const completado = rec.completado == 1;
                                const estiloCompletado = completado ? 'text-decoration: line-through; color: #16a34a;' : '';
                                const checkmark = completado ? '✓ ' : '';

                                html += `<div style="padding: 0.75rem; border: 1px solid #e5e5e5; margin-bottom: 0.5rem; background: ${completado ? '#f0fdf4' : 'white'};">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; ${estiloCompletado}">${checkmark}${rec.titulo}</div>
                                            ${rec.descripcion ? `<div style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">${rec.descripcion}</div>` : ''}
                                        </div>
                                        <div style="display: flex; gap: 0.5rem;">
                                            ${!completado ? `<button class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;" onclick="completarRecordatorioDesdeModal(${rec.id})">Completar</button>` : ''}
                                            <button class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;" onclick="eliminarRecordatorioDesdeModal(${rec.id})">Eliminar</button>
                                        </div>
                                    </div>
                                </div>`;
                            });

                            html += '</div>';
                            container.innerHTML += html;
                        });
                    }

                    document.getElementById('modal-recordatorios').classList.add('active');
                }
            } catch (error) {
                console.error('Error al cargar recordatorios:', error);
            }
        }

        function cerrarModalRecordatorios() {
            document.getElementById('modal-recordatorios').classList.remove('active');
        }

        async function completarRecordatorioDesdeModal(id) {
            await completarRecordatorio(id);
            abrirModalRecordatorios();
        }

        async function eliminarRecordatorioDesdeModal(id) {
            await eliminarRecordatorio(id);
            abrirModalRecordatorios();
        }

        // Cargar al inicio
        cargarEventos();
        cargarRecordatorios();

        // Cerrar modal al hacer clic fuera
        document.getElementById('modal-evento').addEventListener('click', (e) => {
            if (e.target.id === 'modal-evento') {
                cerrarModal();
            }
        });

        document.getElementById('modal-recordatorios').addEventListener('click', (e) => {
            if (e.target.id === 'modal-recordatorios') {
                cerrarModalRecordatorios();
            }
        });
    </script>
</body>
</html>
