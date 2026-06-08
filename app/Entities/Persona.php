<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Persona extends Entity
{
    protected $attributes = [
        'id_persona'       => null,
        'dni'              => null,
        'nombre'           => null,
        'apellido'         => null,
        'fecha_nacimiento' => null,
        'telefono'         => null,
        'calle'            => null,
        'altura'           => null,
    ];
}
