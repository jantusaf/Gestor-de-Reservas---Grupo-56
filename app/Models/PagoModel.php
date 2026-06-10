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
        'estado',
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
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'Reserva no encontrada.']];
        }

        // El estado de pago vive en la tabla 'pago': la reserva está pagada
        // si ya existe un pago vigente (no reembolsado) asociado.
        $pagoVigente = $this->where('id_reserva', $idReserva)->where('estado', 'pagada')->first();
        if ($pagoVigente) {
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'Esta reserva ya fue pagada.']];
        }

        if ($idMedioPago <= 0) {
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'El medio de pago es obligatorio.']];
        }

        // El pago y la confirmación de la reserva deben guardarse juntos:
        // o se registran ambos, o ninguno.
        $db = \Config\Database::connect();
        $db->transBegin();

        $this->insert([
            'id_reserva'    => $idReserva,
            'monto_total'   => $reserva['monto'],
            'estado'        => 'pagada',
            'id_medio_pago' => $idMedioPago,
            'fecha_pago'    => date('Y-m-d'),
            'id_usuario'    => $idUsuario,
        ]);

        $reservaModel->update($idReserva, [
            'estado_reserva' => 'confirmada',
        ]);

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'mensajes' => ['id_medio_pago' => 'No se pudo registrar el pago.']];
        }

        $db->transCommit();
        return ['ok' => true];
    }
}
