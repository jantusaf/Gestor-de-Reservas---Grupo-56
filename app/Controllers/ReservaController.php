<?php

namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\ClienteModel;
use App\Models\RecintoModel;
use CodeIgniter\Controller;

class ReservaController extends Controller
{
    // FORMULARIO DE CREACIÓN DE RESERVA
    public function crear()
    {
        $clienteModel = new ClienteModel();
        $recintoModel = new RecintoModel();

        $data['clientes'] = $clienteModel->where('estado_cliente', 'activo')->findAll();
        $data['recintos'] = $recintoModel->where('habilitado', 1)->findAll();

        return view('plantillas/head')
            . view('contenido/crud_reserva/alta_reserva', $data)
            . view('plantillas/footer');
    }

    // HORAS DISPONIBLES SEGÚN FECHA Y RECINTO
    public function horasDisponibles()
    {
        $fecha = $this->request->getPost('fecha_reserva');
        $recinto = $this->request->getPost('nro_recinto');

        $model = new ReservaModel();
        $ocupadas = $model->select('hora_reserva')
                          ->where('fecha_reserva', $fecha)
                          ->where('nro_recinto', $recinto)
                          ->findAll();

        $horasOcupadas = array_column($ocupadas, 'hora_reserva');

        // Ejemplo: rango de horas de 08:00 a 22:00
        $horasDisponibles = [];
        for ($h = 8; $h <= 22; $h++) {
            $hora = sprintf('%02d:00:00', $h);
            if (!in_array($hora, $horasOcupadas)) {
                $horasDisponibles[] = $hora;
            }
        }

        return $this->response->setJSON($horasDisponibles);
    }


    // GUARDAR RESERVA
    public function save()
    {
        $model = new ReservaModel();

        $fecha   = $this->request->getPost('fecha_reserva');
        $cliente = $this->request->getPost('id_cliente');
        $recinto = $this->request->getPost('nro_recinto');
        $hora    = $this->request->getPost('hora_reserva');

        // Tomar el usuario directamente de la sesión
        $usuario = session()->get('id_usuario');

        // Validaciones básicas
        if (empty($fecha) || empty($cliente) || empty($recinto) || empty($hora) || empty($usuario)) {
            return redirect()->back()->with('error', 'Complete todos los campos.');
        }

        if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->with('error', 'Por favor, seleccione una fecha futura o actual.');
        }

        // Validar cliente activo
        $clienteModel = new ClienteModel();
        $clienteData  = $clienteModel->find($cliente);
        if (!$clienteData || $clienteData['estado_cliente'] !== 'activo') {
            return redirect()->back()->with('error', 'Cliente inválido o inactivo.');
        }

        // Validar recinto habilitado
        $recintoModel = new RecintoModel();
        $recintoData  = $recintoModel->find($recinto);
        if (!$recintoData || $recintoData['habilitado'] != 1) {
            return redirect()->back()->with('error', 'Recinto inválido o no habilitado.');
        }

        // Validar hora dentro del rango y formato correcto
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
            return redirect()->back()->with('error', 'Formato de hora inválido.');
        }

        $horaInt = (int)substr($hora, 0, 2);
        if ($horaInt < 8 || $horaInt > 22) {
            return redirect()->back()->with('error', 'Hora fuera del rango permitido.');
        }

        // Validar que la hora no esté ocupada
        $ocupada = $model->where('fecha_reserva', $fecha)
                        ->where('nro_recinto', $recinto)
                        ->where('hora_reserva', $hora)
                        ->first();
        if ($ocupada) {
            return redirect()->back()->with('error', 'La hora seleccionada ya está ocupada.');
        }

        // Calcular monto: tarifa del recinto × 1 hora
        $tarifa = $recintoData['tarifa_hora'] ?? 0;

        $data = [
            'fecha_reserva' => $fecha,
            'id_cliente'    => $cliente,
            'nro_recinto'   => $recinto,
            'id_Usuario'    => $usuario,  
            'hora_reserva'  => $hora,
            'monto_reserva' => $tarifa,
            'pagado'        => 0,
            'estado'        => 'pendiente'
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('reserva/crear'))->with('success', 'Reserva creada correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al guardar la reserva.');
        }
    }




}
