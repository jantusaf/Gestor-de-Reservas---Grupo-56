<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data['title'] = 'Principal';
        return view('plantillas/head', $data)
             . view('contenido/principal', $data)
            . view('plantillas/footer', $data);
    }
}
