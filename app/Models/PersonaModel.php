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

    public function altaPersona(string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'dni'              => ['label' => 'DNI',                'rules' => 'required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni]'],
            'nombre'           => ['label' => 'Nombre',             'rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'apellido'         => ['label' => 'Apellido',           'rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'fecha_nacimiento' => ['label' => 'Fecha de nacimiento','rules' => 'required|valid_date[Y-m-d]|check_past_date'],
            'telefono'         => ['label' => 'Teléfono',           'rules' => 'permit_empty|max_length[20]'],
            'calle'            => ['label' => 'Calle',              'rules' => 'required|min_length[3]|max_length[50]'],
            'altura'           => ['label' => 'Altura',             'rules' => 'required|max_length[10]'],
        ])->run([
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->insert([
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ]);

        return ['ok' => true, 'id' => $this->getInsertID()];
    }

    public function actualizarPersona(int $id, string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'dni'              => ['label' => 'DNI',                'rules' => "required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni,id_persona,{$id}]"],
            'nombre'           => ['label' => 'Nombre',             'rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'apellido'         => ['label' => 'Apellido',           'rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'fecha_nacimiento' => ['label' => 'Fecha de nacimiento','rules' => 'required|valid_date[Y-m-d]|check_past_date'],
            'telefono'         => ['label' => 'Teléfono',           'rules' => 'permit_empty|max_length[20]'],
            'calle'            => ['label' => 'Calle',              'rules' => 'required|min_length[3]|max_length[50]'],
            'altura'           => ['label' => 'Altura',             'rules' => 'required|max_length[10]'],
        ])->run([
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->update($id, [
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ]);

        return ['ok' => true];
    }
}
