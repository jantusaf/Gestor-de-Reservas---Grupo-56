<?php
namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\ClienteModel;
use App\Models\RecintoModel;
use App\Models\HorarioModel;
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
        $fecha = $this->request->getPost('fecha_reserva');
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

        $resultado = $reservaModel->crearReserva(
            $this->request->getPost('fecha_reserva') ?? '',
            (int) $this->request->getPost('id_cliente'),
            (int) $this->request->getPost('id_recinto'),
            (int) $this->request->getPost('id_horario'),
            (int) session()->get('id_usuario')
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to(base_url('reserva/crear'))->with('nueva_reserva_id', $resultado['id']);
    }

    public function listarReservas()
    {
        $reservaModel = new ReservaModel();
        $dni = trim($this->request->getGet('dni') ?? '');

        return view('plantillas/head', ['title' => 'Listado de Reservas'])
            . view('contenido/crud_reserva/listar_reservas', [
                'reservas' => $reservaModel->listarReservas($dni),
                'dni_busqueda' => $dni,
            ])
            . view('plantillas/footer');
    }

    public function formularioEditar($id)
    {
        $reservaModel = new ReservaModel();
        $reserva = $reservaModel->find((int) $id);

        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        return view('plantillas/head', ['title' => 'Editar Reserva'])
            . view('contenido/crud_reserva/editar_reserva', [
                'reserva' => $reserva,
                'clientes' => (new ClienteModel())->listarClientesActivos(),
                'recintos' => (new RecintoModel())->listarRecintosActivos(),
                'horarios' => (new HorarioModel())->listarHorarios(),
            ])
            . view('plantillas/footer');
    }

    public function actualizarReserva($id)
    {
        $reservaModel = new ReservaModel();

        $resultado = $reservaModel->modificarReserva(
            (int) $id,
            $this->request->getPost('fecha_reserva') ?? '',
            (int) $this->request->getPost('id_cliente'),
            (int) $this->request->getPost('id_recinto'),
            (int) $this->request->getPost('id_horario'),
            $this->request->getPost('estado_reserva') ?? ''
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to('/reserva/listar')->with('success', 'Reserva actualizada correctamente.');
    }

    public function cancelarReserva($id)
    {
        $resultado = (new ReservaModel())->ejecutarAccion((int) $id, 'cancelar');

        if (!$resultado['ok']) {
            return redirect()->to('/reserva/listar')->with('error', $resultado['mensaje']);
        }

        $msg = $resultado['reembolso']
            ? 'Reserva cancelada. El pago será reembolsado.'
            : 'Reserva cancelada. No corresponde reembolso.';

        return redirect()->to('/reserva/listar')->with('success', $msg);
    }
}
