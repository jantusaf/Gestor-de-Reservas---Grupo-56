<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?= esc($factura['id_pago']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
            padding: 40px 20px;
        }

        .factura-container {
            max-width: 680px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* ENCABEZADO */
        .factura-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 32px 40px 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .factura-header h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .factura-header .empresa-sub {
            font-size: 13px;
            color: #a0aec0;
            margin-top: 4px;
        }
        .factura-numero {
            text-align: right;
        }
        .factura-numero .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #a0aec0;
        }
        .factura-numero .valor {
            font-size: 22px;
            font-weight: 700;
            color: #68d391;
        }
        .factura-numero .fecha {
            font-size: 13px;
            color: #a0aec0;
            margin-top: 4px;
        }

        /* CUERPO */
        .factura-body {
            padding: 32px 40px;
        }

        .seccion {
            margin-bottom: 28px;
        }
        .seccion-titulo {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #718096;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 14px;
        }
        .fila {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
            border-bottom: 1px solid #f7fafc;
        }
        .fila .campo { color: #718096; }
        .fila .dato  { font-weight: 600; color: #2d3748; }

        /* TOTAL */
        .factura-total {
            background: #f7fafc;
            border-radius: 10px;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }
        .factura-total .label-total {
            font-size: 16px;
            font-weight: 700;
            color: #2d3748;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .factura-total .monto-total {
            font-size: 32px;
            font-weight: 800;
            color: #276749;
        }

        /* FOOTER */
        .factura-footer {
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 40px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
        }

        /* BOTONES (solo pantalla) */
        .acciones-imprimir {
            max-width: 680px;
            margin: 24px auto 0;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn-imprimir {
            background: #276749;
            color: #fff;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-imprimir:hover { background: #1a4d34; }
        .btn-cerrar {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-cerrar:hover { background: #cbd5e0; }

        /* IMPRESIÓN */
        @media print {
            body { background: #fff; padding: 0; }
            .acciones-imprimir { display: none; }
            .factura-container {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="factura-container">

    <div class="factura-header">
        <div>
            <h1>Sistema de Reservas</h1>
            <div class="empresa-sub">Comprobante de pago</div>
        </div>
        <div class="factura-numero">
            <div class="label">Comprobante N°</div>
            <div class="valor"><?= str_pad($factura['id_pago'], 6, '0', STR_PAD_LEFT) ?></div>
            <div class="fecha"><?= date('d/m/Y', strtotime($factura['fecha_pago'])) ?></div>
        </div>
    </div>

    <div class="factura-body">

        <!-- CLIENTE -->
        <div class="seccion">
            <div class="seccion-titulo">Datos del cliente</div>
            <div class="fila">
                <span class="campo">Nombre completo</span>
                <span class="dato"><?= esc($factura['nombre']).' '.esc($factura['apellido']) ?></span>
            </div>
            <div class="fila">
                <span class="campo">DNI</span>
                <span class="dato"><?= esc($factura['dni']) ?></span>
            </div>
        </div>

        <!-- RESERVA -->
        <div class="seccion">
            <div class="seccion-titulo">Datos de la reserva</div>
            <div class="fila">
                <span class="campo">Recinto</span>
                <span class="dato"><?= esc($factura['nombre_tipo_recinto']).' · '.esc($factura['recinto_desc']) ?></span>
            </div>
            <div class="fila">
                <span class="campo">Fecha</span>
                <span class="dato"><?= date('d/m/Y', strtotime($factura['fecha_reserva'])) ?></span>
            </div>
            <div class="fila">
                <span class="campo">Horario</span>
                <span class="dato"><?= esc($factura['hora']) ?></span>
            </div>
        </div>

        <!-- PAGO -->
        <div class="seccion">
            <div class="seccion-titulo">Datos del pago</div>
            <div class="fila">
                <span class="campo">Medio de pago</span>
                <span class="dato"><?= esc($factura['nombre_medio_pago']) ?></span>
            </div>
            <div class="fila">
                <span class="campo">Fecha de pago</span>
                <span class="dato"><?= date('d/m/Y', strtotime($factura['fecha_pago'])) ?></span>
            </div>
            <div class="fila">
                <span class="campo">Registrado por</span>
                <span class="dato"><?= esc($factura['nombre_usuario']) ?></span>
            </div>
        </div>

        <!-- TOTAL -->
        <div class="factura-total">
            <span class="label-total">Total abonado</span>
            <span class="monto-total">$<?= number_format($factura['monto_total'], 2, ',', '.') ?></span>
        </div>

    </div>

    <div class="factura-footer">
        Este comprobante es válido como constancia de pago. — Sistema de Reservas
    </div>

</div>

<div class="acciones-imprimir">
    <button class="btn-imprimir" onclick="window.print()">
        🖨 Imprimir
    </button>
    <button class="btn-cerrar" onclick="window.close()">
        Cerrar
    </button>
</div>

</body>
</html>
