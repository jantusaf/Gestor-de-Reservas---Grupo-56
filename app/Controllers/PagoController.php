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

        $idReserva = (int) $this->request->getPost('id_reserva');
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
