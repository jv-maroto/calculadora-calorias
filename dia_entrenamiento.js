// Agregar nueva serie
function agregarSet(ejercicioId) {
    const container = document.getElementById(`sets-container-${ejercicioId}`);
    const currentRows = container.querySelectorAll('tr');
    const nextSetNumber = currentRows.length + 1;

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td><strong>${nextSetNumber}</strong></td>
        <td>
            <input type="number"
                   class="form-control form-control-sm set-input"
                   name="peso[]"
                   step="0.5"
                   min="0"
                   placeholder="kg">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm set-input"
                   name="reps[]"
                   min="0"
                   placeholder="reps">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm set-input"
                   name="rpe[]"
                   step="0.5"
                   min="1"
                   max="10"
                   placeholder="1-10">
        </td>
        <td>
            <button type="button"
                    class="btn btn-sm btn-danger"
                    onclick="eliminarSet(this)">
                ×
            </button>
        </td>
    `;
    container.appendChild(newRow);
}

// Eliminar serie
function eliminarSet(button) {
    const row = button.closest('tr');
    const container = row.closest('tbody');

    // No permitir eliminar si solo hay una fila
    if (container.querySelectorAll('tr').length <= 1) {
        alert('Debe haber al menos una serie');
        return;
    }

    row.remove();

    // Renumerar las series
    const rows = container.querySelectorAll('tr');
    rows.forEach((row, index) => {
        const setNumber = row.querySelector('td:first-child strong');
        if (setNumber) {
            setNumber.textContent = index + 1;
        }
    });
}

// Guardar sets de un ejercicio
function guardarSets(event, ejercicioId) {
    event.preventDefault();

    const form = event.target;
    const fecha = new URLSearchParams(window.location.search).get('fecha') || new Date().toISOString().split('T')[0];

    // Recoger datos del formulario
    const pesos = Array.from(form.querySelectorAll('input[name="peso[]"]')).map(input => input.value);
    const reps = Array.from(form.querySelectorAll('input[name="reps[]"]')).map(input => input.value);
    const rpes = Array.from(form.querySelectorAll('input[name="rpe[]"]')).map(input => input.value);

    // Filtrar sets vacíos
    const sets = [];
    for (let i = 0; i < pesos.length; i++) {
        if (pesos[i] && reps[i]) {
            sets.push({
                peso: parseFloat(pesos[i]),
                reps: parseInt(reps[i]),
                rpe: rpes[i] ? parseFloat(rpes[i]) : null,
                set_numero: i + 1
            });
        }
    }

    if (sets.length === 0) {
        alert('Debes completar al menos un set con peso y repeticiones');
        return false;
    }

    // Enviar datos al servidor
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Guardando...';

    fetch('guardar_entrenamiento.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ejercicio_id: ejercicioId,
            fecha: fecha,
            sets: sets
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            submitBtn.innerHTML = '✅ Guardado';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');

            setTimeout(() => {
                submitBtn.innerHTML = '💾 Guardar';
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
                submitBtn.disabled = false;

                // Recargar página para actualizar histórico
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar: ' + error.message);
        submitBtn.innerHTML = '💾 Guardar';
        submitBtn.disabled = false;
    });

    return false;
}
