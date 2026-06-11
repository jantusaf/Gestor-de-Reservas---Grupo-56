<?php

namespace App\States\Reserva;

/**
 * la reserva fue cancelada no se permite ninguna accion
 *
 * pagar() rechaza, debe realizar una nueva reserva
 * cancelar() → rechaza, ya fue cancelada previamente
 */
class EstadoCancelada implements IEstadoReserva
{
    public function pagar(array $reserva, array $horario, array $params): array
    {
        return [
            'ok'      => false,
            'mensajes' => ['id_medio_pago' => 'Esta reserva fue cancelada. Para abonar, realizá una nueva reserva.'],
        ];
    }

    public function cancelar(array $reserva, array $horario, array $params): array
    {
        return [
            'ok'      => false,
            'mensaje' => 'Esta reserva ya fue cancelada previamente.',
        ];
    }

    public function obtenerNombreEstado(): string
    {
        return 'Cancelada';
    }
}
