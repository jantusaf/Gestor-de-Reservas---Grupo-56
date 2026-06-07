<?php
namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\ClienteModel;
use App\Models\RecintoModel;
use App\Models\HorarioModel;
use CodeIgniter\Controller;

class ReservaController extends Controller
{
    public function altaReserva()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $clienteModel = new ClienteModel();
        $recintoModel = new RecintoModel();

        $data['clientes'] = $clienteModel->listarClientesActivos();
        $data['recintos'] = $recintoModel->listarRecintosActivos();

        return view('plantillas/head')
            . view('contenido/crud_reserva/alta_reserva', $data)
            . view('plantillas/footer');
    }

    public function horasDisponibles()
    {
        $fecha     = $this->request->getPost('fecha_reserva');
        $idRecinto = (int) $this->request->getPost('id_recinto');
        $excluirId = (int) ($this->request->getPost('excluir_reserva') ?? 0);

        $reservaModel = new ReservaModel();
        $disponibles = $reservaModel->horasDisponibles($fecha, $idRecinto, $excluirId);

        return $this->response->setJSON($disponibles);
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
        $idUsuario = (int) session()->get('id_usuario');

        $validation = \Config\Services::validation();
        if (!$validation->setRules([
            'Recinto' => 'required',
            'Cliente' => 'required',
            'Fecha'   => 'required|valid_date|check_future_or_today',
            'Hora'    => 'required',
        ])->run(['Recinto' => $idRecinto, 'Cliente' => $idCliente, 'Fecha' => $fecha, 'Hora' => $idHorario])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $reservaModel = new ReservaModel();
        $resultado = $reservaModel->validarReserva($fecha, (int) $idCliente, (int) $idRecinto, (int) $idHorario);

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        $monto   = $resultado['recinto']['tarifa'] ?? 0;
        $nuevaId = $reservaModel->altaReserva($fecha, (int) $idCliente, (int) $idRecinto, (int) $idHorario, $idUsuario, $monto);

        return redirect()->to(base_url('reserva/crear'))->with('nueva_reserva_id', $nuevaId);
    }

    public function listarReservas()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $dni = trim($this->request->getGet('dni') ?? '');
        $reservas = $reservaModel->listarReservas($dni);

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
        $recintoModel = new RecintoModel();
        $horarioModel = new HorarioModel();

        // listarClientesActivos devuelve array plano; adaptamos la estructura
        // para que la vista pueda acceder $c['persona']['nombre']
        $clientes = $clienteModel->listarClientesActivos();
        foreach ($clientes as &$c) {
            $c['persona'] = ['nombre' => $c['nombre'], 'apellido' => $c['apellido']];
        }

        $data = [
            'title'    => 'Editar Reserva',
            'reserva'  => $reserva,
            'clientes' => $clientes,
            'recintos' => $recintoModel->listarRecintosActivos(),
            'horarios' => $horarioModel->listarHorarios(),
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

        $validation = \Config\Services::validation();
        if (!$validation->setRules([
            'Recinto' => 'required',
            'Cliente' => 'required',
            'Fecha'   => 'required|valid_date|check_future_or_today',
            'Hora'    => 'required',
        ])->run(['Recinto' => $idRecinto, 'Cliente' => $idCliente, 'Fecha' => $fecha, 'Hora' => $idHorario])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        if ($reservaModel->estaOcupado($fecha, (int) $idRecinto, (int) $idHorario, $id)) {
            return redirect()->back()->with('error', 'Ese horario ya está reservado para esa fecha y recinto.');
        }

        $reservaModel->actualizarReserva($id, $fecha, (int) $idCliente, (int) $idRecinto, (int) $idHorario, $estadoReserva, $estadoPago, $reserva['monto']);

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

        $reservaModel->cancelarReserva($id);
        return redirect()->to('/reserva/listar')->with('success', 'Reserva cancelada correctamente.');
    }
}
