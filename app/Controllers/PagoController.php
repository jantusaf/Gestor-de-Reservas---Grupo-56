<?php

namespace App\Controllers;

use App\Models\PagoModel;
use App\Models\ReservaModel;
use App\Models\MedioPagoModel;
use App\Models\HorarioModel;

class PagoController extends BaseController
{
    public function listar()
    {
        $pagoModel = new PagoModel();

        return view('plantillas/head')
            . view('contenido/crud_pago/listar_pagos', ['pagos' => $pagoModel->listarPagos()])
            . view('plantillas/footer');
    }

    public function formularioAlta($idReserva)
    {
        $reserva = (new ReservaModel())->find((int) $idReserva);

        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        // Consultar el estado actual via State antes de mostrar el formulario.
        $estadoReserva = $reserva['estado_reserva'];
        $mensajeEstado = null;
        $puedePagar    = true;

        $horario     = (new HorarioModel())->find($reserva['id_horario']);
        $inicioTurno = strtotime($reserva['fecha_reserva'] . ' ' . ($horario['horario'] ?? '23:59:59'));
        $esPasada    = time() >= $inicioTurno + 3600;

        if ($esPasada) {
            $mensajeEstado = 'No se puede pagar una reserva cuya fecha ya pasó.';
            $puedePagar    = false;
        } elseif ($estadoReserva === 'confirmada') {
            $mensajeEstado = 'Esta reserva ya fue pagada.';
            $puedePagar    = false;
        } elseif ($estadoReserva === 'cancelada') {
            $mensajeEstado = 'Esta reserva fue cancelada. Para abonar, realizá una nueva reserva.';
            $puedePagar    = false;
        }

        return view('plantillas/head')
            . view('contenido/crud_pago/alta_pago', [
                'reserva'       => $reserva,
                'medios'        => (new MedioPagoModel())->findAll(),
                'puedePagar'    => $puedePagar,
                'mensajeEstado' => $mensajeEstado,
            ])
            . view('plantillas/footer');
    }

    public function guardar()
    {
        $idReserva = (int) $this->request->getPost('id_reserva');

        $resultado = (new ReservaModel())->ejecutarAccion($idReserva, 'pagar', [
            'id_medio_pago' => (int) $this->request->getPost('id_medio_pago'),
            'id_usuario'    => (int) session()->get('id_usuario'),
        ]);

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['mensajes']);
        }

        return redirect()->to('/reserva/listar')->with('pago_confirmado', $idReserva);
    }

    public function factura($idReserva)
    {
        $pagoModel = new PagoModel();
        $factura   = $pagoModel->datosFactura((int) $idReserva);

        if (!$factura) {
            return redirect()->to('/pago/listar')->with('error', 'Factura no encontrada.');
        }

        return view('contenido/crud_pago/factura_pago', ['factura' => $factura]);
    }
}
