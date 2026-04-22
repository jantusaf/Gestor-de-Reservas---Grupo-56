<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = 'usuario';       // nombre de la tabla
    protected $primaryKey = 'id_Usuario';    // clave primaria

    protected $allowedFields = [
        'DNI_Usuario',
        'foto_usuario',
        'Nombre_Usuario',
        'Apellido_Usuario',
        'Fecha_Ingreso',
        'Fecha_Nacimiento',
        'pass',
        'telefono',
        'Id_Tipo',   
        'Sexo_Usuario',
        'estado_usuario'
    ];

}
