<div class="reserva-wrapper">
    <div class="reserva-card">

        <h2 class="reserva-title">Editar Reserva</h2>
        <p class="reserva-subtitle">Modificá los datos de la reserva</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= site_url('reserva/actualizar/' . $reserva['id_reserva']) ?>" method="post">

            <!-- Recinto -->
            <div class="form-group-modern">
                <select name="id_recinto" id="recinto" required>
                    <option value="" disabled></option>
                    <?php foreach($recintos as $r): ?>
                        <option value="<?= $r['id_recinto'] ?>"
                            <?= $r['id_recinto'] == $reserva['id_recinto'] ? 'selected' : '' ?>>
                            <?= esc($r['nombre_tipo_recinto']).' - '.esc($r['descripcion']).' - $'.number_format($r['tarifa'], 2).'/h' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Recinto</label>
            </div>

            <!-- Cliente -->
            <div class="form-group-modern">
                <select name="id_cliente" required>
                    <option value="" disabled></option>
                    <?php foreach($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"
                            <?= $c['id_cliente'] == $reserva['id_cliente'] ? 'selected' : '' ?>>
                            <?= esc($c['persona']['nombre']).' '.esc($c['persona']['apellido']).' ('.$c['email'].')' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Cliente</label>
            </div>

            <!-- Fecha -->
            <div class="form-group-modern">
                <input type="date" name="fecha_reserva" id="fecha"
                       value="<?= esc($reserva['fecha_reserva']) ?>" required>
                <label>Fecha</label>
            </div>

            <!-- Horario -->
            <div class="form-group-modern">
                <select name="id_horario" id="hora" required>
                    <option value="">Seleccione una hora</option>
                    <?php foreach($horarios as $h): ?>
                        <option value="<?= $h['id_horario'] ?>"
                            <?= $h['id_horario'] == $reserva['id_horario'] ? 'selected' : '' ?>>
                            <?= esc($h['horario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Hora</label>
            </div>

            <!-- Estado Reserva -->
            <div class="form-group-modern">
                <select name="estado_reserva" required>
                    <option value="pendiente"  <?= $reserva['estado_reserva'] === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                    <option value="confirmada" <?= $reserva['estado_reserva'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="cancelada"  <?= $reserva['estado_reserva'] === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                </select>
                <label>Estado de la reserva</label>
            </div>

            <!-- Estado Pago -->
            <div class="form-group-modern">
                <select name="estado_pago" required>
                    <option value="pendiente"  <?= $reserva['estado_pago'] === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                    <option value="pagado"     <?= $reserva['estado_pago'] === 'pagado'     ? 'selected' : '' ?>>Pagado</option>
                    <option value="reembolso"  <?= $reserva['estado_pago'] === 'reembolso'  ? 'selected' : '' ?>>Reembolso</option>
                </select>
                <label>Estado del pago</label>
            </div>

            <button type="submit" class="btn-login">Guardar cambios</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= site_url('reserva/listar') ?>">Volver al listado de reservas</a>
        </div>

    </div>
</div>

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
            body: "fecha_reserva=" + fecha + "&id_recinto=" + recinto + "&excluir_reserva=<?= $reserva['id_reserva'] ?>"
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
