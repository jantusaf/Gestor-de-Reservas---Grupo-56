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

    public function listarPagos(): array
    {
        $db = \Config\Database::connect();
        return $db->table('pago')
            ->select('pago.id_reserva, pago.fecha_pago, pago.monto_total,
                    persona.nombre,
                    persona.apellido,
                    medio_pago.nombre_medio_pago,
                    usuario.nombre_usuario')
            ->join('reserva',    'reserva.id_reserva = pago.id_reserva')
            ->join('cliente',    'cliente.id_cliente = reserva.id_cliente')
            ->join('persona',    'persona.id_persona = cliente.id_persona')
            ->join('medio_pago', 'medio_pago.id_medio_pago = pago.id_medio_pago')
            ->join('usuario',    'usuario.id_usuario = pago.id_usuario')
            ->get()
            ->getResultArray();
    }

    public function altaPago(int $idReserva, float $montoTotal, int $idMedioPago, int $idUsuario): array
    {
        $reservaModel = new ReservaModel();
        $reserva = $reservaModel->find($idReserva);

        if (!$reserva) {
            return ['ok' => false, 'error' => 'Reserva no encontrada.'];
        }

        if ($montoTotal != $reserva['monto']) {
            return ['ok' => false, 'error' => 'El monto debe ser exactamente igual al de la reserva.'];
        }

        $this->insert([
            'id_reserva'    => $idReserva,
            'monto_total'   => $montoTotal,
            'id_medio_pago' => $idMedioPago,
            'fecha_pago'    => date('Y-m-d'),
            'id_usuario'    => $idUsuario,
        ]);

        $reservaModel->update($idReserva, [
            'estado_reserva' => 'confirmada',
            'estado_pago'    => 'pagada',
        ]);

        return ['ok' => true];
    }
}
