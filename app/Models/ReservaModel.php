<?php
namespace App\Models;

use CodeIgniter\Model;

class ReservaModel extends Model
{
    protected $table      = 'reserva';
    protected $primaryKey = 'id_reserva';

    protected $allowedFields = [
        'fecha_reserva',
        'id_cliente',
        'nro_recinto',
        'id_usuario',
        'hora_reserva',
        'monto_reserva',
        'pagado',
        'estado'
    ];
}
