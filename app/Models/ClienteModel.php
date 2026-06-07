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

    public function registrarCliente(string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura, string $email): array
    {
        $personaResult = (new PersonaModel())->altaPersona($dni, $nombre, $apellido, $fechaNacimiento, $telefono, $calle, $altura);
        if (!$personaResult['ok']) {
            return $personaResult;
        }

        return $this->altaCliente($email, $personaResult['id']);
    }

    public function datosFormularioEditar(int $id): ?array
    {
        $cliente = $this->find($id);
        if (!$cliente) {
            return null;
        }

        return [
            'cliente' => $cliente,
            'persona' => (new PersonaModel())->find($cliente['id_persona']),
        ];
    }

    public function modificarCliente(int $id, string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura, string $email, string $estadoCliente): array
    {
        $cliente = $this->find($id);
        if (!$cliente) {
            return ['ok' => false, 'errores' => ['id' => 'Cliente no encontrado.']];
        }

        $personaResult = (new PersonaModel())->actualizarPersona(
            $cliente['id_persona'],
            $dni, $nombre, $apellido, $fechaNacimiento, $telefono, $calle, $altura,
        );
        if (!$personaResult['ok']) {
            return $personaResult;
        }

        return $this->actualizarCliente($id, $email, $estadoCliente);
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

    public function deshabilitar(int $id): array
    {
        if (!$this->find($id)) {
            return ['ok' => false, 'mensaje' => 'Cliente no encontrado.'];
        }

        $this->update($id, ['estado_cliente' => 'inactivo']);
        return ['ok' => true, 'mensaje' => 'Cliente deshabilitado.'];
    }

    public function habilitar(int $id): array
    {
        if (!$this->find($id)) {
            return ['ok' => false, 'mensaje' => 'Cliente no encontrado.'];
        }

        $this->update($id, ['estado_cliente' => 'activo']);
        return ['ok' => true, 'mensaje' => 'Cliente habilitado.'];
    }
}
