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

    <form action="<?= site_url('reserva/save') ?>" method="post">
        <!-- Recinto -->
        <div class="form-group-modern">
            
            <select name="nro_recinto" required>
                <option value="" disabled selected></option>
                <?php foreach($recintos as $r): ?>
                    <option value="<?= $r['nro_recinto'] ?>">
                        <?= 'Recinto '.$r['nro_recinto'].' - Tarifa: $'.$r['tarifa_hora'].'/h' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Recinto</label>
        </div>
        <div class="form-group-modern">
         
            <select name="id_cliente"  required>
                <option value=""disabled selected></option>
                <?php foreach($clientes as $c): ?>
                    <option value="<?= $c['id_cliente'] ?>">
                        <?= $c['nombre_cliente'].' '.$c['apellido_cliente'].' ('.$c['email_cliente'].')' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Cliente</label>
        </div>

<div class="form-group-modern">
    
    <input type="date" name="fecha_reserva" id="fecha" class="form-control" required>
</div>

        <!-- Hora -->
        <div class="form-group-modern">
            
            <input name="hora_reserva" id="hora" class="form-select" required disabled>
             <label>Hora</label>   <option value="">Seleccione una hora</option>
            </select>
        </div>


        <button type="submit" class=" btn-login"> Reservar</button>
    </form>
</div>
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

                select.disabled = false;
            });
        }
    }
</script>
