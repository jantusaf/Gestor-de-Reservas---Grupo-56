<?php
namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\ClienteModel;
use App\Models\RecintoModel;
use App\Models\HorarioModel;
use App\Entities\Reserva;
use CodeIgniter\Controller;

class ReservaController extends Controller
{
    public function formularioAlta()
    {
        return view('plantillas/head')
            . view('contenido/crud_reserva/alta_reserva', [
                'clientes' => (new ClienteModel())->listarClientesActivos(),
                'recintos' => (new RecintoModel())->listarRecintosActivos(),
            ])
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
        $reservaModel = new ReservaModel();

        $reserva = new Reserva([
            'fecha_reserva' => $this->request->getPost('fecha_reserva') ?? '',
            'id_cliente'    => (int) $this->request->getPost('id_cliente'),
            'id_recinto'    => (int) $this->request->getPost('id_recinto'),
            'id_horario'    => (int) $this->request->getPost('id_horario'),
            'id_usuario'    => (int) session()->get('id_usuario'),
        ]);

        $resultado = $reservaModel->crearReserva($reserva);

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to(base_url('reserva/crear'))->with('nueva_reserva_id', $resultado['id']);
    }

    public function listarReservas()
    {
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
        $reservaModel = new ReservaModel();
        $reserva      = $reservaModel->find((int) $id);

        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        return view('plantillas/head', ['title' => 'Editar Reserva'])
            . view('contenido/crud_reserva/editar_reserva', [
                'reserva'  => $reserva,
                'clientes' => (new ClienteModel())->listarClientesActivos(),
                'recintos' => (new RecintoModel())->listarRecintosActivos(),
                'horarios' => (new HorarioModel())->listarHorarios(),
            ])
            . view('plantillas/footer');
    }

    public function actualizarReserva($id)
    {
        $reservaModel = new ReservaModel();

        $reserva = new Reserva([
            'id_reserva'     => (int) $id,
            'fecha_reserva'  => $this->request->getPost('fecha_reserva') ?? '',
            'id_cliente'     => (int) $this->request->getPost('id_cliente'),
            'id_recinto'     => (int) $this->request->getPost('id_recinto'),
            'id_horario'     => (int) $this->request->getPost('id_horario'),
            'estado_reserva' => $this->request->getPost('estado_reserva') ?? '',
        ]);

        $resultado = $reservaModel->modificarReserva($reserva);

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to('/reserva/listar')->with('success', 'Reserva actualizada correctamente.');
    }

    public function cancelarReserva($id)
    {
        $reservaModel = new ReservaModel();
        $resultado    = $reservaModel->cancelarReserva((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/reserva/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/reserva/listar')->with('success', 'Reserva cancelada correctamente.');
    }
}
