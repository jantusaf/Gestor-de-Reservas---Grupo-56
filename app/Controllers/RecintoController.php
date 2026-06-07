<?php

namespace App\Controllers;

use App\Models\RecintoModel;
use CodeIgniter\Controller;

class RecintoController extends Controller
{
    private function validarRecinto(bool $editando = false): bool
    {
        $rules = [
            'Tarifa_por_Hora' => 'required|numeric',
            'Tipo_de_Recinto' => 'required',
            'Descripcion'     => 'required|min_length[3]|max_length[50]',
        ];

        if ($editando) {
            $rules['estado_recinto'] = 'required|in_list[activo,inactivo]';
        }

        return $this->validate($rules);
    }

    public function altaRecinto()
    {
        $recintoModel = new RecintoModel();

        return view('plantillas/head')
            . view('contenido/crud_recinto/alta_recinto', ['tipos' => $recintoModel->listarTipos()])
            . view('plantillas/footer');
    }

    public function guardarRecinto()
    {
        $recintoModel = new RecintoModel();

        if (!$this->validarRecinto()) {
            return view('plantillas/head')
                . view('contenido/crud_recinto/alta_recinto', [
                    'tipos'      => $recintoModel->listarTipos(),
                    'validation' => $this->validator,
                ])
                . view('plantillas/footer');
        }

        $recintoModel->altaRecinto(
            (float) $this->request->getPost('Tarifa_por_Hora'),
            $this->request->getPost('Descripcion'),
            (int) $this->request->getPost('Tipo_de_Recinto'),
        );

        return redirect()->to('/recinto/listar')->with('success', 'Recinto agregado correctamente.');
    }

    public function listarRecintos()
    {
        $recintoModel = new RecintoModel();
        $data['recintos'] = $recintoModel->listarRecintos();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    public function listarRecintosHabilitados()
    {
        $recintoModel = new RecintoModel();
        $data['recintos'] = $recintoModel->listarRecintosActivos();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    public function deshabilitarRecinto($id)
    {
        $recintoModel = new RecintoModel();
        if (!$recintoModel->find($id)) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }
        $recintoModel->deshabilitar($id);
        return redirect()->to('/recinto/listar')->with('success', 'Recinto deshabilitado.');
    }

    public function habilitarRecinto($id)
    {
        $recintoModel = new RecintoModel();
        if (!$recintoModel->find($id)) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }
        $recintoModel->habilitar($id);
        return redirect()->to('/recinto/listar')->with('success', 'Recinto habilitado.');
    }

    public function editarRecinto($id = null)
    {
        $recintoModel = new RecintoModel();
        $recinto = $recintoModel->find($id);

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

    public function actualizarRecinto($id = null)
    {
        if (!$this->validarRecinto(true)) {
            return redirect()->back()->with('error', $this->validator->listErrors());
        }

        $recintoModel = new RecintoModel();
        $recintoModel->actualizarRecinto(
            (int) $id,
            (float) $this->request->getPost('Tarifa_por_Hora'),
            $this->request->getPost('Descripcion'),
            (int) $this->request->getPost('Tipo_de_Recinto'),
            $this->request->getPost('estado_recinto') ?? 'activo',
        );

        return redirect()->to('/recinto/listar')->with('success', 'Recinto actualizado correctamente.');
    }
}
