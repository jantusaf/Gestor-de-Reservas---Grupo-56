<?php

namespace App\Controllers;

use App\Models\RecintoModel;
use CodeIgniter\Controller;

class RecintoController extends Controller
{
    private function obtenerTipos()
    {
        return \Config\Database::connect()
            ->table('tipo_recinto')
            ->get()
            ->getResultArray();
    }

    private function validarRecinto(bool $editando = false)
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
        return view('plantillas/head')
            . view('contenido/crud_recinto/alta_recinto', ['tipos' => $this->obtenerTipos()])
            . view('plantillas/footer');
    }

    public function guardarRecinto()
    {
        if (!$this->validarRecinto()) {
            return view('plantillas/head')
                . view('contenido/crud_recinto/alta_recinto', [
                    'tipos'      => $this->obtenerTipos(),
                    'validation' => $this->validator,
                ])
                . view('plantillas/footer');
        }

        $model = new RecintoModel();
        $model->insert([
            'tarifa'          => number_format((float)$this->request->getPost('Tarifa_por_Hora'), 2, '.', ''),
            'descripcion'     => $this->request->getPost('Descripcion'),
            'id_tipo_recinto' => $this->request->getPost('Tipo_de_Recinto'),
            'estado_recinto'  => 'activo',
        ]);


        return redirect()->to('/recinto/listar')->with('success', 'Recinto agregado correctamente.');
    }

    public function listarRecintos()
    {
        $db = \Config\Database::connect();
        $data['recintos'] = $db->table('recinto')
            ->select('recinto.*, tipo_recinto.nombre_tipo_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->orderBy('recinto.estado_recinto', 'ASC')
            ->get()
            ->getResultArray();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    public function listarRecintosHabilitados()
    {
        $db = \Config\Database::connect();
        $data['recintos'] = $db->table('recinto')
            ->select('recinto.*, tipo_recinto.nombre_tipo_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->where('recinto.estado_recinto', 'activo')
            ->orderBy('tipo_recinto.nombre_tipo_recinto', 'ASC')
            ->get()
            ->getResultArray();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    public function deshabilitarRecinto($id)
    {
        $model = new RecintoModel();
        if (!$model->find($id)) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }
        $model->update($id, ['estado_recinto' => 'inactivo']);
        return redirect()->to('/recinto/listar')->with('success', 'Recinto deshabilitado.');
    }

    public function habilitarRecinto($id)
    {
        $model = new RecintoModel();
        if (!$model->find($id)) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }
        $model->update($id, ['estado_recinto' => 'activo']);
        return redirect()->to('/recinto/listar')->with('success', 'Recinto habilitado.');
    }

    public function editarRecinto($id = null)
    {
        $model   = new RecintoModel();
        $recinto = $model->find($id);

        if (!$recinto) {
            return redirect()->to('/recinto/listar')->with('error', 'Recinto no encontrado.');
        }

        return view('plantillas/head')
            . view('contenido/crud_recinto/editar_recinto', [
                'recinto' => $recinto,
                'tipos'   => $this->obtenerTipos(),
            ])
            . view('plantillas/footer');
    }

    public function actualizarRecinto($id = null)
    {
        if (!$this->validarRecinto(true)) {
            return redirect()->back()->with('error', $this->validator->listErrors());
        }

        $model = new RecintoModel();
        $model->update($id, [
            'tarifa'          => number_format((float)$this->request->getPost('Tarifa_por_Hora'), 2, '.', ''),
            'descripcion'     => $this->request->getPost('Descripcion'),
            'id_tipo_recinto' => $this->request->getPost('Tipo_de_Recinto'),
            'estado_recinto'  => $this->request->getPost('estado_recinto') ?? 'activo',
        ]);

        return redirect()->to('/recinto/listar')->with('success', 'Recinto actualizado correctamente.');
    }
}
