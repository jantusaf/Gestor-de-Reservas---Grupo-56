<?php
namespace App\Models;

use CodeIgniter\Model;

class RecintoModel extends Model
{
    protected $table      = 'recinto';
    protected $primaryKey = 'nro_recinto';

    protected $allowedFields = [
        'tarifa_hora',
        'id_tipo_recinto',
        'habilitado'
    ];
}
