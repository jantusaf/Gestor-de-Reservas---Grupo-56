<div class="reserva-wrapper">
      <div class="reserva-card">
    <h2 class="reserva-title">Nueva Reserva</h2>
 <p class="reserva-subtitle">Completá los datos</p>
    <!-- Mensajes de error o éxito -->
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('reserva/guardar') ?>" method="post">
        <!-- Recinto -->
        <div class="form-group-modern">
            <select name="id_recinto" id="recinto" required>
                <option value="" disabled selected></option>
                <?php foreach($recintos as $r): ?>
                    <option value="<?= $r['id_recinto'] ?>">
                        <?= $r['nombre_tipo_recinto'].' - '.$r['descripcion'].' - Tarifa: $'.$r['tarifa'].'/h' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Recinto</label>
        </div>

        <!-- Cliente -->
        <div class="form-group-modern">
            <select name="id_cliente" required>
                <option value="" disabled selected></option>
                <?php foreach($clientes as $c): ?>
                    <option value="<?= $c['id_cliente'] ?>">
                        <?= $c['persona']['nombre'].' '.$c['persona']['apellido'].' ('.$c['email'].')' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Cliente</label>
        </div>

        <!-- Fecha -->
        <div class="form-group-modern">
            <input type="date" name="fecha_reserva" id="fecha" class="form-control" required>
        </div>

        <!-- Hora -->
        <div class="form-group-modern">
            <select name="id_horario" id="hora" class="form-select" required disabled>
                <option value="">Seleccione una hora</option>
            </select>
            
        </div>

        <button type="submit" class="btn-login">Reservar</button>
    </form>
    <div class="login-footer mt-3">
        <a href="<?= site_url('reserva/listar') ?>">Ver listado de reservas</a>
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
