<?php
namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\ClienteModel;
use App\Models\RecintoModel;
use App\Models\HorarioModel;
use App\Models\PersonaModel;
use CodeIgniter\Controller;

class ReservaController extends Controller
{
    public function altaReserva()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $db = \Config\Database::connect();

        $clientes = $db->table('cliente')
            ->select('cliente.id_cliente, cliente.email, cliente.estado_cliente, cliente.id_persona,
                      persona.nombre, persona.apellido, persona.dni')
            ->join('persona', 'persona.id_persona = cliente.id_persona')
            ->where('cliente.estado_cliente', 'activo')
            ->orderBy('persona.apellido', 'ASC')
            ->get()
            ->getResultArray();

        $recintos = $db->table('recinto')
            ->select('recinto.*, tipo_recinto.nombre_tipo_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->where('recinto.estado_recinto', 'activo')
            ->orderBy('tipo_recinto.nombre_tipo_recinto', 'ASC')
            ->get()
            ->getResultArray();

        $data['clientes'] = $clientes;
        $data['recintos'] = $recintos;

        return view('plantillas/head')
            . view('contenido/crud_reserva/alta_reserva', $data)
            . view('plantillas/footer');
    }

    public function horasDisponibles()
    {
        $fecha      = $this->request->getPost('fecha_reserva');
        $recinto    = $this->request->getPost('id_recinto');
        $excluirId  = $this->request->getPost('excluir_reserva'); // al editar, excluye la reserva actual

        $reservaModel = new ReservaModel();
        $horarioModel = new HorarioModel();

        $builder = $reservaModel
            ->where('fecha_reserva', $fecha)
            ->where('id_recinto', $recinto)
            ->where('estado_reserva !=', 'cancelada');

        if ($excluirId) {
            $builder->where('id_reserva !=', $excluirId);
        }

        $idsOcupados = array_column($builder->findAll(), 'id_horario');

        $todos = $horarioModel->findAll();
        $disponibles = array_filter($todos, function($h) use ($idsOcupados) {
            return !in_array($h['id_horario'], $idsOcupados);
        });

        return $this->response->setJSON(array_values($disponibles));
    }

    public function guardarReserva()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $fecha     = $this->request->getPost('fecha_reserva');
        $idCliente = $this->request->getPost('id_cliente');
        $idRecinto = $this->request->getPost('id_recinto');
        $idHorario = $this->request->getPost('id_horario');
        $idUsuario = session()->get('id_usuario');

        $validacion = $this->validarReserva($fecha, $idCliente, $idRecinto, $idHorario);

        if (!$validacion['ok']) {
            return redirect()->back()->with('error', $validacion['mensaje']);
        }

        $recinto = $validacion['recinto'];
        $monto   = $recinto['tarifa'] ?? 0;

        $reservaModel = new ReservaModel();
        $data = [
            'fecha_reserva'  => $fecha,
            'monto'          => $monto,
            'estado_reserva' => 'pendiente',
            'estado_pago'    => 'pendiente',
            'id_horario'     => $idHorario,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_usuario'     => $idUsuario
        ];

        if ($reservaModel->insert($data)) {
            $nuevaId = $reservaModel->getInsertID();
            return redirect()->to(base_url('reserva/crear'))
                ->with('nueva_reserva_id', $nuevaId);
        }

        return redirect()->back()->with('error', 'Error al guardar la reserva.');
    }

    public function listarReservas()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $db  = \Config\Database::connect();
        $dni = trim($this->request->getGet('dni') ?? '');

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

        $reservas = $builder->get()->getResultArray();

        return view('plantillas/head', ['title' => 'Listado de Reservas'])
            . view('contenido/crud_reserva/listar_reservas', ['reservas' => $reservas, 'dni_busqueda' => $dni])
            . view('plantillas/footer');
    }

    public function editarReserva($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $reserva = $reservaModel->find($id);

        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        $clienteModel = new ClienteModel();
        $personaModel = new PersonaModel();
        $horarioModel = new HorarioModel();

        $clientes = $clienteModel->where('estado_cliente', 'activo')->findAll();
        foreach ($clientes as &$c) {
            $c['persona'] = $personaModel->find($c['id_persona']);
        }

        $db = \Config\Database::connect();
        $recintos = $db->table('recinto')
            ->select('recinto.*, tipo_recinto.nombre_tipo_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->where('recinto.estado_recinto', 'activo')
            ->get()
            ->getResultArray();

        $horarios = $horarioModel->findAll();

        $data = [
            'title'    => 'Editar Reserva',
            'reserva'  => $reserva,
            'clientes' => $clientes,
            'recintos' => $recintos,
            'horarios' => $horarios,
        ];

        return view('plantillas/head', $data)
            . view('contenido/crud_reserva/editar_reserva', $data)
            . view('plantillas/footer');
    }

    public function actualizarReserva($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $reserva = $reservaModel->find($id);

        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        $fecha         = $this->request->getPost('fecha_reserva');
        $idCliente     = $this->request->getPost('id_cliente');
        $idRecinto     = $this->request->getPost('id_recinto');
        $idHorario     = $this->request->getPost('id_horario');
        $estadoReserva = $this->request->getPost('estado_reserva');
        $estadoPago    = $this->request->getPost('estado_pago');

        if (empty($fecha) || empty($idCliente) || empty($idRecinto) || empty($idHorario)) {
            return redirect()->back()->with('error', 'Todos los campos son obligatorios.');
        }

        if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->with('error', 'La fecha no puede ser anterior a hoy.');
        }

        $ocupada = $reservaModel
            ->where('fecha_reserva', $fecha)
            ->where('id_recinto', $idRecinto)
            ->where('id_horario', $idHorario)
            ->where('estado_reserva !=', 'cancelada')
            ->where('id_reserva !=', $id)
            ->first();

        if ($ocupada) {
            return redirect()->back()->with('error', 'Ese horario ya está reservado para esa fecha y recinto.');
        }

        $recintoModel = new RecintoModel();
        $recinto = $recintoModel->find($idRecinto);
        $monto   = $recinto ? $recinto['tarifa'] : $reserva['monto'];

        $reservaModel->update($id, [
            'fecha_reserva'  => $fecha,
            'id_cliente'     => $idCliente,
            'id_recinto'     => $idRecinto,
            'id_horario'     => $idHorario,
            'estado_reserva' => $estadoReserva,
            'estado_pago'    => $estadoPago,
            'monto'          => $monto,
        ]);

        return redirect()->to('/reserva/listar')->with('success', 'Reserva actualizada correctamente.');
    }

    public function cancelarReserva($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $reserva = $reservaModel->find($id);

        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        if ($reserva['estado_reserva'] === 'cancelada') {
            return redirect()->to('/reserva/listar')->with('error', 'La reserva ya está cancelada.');
        }

        $reservaModel->update($id, ['estado_reserva' => 'cancelada']);
        return redirect()->to('/reserva/listar')->with('success', 'Reserva cancelada correctamente.');
    }

    private function validarReserva($fecha, $idCliente, $idRecinto, $idHorario)
    {
        $clienteModel = new ClienteModel();
        $recintoModel = new RecintoModel();
        $reservaModel = new ReservaModel();
        $horarioModel = new HorarioModel();

        if (empty($fecha))     return ['ok' => false, 'mensaje' => 'Falta la fecha.'];
        if (empty($idCliente)) return ['ok' => false, 'mensaje' => 'Falta el cliente.'];
        if (empty($idRecinto)) return ['ok' => false, 'mensaje' => 'Falta el recinto.'];
        if (empty($idHorario)) return ['ok' => false, 'mensaje' => 'Falta la hora.'];

        if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
            return ['ok' => false, 'mensaje' => 'La fecha no puede ser anterior a hoy.'];
        }

        $cliente = $clienteModel->find($idCliente);
        if (!$cliente || $cliente['estado_cliente'] !== 'activo') {
            return ['ok' => false, 'mensaje' => 'Cliente inválido o inactivo.'];
        }

        $recinto = $recintoModel->find($idRecinto);
        if (!$recinto || $recinto['estado_recinto'] !== 'activo') {
            return ['ok' => false, 'mensaje' => 'Recinto inválido o no habilitado.'];
        }

        $horario = $horarioModel->find($idHorario);
        if (!$horario) {
            return ['ok' => false, 'mensaje' => 'Horario inválido.'];
        }

        $ocupada = $reservaModel
            ->where('fecha_reserva', $fecha)
            ->where('id_recinto', $idRecinto)
            ->where('id_horario', $idHorario)
            ->where('estado_reserva !=', 'cancelada')
            ->first();

        if ($ocupada) {
            return ['ok' => false, 'mensaje' => 'Ese horario ya está reservado.'];
        }

        return ['ok' => true, 'recinto' => $recinto];
    }
}