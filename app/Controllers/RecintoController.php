<?php

namespace App\Controllers;

use App\Models\RecintoModel;
use CodeIgniter\Controller;

class RecintoController extends Controller
{
    public function formularioAlta()
    {
        $recintoModel = new RecintoModel();

        return view('plantillas/head')
            . view('contenido/crud_recinto/alta_recinto', $recintoModel->datosFormularioAlta())
            . view('plantillas/footer');
    }

    public function guardarRecinto()
    {
        $recintoModel = new RecintoModel();

        $resultado = $recintoModel->altaRecinto(
            $this->request->getPost('tarifa')          ?? '',
            $this->request->getPost('descripcion')     ?? '',
            (int)   $this->request->getPost('id_tipo_recinto'),
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/recinto/listar')->with('success', 'Recinto agregado correctamente.');
    }

    public function listarRecintos()
    {
        $recintoModel     = new RecintoModel();
        $data['recintos'] = $recintoModel->listarRecintos();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    public function listarRecintosHabilitados()
    {
        $recintoModel     = new RecintoModel();
        $data['recintos'] = $recintoModel->listarRecintosActivos();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    public function deshabilitarRecinto($id)
    {
        $recintoModel = new RecintoModel();
        $resultado    = $recintoModel->deshabilitar((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/recinto/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/recinto/listar')->with('success', $resultado['mensaje']);
    }

    public function habilitarRecinto($id)
    {
        $recintoModel = new RecintoModel();
        $resultado    = $recintoModel->habilitar((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/recinto/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/recinto/listar')->with('success', $resultado['mensaje']);
    }

    public function formularioEditar($id = null)
    {
        $recintoModel = new RecintoModel();
        $data         = $recintoModel->datosFormularioEditar((int) $id);

        if (!$data) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }

        return view('plantillas/head')
            . view('contenido/crud_recinto/editar_recinto', $data)
            . view('plantillas/footer');
    }

    public function actualizarRecinto($id = null)
    {
        $recintoModel = new RecintoModel();

        $resultado = $recintoModel->actualizarRecinto(
            (int)   $id,
            $this->request->getPost('tarifa')          ?? '',
            $this->request->getPost('descripcion')     ?? '',
            (int)   $this->request->getPost('id_tipo_recinto'),
            $this->request->getPost('estado_recinto')  ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/recinto/listar')->with('success', 'Recinto actualizado correctamente.');
    }
}
