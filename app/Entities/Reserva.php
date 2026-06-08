<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Reserva extends Entity
{
    protected $attributes = [
        'id_reserva'     => null,
        'fecha_reserva'  => null,
        'monto'          => null,
        'estado_reserva' => null,
        'estado_pago'    => null,
        'id_horario'     => null,
        'id_cliente'     => null,
        'id_recinto'     => null,
        'id_usuario'     => null,
    ];
}
