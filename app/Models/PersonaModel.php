<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Persona;

class PersonaModel extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    protected $allowedFields = [
        'dni',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'telefono',
        'calle',
        'altura',
    ];

    public function altaPersona(Persona $persona): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'dni'=> ['label' => 'DNI','rules' => 'required|numeric|min_length[7]|max_length[20]'],
            'nombre'=> ['label' => 'Nombre','rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'apellido'=> ['label' => 'Apellido','rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'fecha_nacimiento' => ['label' => 'Fecha de nacimiento', 'rules' => 'required|valid_date[Y-m-d]|check_past_date'],
            'telefono'=> ['label' => 'Teléfono','rules' => 'permit_empty|numeric|min_length[6]|max_length[20]',
                                   'errors' => ['numeric' => 'El campo Teléfono debe contener solo números.', 'min_length' => 'El campo Teléfono debe tener al menos 6 caracteres.']],
            'calle'            => ['label' => 'Calle','rules' => 'required|min_length[3]|max_length[50]'],
            'altura'=> ['label' => 'Altura','rules' => 'required|max_length[10]'],
        ])->run([
            'dni'=> $persona->dni,
            'nombre'=> $persona->nombre,
            'apellido'=> $persona->apellido,
            'fecha_nacimiento' => $persona->fecha_nacimiento,
            'telefono'=> $persona->telefono,
            'calle'=> $persona->calle,
            'altura'=> $persona->altura,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        // unicidad del dni separada de is_unique para permitir pruebas unitarias puras
        if ($this->where('dni', $persona->dni)->first()) {
            return ['ok' => false, 'errores' => ['dni' => 'El DNI ya está registrado.']];
        }

        $this->insert([
            'dni'=> $persona->dni,
            'nombre'=> $persona->nombre,
            'apellido'=> $persona->apellido,
            'fecha_nacimiento' => $persona->fecha_nacimiento,
            'telefono'=> $persona->telefono,
            'calle'=> $persona->calle,
            'altura'=> $persona->altura,
        ]);

        return ['ok' => true, 'id' => $this->getInsertID()];
    }

    public function actualizarPersona(int $id, Persona $persona): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'dni'=> ['label' => 'DNI','rules' => 'required|numeric|min_length[7]|max_length[20]'],
            'nombre'=> ['label' => 'Nombre','rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'apellido'=> ['label' => 'Apellido','rules' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]'],
            'fecha_nacimiento' => ['label' => 'Fecha de nacimiento', 'rules' => 'required|valid_date[Y-m-d]|check_past_date'],
            'telefono'=> ['label' => 'Teléfono','rules' => 'permit_empty|numeric|min_length[6]|max_length[20]',
                                   'errors' => ['numeric' => 'El campo Teléfono debe contener solo números.', 'min_length' => 'El campo Teléfono debe tener al menos 6 caracteres.']],
            'calle'=> ['label' => 'Calle','rules' => 'required|min_length[3]|max_length[50]'],
            'altura'=> ['label' => 'Altura','rules' => 'required|max_length[10]'],
        ])->run([
            'dni'=> $persona->dni,
            'nombre'=> $persona->nombre,
            'apellido'=> $persona->apellido,
            'fecha_nacimiento' => $persona->fecha_nacimiento,
            'telefono'=> $persona->telefono,
            'calle'=> $persona->calle,
            'altura'=> $persona->altura,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

    
        if ($this->where('dni', $persona->dni)->where('id_persona !=', $id)->first()) {
            return ['ok' => false, 'errores' => ['dni' => 'El DNI ya está registrado.']];
        }

        $this->update($id, [
            'dni'=> $persona->dni,
            'nombre'=> $persona->nombre,
            'apellido'=> $persona->apellido,
            'fecha_nacimiento' => $persona->fecha_nacimiento,
            'telefono'=> $persona->telefono,
            'calle'=> $persona->calle,
            'altura'=> $persona->altura,
        ]);

        return ['ok' => true];
    }
}
