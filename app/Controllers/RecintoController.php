<?php

namespace App\Controllers;

use App\Models\RecintoModel;
use App\Entities\Recinto;
use CodeIgniter\Controller;

class RecintoController extends Controller
{
    public function formularioAlta()
    {
        return view('plantillas/head')
            . view('contenido/crud_recinto/alta_recinto', [
                'tipos' => (new RecintoModel())->listarTipos(),
            ])
            . view('plantillas/footer');
    }

    public function guardarRecinto()
    {
        $recintoModel = new RecintoModel();

        $recinto = new Recinto([
            'tarifa'          => $this->request->getPost('tarifa')      ?? '',
            'descripcion'     => $this->request->getPost('descripcion') ?? '',
            'id_tipo_recinto' => (int) $this->request->getPost('id_tipo_recinto'),
        ]);

        $resultado = $recintoModel->altaRecinto($recinto);

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

    public function deshabilitarRecinto($id)
    {
        $recintoModel = new RecintoModel();

        if (!$recintoModel->deshabilitar((int) $id)) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }

        return redirect()->to('/recinto/listar');
    }

    public function habilitarRecinto($id)
    {
        $recintoModel = new RecintoModel();

        if (!$recintoModel->habilitar((int) $id)) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }

        return redirect()->to('/recinto/listar');
    }

    public function formularioEditar($id = null)
    {
        $recintoModel = new RecintoModel();
        $recinto      = $recintoModel->find((int) $id);

        if (!$recinto) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }

        return view('plantillas/head')
            . view('contenido/crud_recinto/editar_recinto', [
                'recinto' => $recinto,
                'tipos'   => $recintoModel->listarTipos(),
            ])
            . view('plantillas/footer');
    }

    public function modificarRecinto($id = null)
    {
        $recintoModel = new RecintoModel();

        $recinto = new Recinto([
            'id_recinto'      => (int) $id,
            'tarifa'          => $this->request->getPost('tarifa')         ?? '',
            'descripcion'     => $this->request->getPost('descripcion')    ?? '',
            'id_tipo_recinto' => (int) $this->request->getPost('id_tipo_recinto'),
            'estado_recinto'  => $this->request->getPost('estado_recinto') ?? '',
        ]);

        $resultado = $recintoModel->modificarRecinto($recinto);

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/recinto/listar')->with('success', 'Recinto actualizado correctamente.');
    }
}
