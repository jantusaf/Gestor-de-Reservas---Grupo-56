<?php
namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table      = 'cliente';
    protected $primaryKey = 'id_cliente';

    protected $allowedFields = [
        'email',
        'fecha_alta',
        'estado_cliente',
        'id_persona'
    ];

    public function altaCliente(string $email, int $idPersona): int
    {
        $this->insert([
            'email'          => $email,
            'fecha_alta'     => date('Y-m-d'),
            'estado_cliente' => 'activo',
            'id_persona'     => $idPersona,
        ]);
        return $this->getInsertID();
    }

    public function listarClientes(string $dni = ''): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('cliente')
            ->select('cliente.id_cliente, cliente.email, cliente.fecha_alta, cliente.estado_cliente, cliente.id_persona,
                      persona.nombre, persona.apellido, persona.dni, persona.telefono, persona.calle, persona.altura, persona.fecha_nacimiento')
            ->join('persona', 'persona.id_persona = cliente.id_persona')
            ->orderBy('cliente.estado_cliente', 'ASC')
            ->orderBy('persona.apellido', 'ASC');

        if ($dni !== '') {
            $builder->like('persona.dni', $dni, 'after');
        }

        return $builder->get()->getResultArray();
    }

    public function listarClientesActivos(): array
    {
        $db = \Config\Database::connect();
        return $db->table('cliente')
            ->select('cliente.id_cliente, cliente.email, cliente.fecha_alta, cliente.estado_cliente, cliente.id_persona,
                      persona.nombre, persona.apellido, persona.dni, persona.telefono, persona.calle, persona.altura, persona.fecha_nacimiento')
            ->join('persona', 'persona.id_persona = cliente.id_persona')
            ->where('cliente.estado_cliente', 'activo')
            ->orderBy('persona.apellido', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function actualizarCliente(int $id, string $email, string $estadoCliente): void
    {
        $this->update($id, [
            'email'          => $email,
            'estado_cliente' => $estadoCliente,
        ]);
    }

    public function deshabilitar(int $id): void
    {
        $this->update($id, ['estado_cliente' => 'inactivo']);
    }

    public function habilitar(int $id): void
    {
        $this->update($id, ['estado_cliente' => 'activo']);
    }
}
