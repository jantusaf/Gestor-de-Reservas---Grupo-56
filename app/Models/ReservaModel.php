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

    public function listarReservas(string $dni = ''): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('reserva')
            ->select('reserva.*, persona.nombre, persona.apellido, persona.dni, horario.horario as hora, recinto.descripcion as recinto_desc, tipo_recinto.nombre_tipo_recinto')
            ->join('cliente',      'cliente.id_cliente = reserva.id_cliente')
            ->join('persona',      'persona.id_persona = cliente.id_persona')
            ->join('horario',      'horario.id_horario = reserva.id_horario')
            ->join('recinto',      'recinto.id_recinto = reserva.id_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->orderBy('reserva.fecha_reserva', 'DESC');

        if ($dni !== '') {
            $builder->like('persona.dni', $dni, 'after');
        }

        return $builder->get()->getResultArray();
    }

    public function altaReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $idUsuario, float $monto): int
    {
        $this->insert([
            'fecha_reserva'  => $fecha,
            'monto'          => $monto,
            'estado_reserva' => 'pendiente',
            'estado_pago'    => 'pendiente',
            'id_horario'     => $idHorario,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_usuario'     => $idUsuario,
        ]);
        return $this->getInsertID();
    }

    public function actualizarReserva(int $id, string $fecha, int $idCliente, int $idRecinto, int $idHorario, string $estadoReserva, string $estadoPago, float $montoActual): void
    {
        $recintoModel = new RecintoModel();
        $recinto = $recintoModel->find($idRecinto);
        $monto = $recinto ? $recinto['tarifa'] : $montoActual;

        $this->update($id, [
            'fecha_reserva'  => $fecha,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_horario'     => $idHorario,
            'estado_reserva' => $estadoReserva,
            'estado_pago'    => $estadoPago,
            'monto'          => $monto,
        ]);
    }

    public function cancelarReserva(int $id): void
    {
        $this->update($id, ['estado_reserva' => 'cancelada']);
    }

    public function validarReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $excluirId = 0): array
    {
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->find($idCliente);
        if (!$cliente || $cliente['estado_cliente'] !== 'activo') {
            return ['ok' => false, 'mensajes' => ['Cliente' => 'Cliente inválido o inactivo.']];
        }

        $recintoModel = new RecintoModel();
        $recinto = $recintoModel->find($idRecinto);
        if (!$recinto || $recinto['estado_recinto'] !== 'activo') {
            return ['ok' => false, 'mensajes' => ['Recinto' => 'Recinto inválido o no habilitado.']];
        }

        $horarioModel = new HorarioModel();
        $horario = $horarioModel->find($idHorario);
        if (!$horario) {
            return ['ok' => false, 'mensajes' => ['Hora' => 'Horario inválido.']];
        }

        if ($this->estaOcupado($fecha, $idRecinto, $idHorario, $excluirId)) {
            return ['ok' => false, 'mensajes' => ['Disponibilidad' => 'Ese horario ya está reservado.']];
        }

        return ['ok' => true, 'recinto' => $recinto];
    }

    public function estaOcupado(string $fecha, int $idRecinto, int $idHorario, int $excluirId = 0): bool
    {
        $builder = $this->where('fecha_reserva', $fecha)
            ->where('id_recinto', $idRecinto)
            ->where('id_horario', $idHorario)
            ->where('estado_reserva !=', 'cancelada');

        if ($excluirId > 0) {
            $builder->where('id_reserva !=', $excluirId);
        }

        return $builder->first() !== null;
    }

    public function horasDisponibles(string $fecha, int $idRecinto, int $excluirId = 0): array
    {
        $builder = $this->where('fecha_reserva', $fecha)
            ->where('id_recinto', $idRecinto)
            ->where('estado_reserva !=', 'cancelada');

        if ($excluirId > 0) {
            $builder->where('id_reserva !=', $excluirId);
        }

        $idsOcupados = array_column($builder->findAll(), 'id_horario');

        $horarioModel = new HorarioModel();
        $todos = $horarioModel->listarHorarios();

        return array_values(array_filter($todos, function ($h) use ($idsOcupados) {
            return !in_array($h['id_horario'], $idsOcupados);
        }));
    }
}
