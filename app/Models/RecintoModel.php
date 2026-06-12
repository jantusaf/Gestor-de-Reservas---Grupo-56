<?php
namespace App\Models;

use CodeIgniter\Model;

class RecintoModel extends Model
{
    protected $table = 'recinto';
    protected $primaryKey = 'id_recinto';

    protected $allowedFields = [
        'tarifa',
        'estado_recinto',
        'descripcion',
        'id_tipo_recinto'
    ];

    public function listarTipos(): array
    {
        return \Config\Database::connect()
            ->table('tipo_recinto')
            ->get()
            ->getResultArray();
    }
//se podria aplicar procedimientos almcacenados para las consultas
    public function listarRecintos(): array
    {
        $db = \Config\Database::connect();
        return $db->table('recinto')
            ->select('recinto.*, tipo_recinto.nombre_tipo_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->orderBy('recinto.estado_recinto', 'ASC')
            ->get()
            ->getResultArray();
    }
//se podria aplicar procedimientos almcacenados para las consultas
    public function listarRecintosActivos(): array
    {
        $db = \Config\Database::connect();
        return $db->table('recinto')
            ->select('recinto.*, tipo_recinto.nombre_tipo_recinto')
            ->join('tipo_recinto', 'tipo_recinto.id_tipo_recinto = recinto.id_tipo_recinto')
            ->where('recinto.estado_recinto', 'activo')
            ->orderBy('tipo_recinto.nombre_tipo_recinto', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function altaRecinto(string $tarifa, string $descripcion, int $idTipoRecinto): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'tarifa'=> ['label' => 'Tarifa por hora',  'rules' => 'required|numeric|greater_than[0]',
                                   'errors' => ['required' => 'El campo Tarifa por hora es obligatorio.', 'numeric' => 'La tarifa debe ser un número.', 'greater_than' => 'La tarifa debe ser mayor a 0.']],
            'descripcion'=> ['label' => 'Descripción',      'rules' => 'required|min_length[3]|max_length[50]'],
            'id_tipo_recinto' => ['label' => 'Tipo de recinto', 'rules' => 'required|integer|greater_than[0]',
                                   'errors' => ['required' => 'El campo Tipo de recinto es obligatorio.', 'integer' => 'El campo Tipo de recinto es inválido.', 'greater_than' => 'El campo Tipo de recinto es obligatorio.']],
        ])->run([
            'tarifa'=> $tarifa,
            'descripcion'=> $descripcion,
            'id_tipo_recinto'=> $idTipoRecinto,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->insert([
            'tarifa'=> number_format((float) $tarifa, 2, '.', ''),
            'descripcion'=> $descripcion,
            'id_tipo_recinto'=> $idTipoRecinto,
            'estado_recinto'=> 'activo',
        ]);

        return ['ok' => true];
    }

    public function modificarRecinto(int $id, string $tarifa, string $descripcion, int $idTipoRecinto, string $estadoRecinto): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'tarifa' => ['label' => 'Tarifa por hora', 'rules' => 'required|numeric|greater_than[0]',
              'errors' => ['required' => 'El campo Tarifa por hora es obligatorio.', 'numeric' => 'La tarifa debe ser un número.', 'greater_than' => 'La tarifa debe ser mayor a 0.']],
            'descripcion'=> ['label' => 'Descripción','rules' => 'required|min_length[3]|max_length[50]'],
            'id_tipo_recinto' => ['label' => 'Tipo de recinto', 'rules' => 'required|integer|greater_than[0]',
                                   'errors' => ['required'=> 'El campo Tipo de recinto es obligatorio.', 'integer' => 'El campo Tipo de recinto es inválido.', 'greater_than' => 'El campo Tipo de recinto es obligatorio.']],
            'estado_recinto' => ['label' => 'Estado','rules' => 'required|in_list[activo,inactivo]'],
        ])->run([
            'tarifa'=> $tarifa,
            'descripcion'=> $descripcion,
            'id_tipo_recinto'=> $idTipoRecinto,
            'estado_recinto'=> $estadoRecinto,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->update($id, [
            'tarifa'=> number_format((float) $tarifa, 2, '.', ''),
            'descripcion'=> $descripcion,
            'id_tipo_recinto'=> $idTipoRecinto,
            'estado_recinto'=> $estadoRecinto,
        ]);

        return ['ok' => true];
    }

    public function deshabilitar(int $id): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query('CALL sp_deshabilitar_recinto(?)', [$id])->getRowArray();

        return $row && (int) $row['encontrado'] === 1;
    }

    public function habilitar(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }
        $this->update($id, ['estado_recinto' => 'activo']);
        return true;
    }
}
