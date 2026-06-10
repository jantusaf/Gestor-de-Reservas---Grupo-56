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
        'id_horario',
        'id_cliente',
        'id_recinto',
        'id_usuario',
    ];

    public function listarReservas(string $dni = ''): array
    {
        $db = \Config\Database::connect();
        return $db->query('CALL sp_listar_reservas(?)', [$dni])->getResultArray();
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
            (float) $validacion['recinto']['tarifa']
        );

        return ['ok' => true, 'id' => $id];
    }

    public function modificarReserva(int $id, string $fecha, int $idCliente, int $idRecinto, int $idHorario, string $estadoReserva): array
    {
        $original = $this->find($id);
        if (!$original) {
            return ['ok' => false, 'mensajes' => ['id' => 'Reserva no encontrada.']];
        }

        $fechaCambio = $original['fecha_reserva'] !== $fecha;

        $validacion = $this->validarReserva($fecha, $idCliente, $idRecinto, $idHorario, $id, $fechaCambio);
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

        // La cancelación y el reembolso deben guardarse juntos: o ambos, o ninguno.
        $db = \Config\Database::connect();
        $db->transBegin();

        $this->update($id, ['estado_reserva' => 'cancelada']);

        // El reembolso se registra como un cambio de estado del pago vigente,
        // no como un flag en la reserva. Solo aplica si la reserva estaba pagada.
        $pagoModel = new PagoModel();
        $pago      = $pagoModel->where('id_reserva', $id)->where('estado', 'pagada')->first();
        if ($pago) {
            $pagoModel->update($pago['id_pago'], ['estado' => 'reembolsado']);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'mensaje' => 'No se pudo cancelar la reserva.'];
        }

        $db->transCommit();
        return ['ok' => true];
    }

    public function validarReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $excluirId = 0, bool $validarFechaFutura = true): array
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

    // Persistencia pura (INSERT): solo la usa crearReserva() una vez validada la
    // reserva y calculado el monto. No valida nada por sí misma.
    private function altaReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $idUsuario, float $monto): int
    {
        $this->insert([
            'fecha_reserva'  => $fecha,
            'monto'          => $monto,
            'estado_reserva' => 'pendiente',
            'id_horario'     => $idHorario,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_usuario'     => $idUsuario,
        ]);
        return $this->getInsertID();
    }

    // Persistencia pura (UPDATE): solo la usa modificarReserva() una vez validada la
    // reserva y calculado el monto. No valida nada por sí misma.
    private function actualizarReserva(int $id, string $fecha, int $idCliente, int $idRecinto, int $idHorario, string $estadoReserva, float $monto): void
    {
        $this->update($id, [
            'fecha_reserva'  => $fecha,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_horario'     => $idHorario,
            'estado_reserva' => $estadoReserva,
            'monto'          => $monto,
        ]);
    }

    // Ayudante interno de validarReserva(): comprueba si ese recinto+fecha+horario
    // ya tiene una reserva no cancelada. No es una operación pública por sí misma.
    private function estaOcupado(string $fecha, int $idRecinto, int $idHorario, int $excluirId = 0): bool
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
