<?php

namespace App\States\Reserva;

use App\Models\PagoModel;
use App\Models\ReservaModel;

/**
 * la reserva fue creada pero aún no tiene pago registrado
 *
 * pagar() registra el pago y pasa a confirmar
 * cancelar() cancela sin reembolso (nunca hubo pago)
 */
class EstadoPendiente implements IEstadoReserva
{
    public function pagar(array $reserva, array $horario, array $params): array
    {
        $idMedioPago = (int) ($params['id_medio_pago'] ?? 0);
        $idUsuario   = (int) ($params['id_usuario']   ?? 0);

        if ($idMedioPago <= 0) {
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'El medio de pago es obligatorio.']];
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        (new PagoModel())->insert([
            'id_reserva'    => $reserva['id_reserva'],
            'monto_total'   => $reserva['monto'],
            'estado'        => 'pagada',
            'id_medio_pago' => $idMedioPago,
            'fecha_pago'    => date('Y-m-d'),
            'id_usuario'    => $idUsuario,
        ]);

        (new ReservaModel())->update($reserva['id_reserva'], [
            'estado_reserva' => 'confirmada',
        ]);

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'No se pudo registrar el pago.']];
        }

        $db->transCommit();
        return ['ok' => true];
    }

    public function cancelar(array $reserva, array $horario, array $params): array
    {
        (new ReservaModel())->update($reserva['id_reserva'], [
            'estado_reserva' => 'cancelada',
        ]);

        return ['ok' => true, 'reembolso' => false];
    }

    public function obtenerNombreEstado(): string
    {
        return 'Pendiente';
    }
}
