<?php
namespace App\Controllers;

use App\Models\ReservaModel;
use CodeIgniter\Controller;

class ReservaController extends Controller
{
    public function formularioAlta()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();

        return view('plantillas/head')
            . view('contenido/crud_reserva/alta_reserva', $reservaModel->datosFormulario())
            . view('plantillas/footer');
    }

    public function horasDisponibles()
    {
        $fecha     = $this->request->getPost('fecha_reserva');
        $idRecinto = (int) $this->request->getPost('id_recinto');
        $excluirId = (int) ($this->request->getPost('excluir_reserva') ?? 0);

        $reservaModel = new ReservaModel();

        return $this->response->setJSON(
            $reservaModel->horasDisponibles($fecha, $idRecinto, $excluirId)
        );
    }

    public function guardarReserva()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $resultado    = $reservaModel->crearReserva(
            $this->request->getPost('fecha_reserva') ?? '',
            $this->request->getPost('id_cliente') ?? '',
            $this->request->getPost('id_recinto') ?? '',
            $this->request->getPost('id_horario') ?? '',
            (int) session()->get('id_usuario'),
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to(base_url('reserva/crear'))->with('nueva_reserva_id', $resultado['id']);
    }

    public function listarReservas()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $dni          = trim($this->request->getGet('dni') ?? '');

        return view('plantillas/head', ['title' => 'Listado de Reservas'])
            . view('contenido/crud_reserva/listar_reservas', [
                'reservas'     => $reservaModel->listarReservas($dni),
                'dni_busqueda' => $dni,
            ])
            . view('plantillas/footer');
    }

    public function formularioEditar($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $data         = $reservaModel->datosFormularioEditar((int) $id);

        if (!$data) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        return view('plantillas/head', ['title' => 'Editar Reserva'])
            . view('contenido/crud_reserva/editar_reserva', $data)
            . view('plantillas/footer');
    }

    public function actualizarReserva($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $resultado    = $reservaModel->modificarReserva(
            (int) $id,
            $this->request->getPost('fecha_reserva') ?? '',
            $this->request->getPost('id_cliente') ?? '',
            $this->request->getPost('id_recinto') ?? '',
            $this->request->getPost('id_horario') ?? '',
            $this->request->getPost('estado_reserva') ?? '',
            $this->request->getPost('estado_pago') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to('/reserva/listar')->with('success', 'Reserva actualizada correctamente.');
    }

    public function cancelarReserva($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        $reservaModel = new ReservaModel();
        $resultado    = $reservaModel->cancelarReserva((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/reserva/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/reserva/listar')->with('success', 'Reserva cancelada correctamente.');
    }
}
