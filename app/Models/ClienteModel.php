<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Persona;

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

    public function registrarCliente(Persona $persona, string $email): array
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        $personaResult = PersonaModel::getInstance()->altaPersona($persona);
        if (!$personaResult['ok']) {
            $db->transRollback();
            return $personaResult;
        }

        $clienteResult = $this->altaCliente($email, $personaResult['id']);
        if (!$clienteResult['ok']) {
            // Si falla el alta del cliente, se revierte la persona ya insertada
            // para no dejar registros huérfanos en la base de datos.
            $db->transRollback();
            return $clienteResult;
        }

        $db->transCommit();
        return $clienteResult;
    }

    public function modificarCliente(int $id, Persona $persona, string $email, string $estadoCliente): array
    {
        $cliente = $this->find($id);
        if (!$cliente) {
            return ['ok' => false, 'errores' => ['id' => 'Cliente no encontrado.']];
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        $personaResult = PersonaModel::getInstance()->actualizarPersona($cliente['id_persona'], $persona);
        if (!$personaResult['ok']) {
            $db->transRollback();
            return $personaResult;
        }

        $clienteResult = $this->actualizarCliente($id, $email, $estadoCliente);
        if (!$clienteResult['ok']) {
            // Si la validación del cliente falla, se revierte también el cambio
            // de persona para no dejar datos modificados a medias.
            $db->transRollback();
            return $clienteResult;
        }

        $db->transCommit();
        return $clienteResult;
    }

    public function altaCliente(string $email, int $idPersona): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'email' => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[100]|is_unique[cliente.email]'],
        ])->run(['email' => $email])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->insert([
            'email'          => $email,
            'fecha_alta'     => date('Y-m-d'),
            'estado_cliente' => 'activo',
            'id_persona'     => $idPersona,
        ]);

        return ['ok' => true, 'id' => $this->getInsertID()];
    }

    public function listarClientes(string $dni = ''): array
    {
        $db      = \Config\Database::connect();
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

    public function actualizarCliente(int $id, string $email, string $estadoCliente): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'email'          => ['label' => 'Email',  'rules' => "required|valid_email|max_length[100]|is_unique[cliente.email,id_cliente,{$id}]"],
            'estado_cliente' => ['label' => 'Estado', 'rules' => 'required|in_list[activo,inactivo]'],
        ])->run(['email' => $email, 'estado_cliente' => $estadoCliente])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->update($id, [
            'email'          => $email,
            'estado_cliente' => $estadoCliente,
        ]);

        return ['ok' => true];
    }

    public function deshabilitar(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $this->update($id, ['estado_cliente' => 'inactivo']);
        return true;
    }

    public function habilitar(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $this->update($id, ['estado_cliente' => 'activo']);
        return true;
    }
}
