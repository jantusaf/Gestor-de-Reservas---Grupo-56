<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table      = 'cliente';
    protected $primaryKey = 'id_cliente';

    protected $allowedFields = [
        'dni_cliente',
        'nombre_cliente',
        'apellido_cliente',
        'telefono_cliente',
        'estado_cliente',
        'email_cliente'
    ];
}
