<?php
namespace App\Models;

use CodeIgniter\Model;

class RecintoModel extends Model
{
    protected $table      = 'recinto';
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

    public function altaRecinto(float $tarifa, string $descripcion, int $idTipoRecinto): void
    {
        $this->insert([
            'tarifa'          => number_format($tarifa, 2, '.', ''),
            'descripcion'     => $descripcion,
            'id_tipo_recinto' => $idTipoRecinto,
            'estado_recinto'  => 'activo',
        ]);
    }

    public function actualizarRecinto(int $id, float $tarifa, string $descripcion, int $idTipoRecinto, string $estadoRecinto): void
    {
        $this->update($id, [
            'tarifa'          => number_format($tarifa, 2, '.', ''),
            'descripcion'     => $descripcion,
            'id_tipo_recinto' => $idTipoRecinto,
            'estado_recinto'  => $estadoRecinto,
        ]);
    }

    public function deshabilitar(int $id): void
    {
        $this->update($id, ['estado_recinto' => 'inactivo']);
    }

    public function habilitar(int $id): void
    {
        $this->update($id, ['estado_recinto' => 'activo']);
    }
}
