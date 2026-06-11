<?php

namespace App\States\Reserva;

interface IEstadoReserva
{
    /**
     * Cada estado decide si la acción es válida y qué hace con ella.
     */
    public function pagar(array $reserva, array $horario, array $params): array;

    /**
     * Cancela la reserva.
     */
    public function cancelar(array $reserva, array $horario, array $params): array;

    /**
     * Devuelve el nombre del estado actual.
     */
    public function obtenerNombreEstado(): string;
}
