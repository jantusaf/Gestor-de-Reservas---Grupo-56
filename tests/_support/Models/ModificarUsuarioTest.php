<?php

namespace Tests\Support\Models;

use App\Models\UsuarioModel;
use App\Models\PersonaModel;
use App\Entities\Persona;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas Unitarias - Modificar Usuario
 * Método: UsuarioModel::modificarUsuario(int $id, Persona $persona, string $nombreUsuario, string $estadoUsuario, ?PersonaModel)
 *
 * Pruebas FANTASMAS: no se conectan a la base de datos.
 * Se mockean find(), where(), first() y persistirModificacionUsuario() en UsuarioModel,
 * y actualizarPersona() en PersonaModel (inyectado como dependencia).
 *
 * Cada test crea su propio mock para evitar conflictos de comportamiento.
 */
class ModificarUsuarioTest extends CIUnitTestCase
{
    private const ID_USUARIO = 1;

    private $usuarioFake = [
        'id_usuario'      => 1,
        'id_persona'      => 1,
        'nombre_usuario'  => 'jperez',
        'estado_usuario'  => 'activo',
        'id_tipo_usuario' => 2,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();
    }

    /**
     * Crea un mock parcial de UsuarioModel con comportamiento configurable.
     *
     * @param array|null $usuarioFind     Lo que devuelve find() (null = usuario no existe).
     * @param array|null $firstReturn     Lo que devuelve first() para la unicidad del username.
     * @param array      $persistirResult Lo que devuelve persistirModificacionUsuario().
     */
    private function makeModel(
        $usuarioFind    = null,
        $firstReturn    = null,
        array $persistirResult = ['ok' => true]
    ): UsuarioModel {
        $model = $this->getMockBuilder(UsuarioModel::class)
            ->onlyMethods(['find', 'first', 'persistirModificacionUsuario'])
            ->addMethods(['where'])    // where() llega via __call() en CI4 — no es método real
            ->getMock();

        $model->method('find')->willReturn($usuarioFind === null ? $this->usuarioFake : $usuarioFind);
        $model->method('where')->willReturnSelf();
        $model->method('first')->willReturn($firstReturn);
        $model->method('persistirModificacionUsuario')->willReturn($persistirResult);

        return $model;
    }

    /**
     * Crea un mock de PersonaModel con comportamiento configurable.
     */
    private function makePersonaModel(array $actualizarResult = ['ok' => true]): PersonaModel
    {
        $mock = $this->getMockBuilder(PersonaModel::class)
            ->onlyMethods(['actualizarPersona'])
            ->getMock();

        $mock->method('actualizarPersona')->willReturn($actualizarResult);

        return $mock;
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Todos los datos válidos - Usuario modificado correctamente
     */
    public function modificarUsuario_TodosLosDatosValidos()
    {
        $model = $this->makeModel();
        $model->expects($this->once())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO,
            $this->personaValida(),
            'jperez',
            'activo',
            $this->makePersonaModel()
        );

        $this->assertTrue($resultado['ok']);
    }

    /**
     * @test
     * @testdox Cambiar estado a inactivo - Usuario modificado correctamente
     */
    public function modificarUsuario_CambiarEstadoAInactivo_ModificaElUsuario()
    {
        $model = $this->makeModel();
        $model->expects($this->once())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO,
            $this->personaValida(),
            'jperez',
            'inactivo',
            $this->makePersonaModel()
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
        $model = $this->makeModel();
        $model->expects($this->never())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO, $this->personaValida(), '', 'activo', $this->makePersonaModel()
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre_usuario', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre de usuario menor a 3 caracteres - Retorna error
     */
    public function modificarUsuario_NombreUsuarioMenorA3Caracteres_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO, $this->personaValida(), 'ab', 'activo', $this->makePersonaModel()
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre_usuario', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre de usuario ya registrado - Retorna error
     */
    public function modificarUsuario_NombreUsuarioDuplicado_RetornaError()
    {
        // first() devuelve una fila: simula que 'agomez' pertenece a otro usuario.
        $model = $this->makeModel(null, ['id_usuario' => 2, 'nombre_usuario' => 'agomez']);
        $model->expects($this->never())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO, $this->personaValida(), 'agomez', 'activo', $this->makePersonaModel()
        );

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO, $this->personaValida(), 'jperez', 'suspendido', $this->makePersonaModel()
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('estado_usuario', $resultado['errores']);
    }

    // ================================================================
    // ERRORES PROPAGADOS DESDE PersonaModel (vía persistirModificacionUsuario)
    // ================================================================

    /**
     * @test
     * @testdox DNI ya registrado - Retorna error
     */
    public function modificarUsuario_DniDuplicado_RetornaError()
    {
        // Simulamos que persistirModificacionUsuario propagó el error de DNI duplicado
        // tal como lo haría si actualizarPersona() rechazara la operación.
        $model = $this->makeModel(null, null, [
            'ok'     => false,
            'errores' => ['dni' => 'El DNI ya está registrado.'],
        ]);

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO,
            $this->persona(['dni' => '99999999']),
            'jperez',
            'activo',
            $this->makePersonaModel()
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre de persona con números - Retorna error
     */
    public function modificarUsuario_NombrePersonaConNumeros_RetornaError()
    {
        $model = $this->makeModel(null, null, [
            'ok'     => false,
            'errores' => ['nombre' => 'El campo Nombre solo puede contener letras y espacios.'],
        ]);

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO,
            $this->persona(['nombre' => 'Juan123']),
            'jperez',
            'activo',
            $this->makePersonaModel()
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Fecha de nacimiento futura - Retorna error
     */
    public function modificarUsuario_FechaNacimientoFutura_RetornaError()
    {
        $model = $this->makeModel(null, null, [
            'ok'     => false,
            'errores' => ['fecha_nacimiento' => 'La fecha de nacimiento debe ser anterior a hoy.'],
        ]);

        $resultado = $model->modificarUsuario(
            self::ID_USUARIO,
            $this->persona(['fecha_nacimiento' => '2030-01-01']),
            'jperez',
            'activo',
            $this->makePersonaModel()
        );

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
        // find() retorna false para simular usuario inexistente.
        $model = $this->makeModel(false);
        $model->expects($this->never())->method('persistirModificacionUsuario');

        $resultado = $model->modificarUsuario(999, $this->personaValida(), 'jperez', 'activo', $this->makePersonaModel());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado['errores']);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function datosPersonaValida(): array
    {
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
