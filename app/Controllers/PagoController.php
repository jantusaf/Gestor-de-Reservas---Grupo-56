<?php

namespace App\Controllers;

use App\Models\ReservaModel;
use App\Models\PagoModel;
use App\Models\MedioPagoModel;

class PagoController extends BaseController
{
    // Listar todos los pagos
    public function listar()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('pago')
            ->select('pago.id_reserva, pago.fecha_pago, pago.monto_total,
                    persona.nombre, 
                    persona.apellido, 
                    medio_pago.nombre_medio_pago, 
                    usuario.nombre_usuario')
            ->join('reserva', 'reserva.id_reserva = pago.id_reserva')
            ->join('cliente', 'cliente.id_cliente = reserva.id_cliente')
            ->join('persona', 'persona.id_persona = cliente.id_persona')
            ->join('medio_pago', 'medio_pago.id_medio_pago = pago.id_medio_pago')
            ->join('usuario', 'usuario.id_usuario = pago.id_usuario');

        $data['pagos'] = $builder->get()->getResultArray();

        return view('plantillas/head')
            . view('contenido/crud_pago/listar_pagos', $data)
            . view('plantillas/footer');
    }






    // Mostrar formulario de alta de pago
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



    // Guardar pago y validar contra el total
public function guardar()
{
    $idReserva   = $this->request->getPost('id_reserva');
    $monto       = $this->request->getPost('monto_total');
    $idMedioPago = $this->request->getPost('id_medio_pago');

    $reservaModel = new ReservaModel();
    $pagoModel    = new PagoModel();

    $reserva = $reservaModel->find($idReserva);

    // Validar que el monto sea exactamente igual al de la reserva
    if ($monto != $reserva['monto']) {
        return redirect()->back()->with('error', 'El monto debe ser exactamente igual al de la reserva.');
    }

    // Insertar pago único
    $pagoModel->insert([
        'id_reserva'    => $idReserva,
        'monto_total'   => $monto,
        'id_medio_pago' => $idMedioPago,
        'fecha_pago'    => date('Y-m-d'),
        'id_usuario'    => session()->get('id_usuario')
    ]);

    // Actualizar estado de la reserva
    $reservaModel->update($idReserva, [
        'estado_reserva' => 'confirmada',
        'estado_pago'    => 'pagada'
    ]);

    return redirect()->to('/reserva/listar')->with('success', 'Pago registrado correctamente.');
}


}
