<?php

namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\ClienteModel;
use App\Models\RecintoModel;
use CodeIgniter\Controller;

class ReservaController extends BaseController
{
    // FORMULARIO DE CREACIÓN DE RESERVA
    public function crear()
    {
         dd(session()->get());
        $clienteModel = new ClienteModel();
        $recintoModel = new RecintoModel();

        $data['clientes'] = $clienteModel->where('estado_cliente', 'activo')->findAll();
        $data['recintos'] = $recintoModel->where('habilitado', 1)->findAll();

        return view('plantillas/head')
            . view('contenido/crud_reserva/alta_reserva', $data)
            . view('plantillas/footer');
    }

    // HORAS DISPONIBLES SEGÚN FECHA Y RECINTO
    public function ActualizarHorasDisponibles()
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


    // boton de agregar reserva
   public function AgregarReserva()
{

    $model = new ReservaModel();

    $fecha   = $this->request->getPost('fecha_reserva');
    $cliente = $this->request->getPost('id_cliente');
    $recinto = $this->request->getPost('nro_recinto');
    $hora    = $this->request->getPost('hora_reserva');
    

   
    $resultado = $this->validarCamposReserva($fecha, $cliente, $recinto, $hora, $usuario);

    if (!$resultado['ok']) {
        return redirect()->back()->with('error', $resultado['mensaje']);
    }

   
    $recintoData = $resultado['recinto'];

    // calcular monto
    $tarifa = $recintoData['tarifa_hora'] ?? 0;

    $data = [
        'fecha_reserva' => $fecha,
        'id_cliente'    => $cliente,
        'nro_recinto'   => $recinto,
       
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

private function validarCamposReserva($fecha, $cliente, $recinto, $hora, $usuario)
{
    $reservaModel = new ReservaModel();
    $clienteModel = new ClienteModel();
    $recintoModel = new RecintoModel();

    // campos obligatorios
    if (empty($fecha)) return ['ok' => false, 'mensaje' => 'Falta la fecha'];
if (empty($cliente)) return ['ok' => false, 'mensaje' => 'Falta el cliente'];
if (empty($recinto)) return ['ok' => false, 'mensaje' => 'Falta el recinto'];
if (empty($hora)) return ['ok' => false, 'mensaje' => 'Falta la hora'];


    //fecha válida
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
        return ['ok' => false, 'mensaje' => 'Seleccione una fecha válida.'];
    }

    //cliente activo
    $clienteData = $clienteModel->find($cliente);
    if (!$clienteData || $clienteData['estado_cliente'] !== 'activo') {
        return ['ok' => false, 'mensaje' => 'Cliente inválido o inactivo.'];
    }

    //recinto habilitado
    $recintoData = $recintoModel->find($recinto);
    if (!$recintoData || $recintoData['habilitado'] != 1) {
        return ['ok' => false, 'mensaje' => 'Recinto inválido o no habilitado.'];
    }

    // formato de hora
    if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
        return ['ok' => false, 'mensaje' => 'Formato de hora inválido.'];
    }

    // rango de hora
    $horaInt = (int)substr($hora, 0, 2);
    if ($horaInt < 8 || $horaInt > 22) {
        return ['ok' => false, 'mensaje' => 'Hora fuera del rango permitido.'];
    }

  
    $ocupada = $reservaModel->where('fecha_reserva', $fecha)
        ->where('nro_recinto', $recinto)
        ->where('hora_reserva', $hora)
        ->first();

    if ($ocupada) {
        return ['ok' => false, 'mensaje' => 'La hora seleccionada ya está ocupada.'];
    }

    return [
        'ok' => true,
        'recinto' => $recintoData
    ];
}

}
