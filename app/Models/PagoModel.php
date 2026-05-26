<?php
namespace App\Models;
use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table      = 'pago';
    protected $primaryKey = 'id_pago';
    protected $allowedFields = [
        'fecha_pago',
        'monto_total',
        'id_reserva',
        'id_medio_pago',
        'id_usuario'
    ];
}
