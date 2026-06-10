<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    /**
     * Permite el acceso solo a usuarios administradores (id_tipo_usuario = 1).
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Debés iniciar sesión.');
        }

        if ((int) session()->get('id_tipo') !== 1) {
            return redirect()->to('/contenido/principal')
                ->with('error', 'No tenés permisos para acceder a esa sección.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción posterior.
    }
}
