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

    public function datosFormularioAlta(int $idReserva): ?array
    {
        $reserva = (new ReservaModel())->find($idReserva);
        if (!$reserva) {
            return null;
        }

        return [
            'reserva' => $reserva,
            'medios'  => (new MedioPagoModel())->findAll(),
        ];
    }

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

    public function datosFactura(int $idReserva): ?array
    {
        $db  = \Config\Database::connect();
        $row = $db->query('CALL sp_datos_factura(?)', [$idReserva])->getRowArray();

        return $row ?: null;
    }

    public function altaPago(int $idReserva, int $idMedioPago, int $idUsuario): array
    {
        $reservaModel = new ReservaModel();
        $reserva = $reservaModel->find($idReserva);

        if (!$reserva) {
            return ['ok' => false, 'mensaje' => 'Reserva no encontrada.'];
        }

        if ($reserva['estado_pago'] === 'pagada') {
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'Esta reserva ya fue pagada.']];
        }

        if ($idMedioPago <= 0) {
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'El medio de pago es obligatorio.']];
        }

        $this->insert([
            'id_reserva'    => $idReserva,
            'monto_total'   => $reserva['monto'],
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
