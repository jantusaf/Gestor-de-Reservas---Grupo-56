<?php

namespace App\States\Reserva;

use App\Models\PagoModel;
use App\Models\ReservaModel;

/**
 * la reserva tiene un pago registrado
 *
 * pagar() rechaza porq ya está pagada
 * cancelar()  cancela si faltan más de 30 minutos para el turno se marca el pago como reembolsado, y si no sin reembolso
 */
class EstadoConfirmada implements IEstadoReserva
{
    private const MINUTOS_LIMITE_REEMBOLSO = 30;

    public function pagar(array $reserva, array $horario, array $params): array
    {
        return [
            'ok'      => false,
            'mensajes' => ['id_medio_pago' => 'Esta reserva ya fue pagada.'],
        ];
    }

    public function cancelar(array $reserva, array $horario, array $params): array
    {
        $fechaHoraInicio  = $reserva['fecha_reserva'] . ' ' . ($horario['horario'] ?? '23:59:59');
        $minutosRestantes = (strtotime($fechaHoraInicio) - time()) / 60;
        $aplicaReembolso  = $minutosRestantes > self::MINUTOS_LIMITE_REEMBOLSO;

        $db = \Config\Database::connect();
        $db->transBegin();

        (new ReservaModel())->update($reserva['id_reserva'], [
            'estado_reserva' => 'cancelada',
        ]);

        if ($aplicaReembolso) {
            $pagoModel = new PagoModel();
            $pago = $pagoModel
                ->where('id_reserva', $reserva['id_reserva'])
                ->where('estado', 'pagada')
                ->first();

            if ($pago) {
                $pagoModel->update($pago['id_pago'], ['estado' => 'reembolsado']);
            }
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'mensaje' => 'No se pudo cancelar la reserva.'];
        }

        $db->transCommit();
        return ['ok' => true, 'reembolso' => $aplicaReembolso];
    }

    public function obtenerNombreEstado(): string
    {
        return 'Confirmada';
    }
}
