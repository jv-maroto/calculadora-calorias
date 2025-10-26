<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Peso - Calculadora de Calorías</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="index_v0_design.php" class="navbar-brand-modern">💪 Calculadora de Calorías</a>
        <div class="navbar-links">
            <a href="index_v0_design.php" title="Calculadora">🧮</a>
            <a href="reverse_diet_v0.php" title="Reverse Diet">🔄</a>
            <a href="rutinas_v0.php" title="Rutinas">🏋️</a>
            <a href="introducir_peso_v0.php" title="Registrar Peso" style="color: #6366f1;">⚖️</a>
            <a href="seguimiento_v0.php" title="Seguimiento">📊</a>
            <a href="logout.php" title="Cerrar Sesión">🚪</a>
        </div>
    </div>

    <!-- Contenido -->
    <div style="max-width: 800px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Mensaje de resultado -->
        <div id="mensaje-resultado" class="v0-alert" style="display: none;"></div>

        <!-- Card de registro de peso -->
        <div class="v0-card">
            <div class="v0-card-header">
                <i data-lucide="scale" style="color: #6366f1; width: 24px; height: 24px;"></i>
                <div>
                    <h3>Registrar Peso Diario</h3>
                    <p>Lleva un seguimiento de tu progreso día a día</p>
                </div>
            </div>
            <div class="v0-card-body">
                <form id="formPeso">
                    <div class="grid-2 mb-3">
                        <div>
                            <label class="v0-label">Fecha</label>
                            <input type="date" class="v0-input" id="fecha" name="fecha" required>
                            <small class="v0-helper">Selecciona el día del registro</small>
                        </div>

                        <div>
                            <label class="v0-label">Peso (kg)</label>
                            <input type="number" class="v0-input" id="peso" name="peso" step="0.1" min="30" max="300" required placeholder="75.5">
                            <small class="v0-helper">Tu peso en kilogramos</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="v0-label">Notas (opcional)</label>
                        <textarea class="v0-textarea" id="notas" name="notas" rows="3" placeholder="Ej: Me sentí bien hoy, día de trampa, buena hidratación..."></textarea>
                        <small class="v0-helper">Añade cualquier información relevante sobre el día</small>
                    </div>

                    <div class="grid-2" style="gap: 1rem;">
                        <button type="submit" class="v0-btn v0-btn-primary">
                            <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                            Guardar Peso
                        </button>
                        <a href="grafica_v0.php" class="v0-btn v0-btn-secondary">
                            <i data-lucide="trending-up" style="width: 18px; height: 18px;"></i>
                            Ver Progreso
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card de historial -->
        <div class="v0-card mt-3">
            <div class="v0-card-header">
                <i data-lucide="calendar-days" style="color: #6366f1; width: 24px; height: 24px;"></i>
                <div>
                    <h3>Registros Recientes</h3>
                    <p>Últimos 7 días</p>
                </div>
            </div>
            <div class="v0-card-body">
                <div id="historial-pesos">
                    <div class="text-center" style="padding: 2rem;">
                        <i data-lucide="loader-2" style="width: 32px; height: 32px; color: #6366f1; animation: spin 1s linear infinite;"></i>
                        <p style="color: #64748b; margin-top: 1rem;">Cargando registros...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .registro-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
        }

        .registro-item:last-child {
            border-bottom: none;
        }

        .registro-item:hover {
            background: #f8fafc;
        }

        .peso-grande {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6366f1;
        }

        .diferencia-peso {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }

        .diferencia-positiva {
            background: #fef2f2;
            color: #dc2626;
        }

        .diferencia-negativa {
            background: #f0fdf4;
            color: #16a34a;
        }
    </style>

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        // Establecer fecha de hoy por defecto
        document.getElementById('fecha').valueAsDate = new Date();

        // Cargar historial de pesos al cargar la página
        cargarHistorial();

        // Manejar el envío del formulario
        document.getElementById('formPeso').addEventListener('submit', async (e) => {
            e.preventDefault();

            const fecha = document.getElementById('fecha').value;
            const peso = parseFloat(document.getElementById('peso').value);
            const notas = document.getElementById('notas').value;

            try {
                const response = await fetch('api_peso.php?action=guardar_peso', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        fecha: fecha,
                        peso: peso,
                        notas: notas
                    })
                });

                const resultado = await response.json();

                const mensaje = document.getElementById('mensaje-resultado');

                if (resultado.success) {
                    mensaje.className = 'v0-alert v0-alert-success fade-in';
                    mensaje.innerHTML = '<strong>✅ Peso guardado correctamente</strong><br>Tu progreso ha sido registrado.';
                    mensaje.style.display = 'block';

                    // Limpiar form pero mantener la fecha
                    document.getElementById('notas').value = '';
                    document.getElementById('peso').value = '';

                    // Recargar historial
                    cargarHistorial();

                    // Ocultar mensaje después de 4 segundos
                    setTimeout(() => {
                        mensaje.style.display = 'none';
                    }, 4000);
                } else {
                    mensaje.className = 'v0-alert v0-alert-danger fade-in';
                    mensaje.innerHTML = '<strong>❌ Error al guardar</strong><br>' + resultado.error;
                    mensaje.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                const mensaje = document.getElementById('mensaje-resultado');
                mensaje.className = 'v0-alert v0-alert-danger fade-in';
                mensaje.innerHTML = '<strong>❌ Error de conexión</strong><br>No se pudo guardar el peso. Verifica tu conexión.';
                mensaje.style.display = 'block';
            }
        });

        // Función para cargar historial
        async function cargarHistorial() {
            try {
                const response = await fetch('api_peso.php?action=obtener_pesos&dias=7');
                const resultado = await response.json();

                const contenedor = document.getElementById('historial-pesos');

                if (resultado.success && resultado.data.length > 0) {
                    let html = '';

                    // Ordenar por fecha descendente y calcular diferencias
                    const registros = resultado.data.reverse();

                    registros.forEach((registro, index) => {
                        const fechaObj = new Date(registro.fecha + 'T00:00:00');
                        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', {
                            weekday: 'short',
                            day: 'numeric',
                            month: 'short'
                        });

                        // Calcular diferencia con el día anterior
                        let diferenciaPeso = '';
                        if (index < registros.length - 1) {
                            const pesoAnterior = registros[index + 1].peso;
                            const diff = (registro.peso - pesoAnterior).toFixed(1);
                            const isPositive = diff > 0;

                            if (diff != 0) {
                                const icono = isPositive ? '↑' : '↓';
                                const clase = isPositive ? 'diferencia-positiva' : 'diferencia-negativa';
                                diferenciaPeso = `<span class="diferencia-peso ${clase}">${icono} ${Math.abs(diff)} kg</span>`;
                            }
                        }

                        html += `
                            <div class="registro-item">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                        <i data-lucide="calendar" style="width: 16px; height: 16px; color: #64748b;"></i>
                                        <span style="font-weight: 600; color: #334155; text-transform: capitalize;">${fechaFormateada}</span>
                                        ${diferenciaPeso}
                                    </div>
                                    <div class="peso-grande">${registro.peso} kg</div>
                                    ${registro.notas ? `<div style="margin-top: 0.5rem; color: #64748b; font-size: 0.875rem;">
                                        <i data-lucide="sticky-note" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
                                        ${registro.notas}
                                    </div>` : ''}
                                </div>
                                <button class="v0-btn v0-btn-sm"
                                        style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;"
                                        onclick="eliminarPeso(${registro.id})">
                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                        `;
                    });

                    contenedor.innerHTML = html;
                    lucide.createIcons(); // Re-inicializar iconos
                } else {
                    contenedor.innerHTML = `
                        <div style="text-align: center; padding: 3rem 1rem;">
                            <i data-lucide="inbox" style="width: 64px; height: 64px; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748b; font-size: 1.125rem; margin-bottom: 0.5rem;">No hay registros todavía</p>
                            <p style="color: #94a3b8; font-size: 0.875rem;">¡Añade tu primer peso para empezar a hacer seguimiento!</p>
                        </div>
                    `;
                    lucide.createIcons();
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('historial-pesos').innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <i data-lucide="alert-circle" style="width: 48px; height: 48px; color: #ef4444;"></i>
                        <p style="color: #ef4444; margin-top: 1rem;">Error al cargar el historial</p>
                    </div>
                `;
                lucide.createIcons();
            }
        }

        // Función para eliminar un peso
        async function eliminarPeso(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                return;
            }

            try {
                const response = await fetch(`api_peso.php?action=eliminar_peso&id=${id}`, {
                    method: 'DELETE'
                });

                const resultado = await response.json();

                if (resultado.success) {
                    // Mostrar mensaje de éxito
                    const mensaje = document.getElementById('mensaje-resultado');
                    mensaje.className = 'v0-alert v0-alert-success fade-in';
                    mensaje.innerHTML = '<strong>✅ Registro eliminado</strong><br>El peso ha sido eliminado correctamente.';
                    mensaje.style.display = 'block';

                    // Recargar historial
                    cargarHistorial();

                    // Ocultar mensaje después de 3 segundos
                    setTimeout(() => {
                        mensaje.style.display = 'none';
                    }, 3000);
                } else {
                    alert('Error al eliminar: ' + resultado.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el registro');
            }
        }
    </script>
</body>
</html>
