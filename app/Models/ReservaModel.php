<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Reserva;

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
        'id_usuario',
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
        $db = \Config\Database::connect();
        return $db->query('CALL sp_listar_reservas(?)', [$dni])->getResultArray();
    }

    // Firma pública mantenida para el Controller
    public function crearReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $idUsuario): array
    {
        $reserva = new Reserva([
            'fecha_reserva' => $fecha,
            'id_cliente'    => $idCliente,
            'id_recinto'    => $idRecinto,
            'id_horario'    => $idHorario,
            'id_usuario'    => $idUsuario,
        ]);

        $validacion = $this->validarReserva($reserva);
        if (!$validacion['ok']) {
            return $validacion;
        }

        $reserva->monto = $validacion['recinto']['tarifa'];

        $id = $this->altaReserva($reserva);
        return ['ok' => true, 'id' => $id];
    }

    // Firma pública mantenida para el Controller
    public function modificarReserva(int $id, string $fecha, int $idCliente, int $idRecinto, int $idHorario, string $estadoReserva, string $estadoPago): array
    {
        $original    = $this->find($id);
        $fechaCambio = !$original || $original['fecha_reserva'] !== $fecha;

        $reserva = new Reserva([
            'fecha_reserva'  => $fecha,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_horario'     => $idHorario,
            'estado_reserva' => $estadoReserva,
            'estado_pago'    => $estadoPago,
        ]);

        $validacion = $this->validarReserva($reserva, $id, $fechaCambio);
        if (!$validacion['ok']) {
            return $validacion;
        }

        $reserva->monto = (float) $validacion['recinto']['tarifa'];

        $this->actualizarReserva($id, $reserva);
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
            'estado_pago'    => $estadoPago,
        ]);
        return ['ok' => true];
    }

    public function validarReserva(Reserva $reserva, int $excluirId = 0, bool $validarFechaFutura = true): array
    {
        $validation = \Config\Services::validation();

        $reglasFecha = $validarFechaFutura ? 'required|valid_date|check_future_or_today' : 'required|valid_date';

        if (!$validation->setRules([
            'fecha_reserva' => ['label' => 'Fecha',   'rules' => $reglasFecha],
            'id_cliente'    => ['label' => 'Cliente',  'rules' => 'required|integer|greater_than[0]',
                                'errors' => ['required' => 'El campo Cliente es obligatorio.', 'integer' => 'El campo Cliente es inválido.', 'greater_than' => 'El campo Cliente es obligatorio.']],
            'id_recinto'    => ['label' => 'Recinto',  'rules' => 'required|integer|greater_than[0]',
                                'errors' => ['required' => 'El campo Recinto es obligatorio.', 'integer' => 'El campo Recinto es inválido.', 'greater_than' => 'El campo Recinto es obligatorio.']],
            'id_horario'    => ['label' => 'Horario',  'rules' => 'required|integer|greater_than[0]',
                                'errors' => ['required' => 'El campo Horario es obligatorio.', 'integer' => 'El campo Horario es inválido.', 'greater_than' => 'El campo Horario es obligatorio.']],
        ])->run([
            'fecha_reserva' => $reserva->fecha_reserva,
            'id_cliente'    => $reserva->id_cliente,
            'id_recinto'    => $reserva->id_recinto,
            'id_horario'    => $reserva->id_horario,
        ])) {
            return ['ok' => false, 'mensajes' => $validation->getErrors()];
        }

        $cliente = (new ClienteModel())->find($reserva->id_cliente);
        if (!$cliente || $cliente['estado_cliente'] !== 'activo') {
            return ['ok' => false, 'mensajes' => ['id_cliente' => 'Cliente inválido o inactivo.']];
        }

        $recinto = (new RecintoModel())->find($reserva->id_recinto);
        if (!$recinto || $recinto['estado_recinto'] !== 'activo') {
            return ['ok' => false, 'mensajes' => ['id_recinto' => 'Recinto inválido o no habilitado.']];
        }

        $horario = (new HorarioModel())->find($reserva->id_horario);
        if (!$horario) {
            return ['ok' => false, 'mensajes' => ['id_horario' => 'Horario inválido.']];
        }

        if ($this->estaOcupado($reserva->fecha_reserva, $reserva->id_recinto, $reserva->id_horario, $excluirId)) {
            return ['ok' => false, 'mensajes' => ['disponibilidad' => 'Ese horario ya está reservado.']];
        }

        return ['ok' => true, 'recinto' => $recinto];
    }

    public function altaReserva(Reserva $reserva): int
    {
        $this->insert([
            'fecha_reserva'  => $reserva->fecha_reserva,
            'monto'          => $reserva->monto,
            'estado_reserva' => 'pendiente',
            'estado_pago'    => 'pendiente',
            'id_horario'     => $reserva->id_horario,
            'id_cliente'     => $reserva->id_cliente,
            'id_recinto'     => $reserva->id_recinto,
            'id_usuario'     => $reserva->id_usuario,
        ]);
        return $this->getInsertID();
    }

    public function actualizarReserva(int $id, Reserva $reserva): void
    {
        $this->update($id, [
            'fecha_reserva'  => $reserva->fecha_reserva,
            'id_cliente'     => $reserva->id_cliente,
            'id_recinto'     => $reserva->id_recinto,
            'id_horario'     => $reserva->id_horario,
            'estado_reserva' => $reserva->estado_reserva,
            'estado_pago'    => $reserva->estado_pago,
            'monto'          => $reserva->monto,
        ]);
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
        $todos       = (new HorarioModel())->listarHorarios();

        return array_values(array_filter($todos, fn($h) => !in_array($h['id_horario'], $idsOcupados)));
    }
}
