<?php
namespace App\Models;

use CodeIgniter\Model;

class HorarioModel extends Model
{
    protected $table      = 'horario';
    protected $primaryKey = 'id_horario';

    protected $allowedFields = [
        'horario'
    ];

    public function listarHorarios()
    {
        return $this->findAll();
    }
}
