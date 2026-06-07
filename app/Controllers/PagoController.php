<?php

namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\PagoModel;
use App\Models\MedioPagoModel;

class PagoController extends BaseController
{
    public function listar()
    {
        $pagoModel = new PagoModel();
        $data['pagos'] = $pagoModel->listarPagos();

        return view('plantillas/head')
            . view('contenido/crud_pago/listar_pagos', $data)
            . view('plantillas/footer');
    }

    public function alta($idReserva)
    {
        $reservaModel = new ReservaModel();
        $medioModel   = new MedioPagoModel();

        $reserva = $reservaModel->find($idReserva);
        if (!$reserva) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        $data['reserva'] = $reserva;
        $data['medios']  = $medioModel->findAll();

        return view('plantillas/head')
            . view('contenido/crud_pago/alta_pago', $data)
            . view('plantillas/footer');
    }

    public function guardar()
    {
        $idReserva   = (int) $this->request->getPost('id_reserva');
        $monto       = (float) $this->request->getPost('monto_total');
        $idMedioPago = (int) $this->request->getPost('id_medio_pago');
        $idUsuario   = (int) session()->get('id_usuario');

        $pagoModel = new PagoModel();
        $resultado = $pagoModel->altaPago($idReserva, $monto, $idMedioPago, $idUsuario);

        if (!$resultado['ok']) {
            return redirect()->back()->with('error', $resultado['error']);
        }

        return redirect()->to('/reserva/listar')->with('pago_confirmado', true);
    }
}
