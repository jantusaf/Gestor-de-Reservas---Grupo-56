<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data['title'] = 'Principal';
        $vista = session()->get('id_tipo') == 1
            ? 'contenido/principal_admin'
            : 'contenido/principal';

        return view('plantillas/head', $data)
            . view($vista, $data)
            . view('plantillas/footer', $data);
    }
}
