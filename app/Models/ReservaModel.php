<?php
namespace App\Models;

use CodeIgniter\Model;

class ReservaModel extends Model
{
    protected $table      = 'reserva';
    protected $primaryKey = 'id_reserva';

    protected $allowedFields = [
        'fecha_reserva',
        'monto',
        'estado_reserva',
        'estado_pago',
        'id_horario',
        'id_cliente',
        'id_recinto',
        'id_usuario'
    ];
}
