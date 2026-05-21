<?php

namespace App\Controllers;

use App\Models\RecintoModel;
use CodeIgniter\Controller;

class RecintoController extends Controller
{
    // Vista de alta
    public function muestra_vista_alta_recinto()
    {
        $tipos = $this->listar_tipo_recinto();
        $data['tipos'] = $tipos;

        return view('plantillas/head')
            . view('contenido/crud_recinto/alta_recinto', $data)
            . view('plantillas/footer');
    }

    // Listar tipos
    public function listar_tipo_recinto()
    {
        $db = \Config\Database::connect();
        return $db->table('tipo_recinto')->get()->getResultArray();
    }

    // Validar campos
    public function validar_campos_recinto()
    {
        $rules = [
            'tarifa' => 'required|numeric',
            'id_tipo_recinto' => 'required',
            'descripcion' => 'required|min_length[3]|max_length[50]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->listErrors());
        }

        return redirect()->back()->with('success', 'Validación correcta');
    }

    // Insertar recinto
    public function agregar_recinto()
    {
        $tarifa      = number_format((float)$this->request->getPost('tarifa'), 2, '.', '');
        $tipo        = $this->request->getPost('id_tipo_recinto');
        $descripcion = $this->request->getPost('descripcion');

        $model = new RecintoModel();
        $data = [
            'tarifa'          => $tarifa,
            'descripcion'     => $descripcion,
            'id_tipo_recinto' => $tipo,
            'estado_recinto'  => 'activo'
        ];

        if ($model->insert($data)) {
            return redirect()->back()->with('success', 'Recinto agregado correctamente');
        }
        return redirect()->back()->with('error', 'Error al agregar el recinto');
    }

    // Alta (validación + inserción)
    public function alta()
    {
        $validacion = $this->validar_campos_recinto();

        if (session()->getFlashdata('error')) {
            return $validacion;
        }

        return $this->agregar_recinto();
    }

    // Listar activos
    public function listar_recintos()
    {
        $model = new RecintoModel();
        $data['recintos'] = $model->where('estado_recinto', 'activo')->findAll();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }

    // Eliminar (baja lógica)
    public function eliminar_recinto($id = null)
    {
        $model = new RecintoModel();
        if ($model->update($id, ['estado_recinto' => 'inactivo'])) {
            return redirect()->back()->with('success', 'Recinto deshabilitado');
        }
        return redirect()->back()->with('error', 'Error al deshabilitar');
    }

    // Activar
    public function activar_recinto($id = null)
    {
        $model = new RecintoModel();
        if ($model->update($id, ['estado_recinto' => 'activo'])) {
            return redirect()->to(base_url('recinto'))->with('success', 'Recinto habilitado nuevamente');
        }
        return redirect()->back()->with('error', 'Error al habilitar');
    }

    // Listar inactivos
    public function listar_recintos_inactivos()
    {
        $model = new RecintoModel();
        $data['recintos'] = $model->where('estado_recinto', 'inactivo')->findAll();

        return view('plantillas/head')
            . view('contenido/crud_recinto/recinto_eliminado', $data)
            . view('plantillas/footer');
    }

    // Formulario edición
    public function mostrar_formulario_editar_recinto($id = null)
    {
        $model = new RecintoModel();
        $recinto = $model->find($id);
        $tipos   = $this->listar_tipo_recinto();

        if (!$recinto) {
            return redirect()->to(base_url('recinto'))->with('error', 'Recinto no encontrado');
        }

        $data['recinto'] = $recinto;
        $data['tipos']   = $tipos;

        return view('plantillas/head')
            . view('contenido/crud_recinto/editar_recinto', $data)
            . view('plantillas/footer');
    }

    // Actualizar
    public function actualizar_recinto($id = null)
    {
        $rules = [
            'tarifa' => 'required|numeric',
            'id_tipo_recinto' => 'required',
            'descripcion' => 'required|min_length[3]|max_length[50]',
            'estado_recinto' => 'in_list[activo,inactivo]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->listErrors());
        }

        $tarifa      = number_format((float)$this->request->getPost('tarifa'), 2, '.', '');
        $descripcion = $this->request->getPost('descripcion');

        $data = [
            'tarifa'          => $tarifa,
            'descripcion'     => $descripcion,
            'id_tipo_recinto' => $this->request->getPost('id_tipo_recinto'),
            'estado_recinto'  => $this->request->getPost('estado_recinto') ?? 'activo'
        ];

        $model = new RecintoModel();
        if ($model->update($id, $data)) {
            return redirect()->to(base_url('recinto'))->with('success', 'Recinto actualizado');
        }
        return redirect()->back()->with('error', 'Error al actualizar');
    }
}
