const modal = new bootstrap.Modal(document.getElementById('modalEjercicio'));

// Mostrar modal para nuevo ejercicio
function mostrarModalNuevo(diaId, diaNombre) {
    document.getElementById('modalTitulo').textContent = `Añadir Ejercicio - ${diaNombre}`;
    document.getElementById('formEjercicio').reset();
    document.getElementById('ejercicio_id').value = '';
    document.getElementById('dia_id').value = diaId;
    document.getElementById('accion').value = 'crear';

    // Calcular próximo orden
    const tablas = document.querySelectorAll('table tbody');
    let maxOrden = 0;
    tablas.forEach(tabla => {
        const filas = tabla.querySelectorAll('tr');
        filas.forEach(fila => {
            const orden = parseInt(fila.querySelector('td:first-child strong')?.textContent || 0);
            if (orden > maxOrden) maxOrden = orden;
        });
    });
    document.getElementById('orden').value = maxOrden + 1;

    modal.show();
}

// Editar ejercicio existente
function editarEjercicio(ejercicio) {
    document.getElementById('modalTitulo').textContent = 'Editar Ejercicio';
    document.getElementById('ejercicio_id').value = ejercicio.id;
    document.getElementById('dia_id').value = ejercicio.dia_id;
    document.getElementById('accion').value = 'editar';
    document.getElementById('nombre').value = ejercicio.nombre;
    document.getElementById('orden').value = ejercicio.orden;
    document.getElementById('sets_recomendados').value = ejercicio.sets_recomendados;
    document.getElementById('reps_recomendadas').value = ejercicio.reps_recomendadas;
    document.getElementById('tipo_equipo').value = ejercicio.tipo_equipo;
    document.getElementById('grupo_muscular').value = ejercicio.grupo_muscular;
    document.getElementById('notas').value = ejercicio.notas || '';

    modal.show();
}

// Eliminar ejercicio
function eliminarEjercicio(ejercicioId, nombre) {
    if (!confirm(`¿Estás seguro de eliminar "${nombre}"?\n\n⚠️ Se perderán todos los registros históricos de este ejercicio.`)) {
        return;
    }

    fetch('api_ejercicios.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            accion: 'eliminar',
            ejercicio_id: ejercicioId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Ejercicio eliminado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error al eliminar: ' + error.message);
    });
}

// Guardar ejercicio (crear o editar)
document.getElementById('formEjercicio').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('api_ejercicios.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.accion === 'crear' ? '✅ Ejercicio añadido correctamente' : '✅ Ejercicio actualizado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error al guardar: ' + error.message);
    });
});
