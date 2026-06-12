<?php
namespace App\Models;
use CodeIgniter\Model;

class MedioPagoModel extends Model
{
    protected $table = 'medio_pago';
    protected $primaryKey = 'id_medio_pago';
    protected $allowedFields = ['nombre_medio_pago'];
}
