<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table      = 'cliente';
    protected $primaryKey = 'id_cliente';

    protected $allowedFields = [
        'email',
        'fecha_alta',
        'estado_cliente',
        'id_persona'
    ];
}
