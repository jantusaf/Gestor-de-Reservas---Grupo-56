<?php

namespace Tests\Support\Models;

use App\Models\UsuarioModel;
use App\Entities\Persona;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\UsuarioSeeder;

/**
 * Pruebas Unitarias - Modificar Usuario
 * Método: UsuarioModel::modificarUsuario(int $id, Persona $persona, string $nombreUsuario, string $estadoUsuario)
 *
 * El seeder deja:
 *   - usuario id_usuario=1 (persona dni 30123456, nombre_usuario 'jperez')  -> el que se modifica
 *   - usuario id_usuario=2 (persona dni 99999999, nombre_usuario 'agomez')  -> para duplicados
 */
class ModificarUsuarioTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed    = UsuarioSeeder::class;
    protected $refresh = true;

    private UsuarioModel $usuarioModel;

    private const ID_USUARIO = 1;

    protected function setUp(): void
    {
        parent::setUp();
        // El servicio de validación es compartido durante todo el proceso de PHPUnit
        // y acumula errores entre tests. Lo reseteamos para que cada prueba parta limpia
        // y el script sea ejecutable múltiples veces sin contaminación de estado.
        \Config\Services::validation()->reset();
        $this->usuarioModel = new UsuarioModel();
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Todos los datos válidos - Modifica el usuario
     */
    public function modificarUsuario_TodosLosDatosValidos()
    {
        $resultado = $this->usuarioModel->modificarUsuario(
            self::ID_USUARIO,
            $this->personaValida(),
            'jperez',
            'activo'
        );

        $this->assertTrue($resultado['ok']);
    }

    /**
     * @test
     * @testdox Cambiar estado a inactivo - Modifica el usuario
     */
    public function modificarUsuario_CambiarEstadoAInactivo_ModificaElUsuario()
    {
        $resultado = $this->usuarioModel->modificarUsuario(
            self::ID_USUARIO,
            $this->personaValida(),
            'jperez',
            'inactivo'
        );

        $this->assertTrue($resultado['ok']);
    }

    // ================================================================
    // VALIDACIONES DE NOMBRE DE USUARIO
    // ================================================================

    /**
     * @test
     * @testdox Nombre de usuario vacío - Retorna error
     */
    public function modificarUsuario_NombreUsuarioVacio_RetornaError()
    {
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $this->personaValida(), '', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre_usuario', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre de usuario menor a 3 caracteres - Retorna error
     */
    public function modificarUsuario_NombreUsuarioMenorA3Caracteres_RetornaError()
    {
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $this->personaValida(), 'ab', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre_usuario', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre de usuario ya registrado - Retorna error
     */
    public function modificarUsuario_NombreUsuarioDuplicado_RetornaError()
    {
        // 'agomez' ya pertenece al usuario id_usuario=2.
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $this->personaValida(), 'agomez', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre_usuario', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE ESTADO
    // ================================================================

    /**
     * @test
     * @testdox Estado inválido - Retorna error
     */
    public function modificarUsuario_EstadoInvalido_RetornaError()
    {
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $this->personaValida(), 'jperez', 'suspendido');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('estado_usuario', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE LOS DATOS DE PERSONA
    // ================================================================

    /**
     * @test
     * @testdox DNI ya registrado - Retorna error
     */
    public function modificarUsuario_DniDuplicado_RetornaError()
    {
        // '99999999' ya pertenece a la persona del usuario id_usuario=2.
        $persona   = $this->persona(['dni' => '99999999']);
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $persona, 'jperez', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre con números - Retorna error
     */
    public function modificarUsuario_NombrePersonaConNumeros_RetornaError()
    {
        $persona   = $this->persona(['nombre' => 'Juan123']);
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $persona, 'jperez', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Fecha de nacimiento futura - Retorna error
     */
    public function modificarUsuario_FechaNacimientoFutura_RetornaError()
    {
        $persona   = $this->persona(['fecha_nacimiento' => '2030-01-01']);
        $resultado = $this->usuarioModel->modificarUsuario(self::ID_USUARIO, $persona, 'jperez', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    // ================================================================
    // USUARIO INEXISTENTE
    // ================================================================

    /**
     * @test
     * @testdox Usuario inexistente - Retorna error
     */
    public function modificarUsuario_IdInexistente_RetornaError()
    {
        $resultado = $this->usuarioModel->modificarUsuario(999, $this->personaValida(), 'jperez', 'activo');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado['errores']);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function datosPersonaValida(): array
    {
        // dni 30123456 = el de la propia persona del usuario id_usuario=1 (is_unique se excluye a sí mismo).
        return [
            'dni'              => '30123456',
            'nombre'           => 'Juan',
            'apellido'         => 'Pérez',
            'fecha_nacimiento' => '1990-05-15',
            'telefono'         => '3794123456',
            'calle'            => 'San Martín',
            'altura'           => '123',
        ];
    }

    private function personaValida(): Persona
    {
        return new Persona($this->datosPersonaValida());
    }

    private function persona(array $overrides): Persona
    {
        return new Persona(array_merge($this->datosPersonaValida(), $overrides));
    }
}
