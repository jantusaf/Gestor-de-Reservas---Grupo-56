<?php

namespace App\States\Reserva;

/**
 * la fecha de la reserva es pasada  — el turno ya paso o no se presento
 * nnguna accion se puede ejecutar sobre sobre una reserva vencida.
 */
class EstadoVencida implements IEstadoReserva
{
    public function pagar(array $reserva, array $horario, array $params): array
    {
        return [
            'ok' => false,
            'mensajes' => ['id_medio_pago' => 'No se puede pagar una reserva cuya fecha ya pasó.'],
        ];
    }

    public function cancelar(array $reserva, array $horario, array $params): array
    {
        return [
            'ok'=> false,
            'mensaje' => 'No se puede cancelar una reserva cuya fecha ya pasó.',
        ];
    }

    public function obtenerNombreEstado(): string
    {
        return 'Vencida';
    }
}
