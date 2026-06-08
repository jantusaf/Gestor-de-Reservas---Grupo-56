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

    public function datosFormulario(): array
    {
        return [
            'clientes' => (new ClienteModel())->listarClientesActivos(),
            'recintos' => (new RecintoModel())->listarRecintosActivos(),
        ];
    }

    public function datosFormularioEditar(int $id): ?array
    {
        $reserva = $this->find($id);
        if (!$reserva) {
            return null;
        }

        return [
            'reserva'  => $reserva,
            'clientes' => (new ClienteModel())->listarClientesActivos(),
            'recintos' => (new RecintoModel())->listarRecintosActivos(),
            'horarios' => (new HorarioModel())->listarHorarios(),
        ];
    }

    public function listarReservas(string $dni = ''): array
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('reserva')
            ->select('reserva.*, persona.nombre, persona.apellido, persona.dni, horario.horario as hora, recinto.descripcion as recinto_desc, tipo_recinto.nombre_tipo_recinto, medio_pago.nombre_medio_pago')
            ->join('cliente',      'cliente.id_cliente = reserva.id_cliente')
            ->join('persona',      'persona.id_persona = cliente.id_persona')
            ->join('horario',      'horario.id_horario = reserva.id_horario')
            ->join('recinto',      'recinto.id_recinto = reserva.id_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->join('pago',         'pago.id_reserva = reserva.id_reserva', 'left')
            ->join('medio_pago',   'medio_pago.id_medio_pago = pago.id_medio_pago', 'left')
            ->groupBy('reserva.id_reserva')
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

    public function actualizarReserva(int $id, string $fecha, int $idCliente, int $idRecinto, int $idHorario, string $estadoReserva, string $estadoPago, float $monto): void
    {
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

    public function crearReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $idUsuario): array
    {
        $validacion = $this->validarReserva($fecha, $idCliente, $idRecinto, $idHorario);
        if (!$validacion['ok']) {
            return $validacion;
        }

        $id = $this->altaReserva(
            $fecha,
            $idCliente,
            $idRecinto,
            $idHorario,
            $idUsuario,
            $validacion['recinto']['tarifa']
        );

        return ['ok' => true, 'id' => $id];
    }

    public function modificarReserva(int $id, string $fecha, int $idCliente, int $idRecinto, int $idHorario, string $estadoReserva, string $estadoPago): array
    {
        $validacion = $this->validarReserva($fecha, $idCliente, $idRecinto, $idHorario, $id);
        if (!$validacion['ok']) {
            return $validacion;
        }

        $this->actualizarReserva(
            $id,
            $fecha,
            $idCliente,
            $idRecinto,
            $idHorario,
            $estadoReserva,
            $estadoPago,
            (float) $validacion['recinto']['tarifa']
        );

        return ['ok' => true];
    }

    public function cancelarReserva(int $id): array
    {
        $reserva = $this->find($id);
        if (!$reserva) {
            return ['ok' => false, 'mensaje' => 'Reserva no encontrada.'];
        }
        if ($reserva['estado_reserva'] === 'cancelada') {
            return ['ok' => false, 'mensaje' => 'La reserva ya está cancelada.'];
        }

        $estadoPago = $reserva['estado_pago'] === 'pagado' ? 'reembolsado' : $reserva['estado_pago'];

        $this->update($id, [
            'estado_reserva' => 'cancelada',
            'estado_pago'    => $estadoPago
        ]);
        return ['ok' => true];
    }

    public function validarReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $excluirId = 0): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'fecha_reserva' => ['label' => 'Fecha',  'rules' => 'required|valid_date|check_future_or_today'],
            'id_cliente'    => ['label' => 'Cliente', 'rules' => 'required|integer|greater_than[0]',
                                'errors' => ['required' => 'El campo Cliente es obligatorio.', 'integer' => 'El campo Cliente es inválido.', 'greater_than' => 'El campo Cliente es obligatorio.']],
            'id_recinto'    => ['label' => 'Recinto', 'rules' => 'required|integer|greater_than[0]',
                                'errors' => ['required' => 'El campo Recinto es obligatorio.', 'integer' => 'El campo Recinto es inválido.', 'greater_than' => 'El campo Recinto es obligatorio.']],
            'id_horario'    => ['label' => 'Horario', 'rules' => 'required|integer|greater_than[0]',
                                'errors' => ['required' => 'El campo Horario es obligatorio.', 'integer' => 'El campo Horario es inválido.', 'greater_than' => 'El campo Horario es obligatorio.']],
        ])->run([
            'fecha_reserva' => $fecha,
            'id_cliente'    => $idCliente,
            'id_recinto'    => $idRecinto,
            'id_horario'    => $idHorario,
        ])) {
            return ['ok' => false, 'mensajes' => $validation->getErrors()];
        }

        $cliente = (new ClienteModel())->find($idCliente);
        if (!$cliente || $cliente['estado_cliente'] !== 'activo') {
            return ['ok' => false, 'mensajes' => ['id_cliente' => 'Cliente inválido o inactivo.']];
        }

        $recinto = (new RecintoModel())->find($idRecinto);
        if (!$recinto || $recinto['estado_recinto'] !== 'activo') {
            return ['ok' => false, 'mensajes' => ['id_recinto' => 'Recinto inválido o no habilitado.']];
        }

        $horario = (new HorarioModel())->find($idHorario);
        if (!$horario) {
            return ['ok' => false, 'mensajes' => ['id_horario' => 'Horario inválido.']];
        }

        if ($this->estaOcupado($fecha, $idRecinto, $idHorario, $excluirId)) {
            return ['ok' => false, 'mensajes' => ['disponibilidad' => 'Ese horario ya está reservado.']];
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

        $idsOcupados  = array_column($builder->findAll(), 'id_horario');
        $todos        = (new HorarioModel())->listarHorarios();

        return array_values(array_filter($todos, fn($h) => !in_array($h['id_horario'], $idsOcupados)));
    }
}
