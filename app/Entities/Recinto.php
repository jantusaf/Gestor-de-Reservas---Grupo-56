<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Recinto extends Entity
{
    protected $attributes = [
        'id_recinto'      => null,
        'tarifa'          => null,
        'estado_recinto'  => null,
        'descripcion'     => null,
        'id_tipo_recinto' => null,
    ];
}
