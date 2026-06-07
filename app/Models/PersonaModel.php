<?php
namespace App\Models;

use CodeIgniter\Model;

class PersonaModel extends Model
{
    protected $table      = 'persona';
    protected $primaryKey = 'id_persona';

    protected $allowedFields = [
        'dni',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'telefono',
        'calle',
        'altura'
    ];

    public function altaPersona(string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura): int
    {
        $this->insert([
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ]);

        return $this->getInsertID();
    }
}
