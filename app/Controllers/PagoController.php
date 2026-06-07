<?php

namespace App\Controllers;

use App\Models\PagoModel;

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
        $pagoModel = new PagoModel();
        $data      = $pagoModel->datosFormularioAlta((int) $idReserva);

        if (!$data) {
            return redirect()->to('/reserva/listar')->with('error', 'Reserva no encontrada.');
        }

        return view('plantillas/head')
            . view('contenido/crud_pago/alta_pago', $data)
            . view('plantillas/footer');
    }

    public function guardar()
    {
        $pagoModel = new PagoModel();

        $resultado = $pagoModel->altaPago(
            (int)   $this->request->getPost('id_reserva'),
            (float) $this->request->getPost('monto_total'),
            (int)   $this->request->getPost('id_medio_pago'),
            (int)   session()->get('id_usuario'),
        );

        if (!$resultado['ok']) {
            return redirect()->back()->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/reserva/listar')->with('pago_confirmado', true);
    }
}
