<div class="reserva-wrapper">
    <div class="reserva-card">
        <h2 class="reserva-title">Nueva Reserva</h2>
        <p class="reserva-subtitle">Completá los datos</p>

<?php if(session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul>
        <?php foreach(session()->getFlashdata('errors') as $error): ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


        <form action="<?= site_url('reserva/guardar') ?>" method="post">
            <div class="form-group-modern">
                <select name="id_recinto" id="recinto" >
                    <option value="" disabled selected></option>
                    <?php foreach($recintos as $r): ?>
                        <option value="<?= $r['id_recinto'] ?>">
                            <?= $r['nombre_tipo_recinto'].' - '.$r['descripcion'].' - Tarifa: $'.$r['tarifa'].'/h' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Recinto</label>
            </div>

            <div class="form-group-modern">
                <select name="id_cliente" >
                    <option value="" disabled selected></option>
                    <?php foreach($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>">
                            <?= $c['nombre'].' '.$c['apellido'].' ('.$c['email'].')' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Cliente</label>
            </div>

            <div class="form-group-modern">
                <input type="date" name="fecha_reserva" id="fecha" >
                <label for="fecha" class="label-active">Fecha</label>
            </div>

            <div class="form-group-modern">
                <select name="id_horario" id="hora"  disabled>
                    <option value="">Seleccione una hora</option>
                </select>
                <label>Horario</label>
            </div>

            <button type="submit" class="btn-login">Reservar</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= site_url('reserva/listar') ?>">Ver listado de reservas</a>
        </div>
    </div>
</div>

<?php $nuevaReservaId = session()->getFlashdata('nueva_reserva_id'); ?>
<?php if($nuevaReservaId): ?>
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-reserva">
        <div class="modal-reserva-icon">✓</div>
        <h3 class="modal-reserva-title">¡Reserva creada!</h3>
        <p class="modal-reserva-msg">¿Qué desea hacer ahora?</p>
        <div class="modal-reserva-btns">
            <button class="btn-login" onclick="cerrarModal()">Seguir reservando</button>
            <a href="<?= site_url('pago/alta/' . $nuevaReservaId) ?>" class="btn-pagar">Ir a pagar</a>
        </div>
    </div>
</div>


<script>
function cerrarModal() {
    document.getElementById('modalOverlay').style.display = 'none';
}
document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>
<?php endif; ?>

<script>
    document.getElementById('fecha').addEventListener('change', cargarHoras);
    document.getElementById('recinto').addEventListener('change', cargarHoras);

    function getCookie(name) {
        let parts = ('; ' + document.cookie).split('; ' + name + '=');
        return parts.length === 2 ? parts.pop().split(';').shift() : '';
    }

    function cargarHoras() {
        let fecha   = document.getElementById('fecha').value;
        let recinto = document.getElementById('recinto').value;
        let select  = document.getElementById('hora');

        if (!fecha || !recinto) return;

        select.disabled = true;
        select.innerHTML = "<option value=''>Cargando...</option>";

        fetch("<?= site_url('reserva/horas') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-TOKEN": getCookie('csrf_cookie_name')
            },
            body: "fecha_reserva=" + fecha + "&id_recinto=" + recinto
        })
        .then(res => {
            if (!res.ok) throw new Error('Error ' + res.status);
            return res.json();
        })
        .then(data => {
            select.innerHTML = "<option value=''>Seleccione una hora</option>";
            if (data.length === 0) {
                select.innerHTML = "<option value=''>No hay horarios disponibles</option>";
            } else {
                data.forEach(h => {
                    let opt = document.createElement("option");
                    opt.value       = h['id_horario'];
                    opt.textContent = h['horario'];
                    select.appendChild(opt);
                });
            }
            select.disabled = false;
        })
        .catch(() => {
            select.innerHTML = "<option value=''>Error al cargar horarios</option>";
            select.disabled = false;
        });
    }
</script>
