<div class="container mt-4">
    <h2>Agregar Reserva</h2>

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

    <form action="<?= site_url('reserva/save') ?>" method="post">
        <!-- Cliente -->
        <div class="mb-3">
            <label for="cliente" class="form-label">Cliente</label>
            <select name="id_cliente" id="cliente" class="form-select" required>
                <option value="">Seleccione un cliente</option>
                <?php foreach($clientes as $c): ?>
                    <option value="<?= $c['id_cliente'] ?>">
                        <?= $c['nombre_cliente'].' '.$c['apellido_cliente'].' ('.$c['email_cliente'].')' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <!-- Recinto -->
        <div class="mb-3">
            <label for="recinto" class="form-label">Recinto</label>
            <select name="nro_recinto" id="recinto" class="form-select" required>
                <option value="">Seleccione un recinto</option>
                <?php foreach($recintos as $r): ?>
                    <option value="<?= $r['nro_recinto'] ?>">
                        <?= 'Recinto '.$r['nro_recinto'].' - Tarifa: $'.$r['tarifa_hora'].'/h' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Fecha -->
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" name="fecha_reserva" id="fecha" class="form-control" required>
        </div>

        <!-- Hora -->
        <div class="mb-3">
            <label for="hora" class="form-label">Hora</label>
            <select name="hora_reserva" id="hora" class="form-select" required>
                <option value="">Seleccione una hora</option>
            </select>
        </div>

        <!-- Usuario (según login) -->
        <input type="hidden" name="id_usuario" value="<?= session()->get('id_usuario') ?>">

        <button type="submit" class="btn btn-primary">Agregar Reserva</button>
    </form>
</div>

<script>
    // Cargar horas disponibles dinámicamente
    document.getElementById('fecha').addEventListener('change', cargarHoras);
    document.getElementById('recinto').addEventListener('change', cargarHoras);

    function cargarHoras() {
        let fecha = document.getElementById('fecha').value;
        let recinto = document.getElementById('recinto').value;

        if(fecha && recinto) {
            fetch("<?= site_url('reserva/horasDisponibles') ?>", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "fecha_reserva=" + fecha + "&nro_recinto=" + recinto
            })
            .then(res => res.json())
            .then(data => {
                let select = document.getElementById('hora');
                select.innerHTML = "<option value=''>Seleccione una hora</option>";
                data.forEach(h => {
                    let opt = document.createElement("option");
                    opt.value = h;
                    opt.textContent = h;
                    select.appendChild(opt);
                });
            });
        }
    }
</script>
