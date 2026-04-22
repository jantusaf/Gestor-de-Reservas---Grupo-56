<?php

namespace App\Controllers;

use App\Models\RecintoModel;
use CodeIgniter\Controller;

class RecintoController extends Controller
{
    // LISTAR RECINTOS
    public function index()
    {
        $model = new RecintoModel();
        // Solo mostrar habilitados
        $data['recintos'] = $model->where('habilitado', 1)->findAll();

        return view('plantillas/head')
            . view('contenido/crud_recinto/listar_recinto', $data)
            . view('plantillas/footer');
    }


    // FORMULARIO DE CREACIÓN
    public function crear()
    {
        $db = \Config\Database::connect();
        $tipos = $db->table('tipo_recinto')->get()->getResultArray();

        $data['tipos'] = $tipos;

        return view('plantillas/head')
            . view('contenido/crud_recinto/alta_recinto', $data)
            . view('plantillas/footer');
    }

    
    
    public function listar_recintos_habilitados()
    {
        $model = new RecintoModel();

        $tarifa = $this->request->getPost('tarifa_hora');
        $tipo   = $this->request->getPost('id_tipo_recinto');

        // Validar que no haya campos vacíos
        if (empty($tarifa) || empty($tipo)) {
            return redirect()->back()->with('error', 'Por favor, complete los campos correspondientes');
        }

        // Validar que tarifa sea numérica
        if (!is_numeric($tarifa)) {
            return redirect()->back()->with('error', 'Revise el formato de la tarifa');
        }

        // Formatear tarifa
        $tarifa = number_format((float)$tarifa, 2, '.', '');

        $data = [
            'tarifa_hora'     => $tarifa,
            'id_tipo_recinto' => $tipo,
            'habilitado'      => 1 // siempre habilitado al crear
        ];

        if ($model->insert($data)) {
            return redirect()->back()->with('success', 'Recinto creado correctamente');
        } else {
            return redirect()->back()->with('error', 'Error al crear el recinto');
        }
    }




    // BAJA LÓGICA DE RECINTO
    public function eliminar($id = null)
    {
        $model = new RecintoModel();

        // en vez de delete, actualizamos habilitado = 0
        if ($model->update($id, ['habilitado' => 0])) {
            return redirect()->to(base_url('recinto'))->with('success', 'Recinto deshabilitado correctamente');
        } else {
            return redirect()->back()->with('error', 'Error al deshabilitar el recinto');
        }
    }

    // OPCIONAL: ACTIVAR RECINTO
    public function activar($id = null)
    {
        $model = new RecintoModel();

        if ($model->update($id, ['habilitado' => 1])) {
            // Redirige a la lista de eliminados, refrescando la página
            return redirect()->to(base_url('recintos-eliminados'))
                            ->with('success', 'Recinto habilitado nuevamente');
        } else {
            return redirect()->back()->with('error', 'Error al habilitar el recinto');
        }
    }


    public function eliminados()
    {
        $model = new RecintoModel();
        $data['recintos'] = $model->where('habilitado', 0)->findAll();

        return view('plantillas/head')
            . view('contenido/crud_recinto/recinto_eliminado', $data)
            . view('plantillas/footer');
    }

        // EDITAR RECINTO
    public function editar($id = null)
    {
        $model = new RecintoModel();
        $db = \Config\Database::connect();

        $recinto = $model->find($id);
        $tipos   = $db->table('tipo_recinto')->get()->getResultArray();

        if (!$recinto) {
            return redirect()->to(base_url('recinto'))->with('error', 'Recinto no encontrado');
        }

        $data['recinto'] = $recinto;
        $data['tipos']   = $tipos;

        return view('plantillas/head')
            . view('contenido/crud_recinto/editar_recinto', $data)
            . view('plantillas/footer');
    }

    public function update($id = null)
    {
        $model = new RecintoModel();

        $tarifa = $this->request->getPost('tarifa_hora');

        if (!is_numeric($tarifa)) {
            return redirect()->back()->with('error', 'Revise el formato de la tarifa');
        }

        $tarifa = number_format((float)$tarifa, 2, '.', '');

        $data = [
            'tarifa_hora'     => $tarifa,
            'id_tipo_recinto' => $this->request->getPost('id_tipo_recinto'),
            'habilitado'      => $this->request->getPost('habilitado') ?? 1
        ];

        if ($model->update($id, $data)) {
            return redirect()->to(base_url('recinto'))->with('success', 'Recinto actualizado correctamente');
        } else {
            return redirect()->back()->with('error', 'Error al actualizar el recinto');
        }
    }


}
