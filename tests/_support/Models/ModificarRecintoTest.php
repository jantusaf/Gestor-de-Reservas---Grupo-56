<?php

namespace Tests\Support\Models;

use App\Models\RecintoModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\RecintoSeeder;

/**
 * Pruebas Unitarias - Modificar Recinto
 * Método: RecintoModel::modificarRecinto(int $id, string $tarifa, string $descripcion, int $idTipoRecinto, string $estadoRecinto)
 */
class ModificarRecintoTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed    = RecintoSeeder::class;
    protected $refresh = true;

    private RecintoModel $recintoModel;

    protected function setUp(): void
    {
        parent::setUp();
        // El servicio de validación es compartido durante todo el proceso de PHPUnit
        // y acumula errores entre tests. Lo reseteamos para que cada prueba parta limpia
        // y el script sea ejecutable múltiples veces sin contaminación de estado.
        \Config\Services::validation()->reset();
        $this->recintoModel = new RecintoModel();
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Todos los datos válidos - Modifica el recinto
     */
    public function modificarRecinto_TodosLosDatosValidos()
    {
        $resultado = $this->modificar();

        $this->assertTrue($resultado['ok']);
    }

    /**
     * @test
     * @testdox Cambiar estado a inactivo - Modifica el recinto
     */
    public function modificarRecinto_CambiarEstadoAInactivo_ModificaElRecinto()
    {
        $resultado = $this->modificar([
            'tarifa'         => '2000',
            'descripcion'    => 'Cancha de tenis',
            'estado_recinto' => 'inactivo',
        ]);

        $this->assertTrue($resultado['ok']);
    }

    // ================================================================
    // VALIDACIONES DE TARIFA
    // ================================================================

    /**
     * @test
     * @testdox Tarifa vacía - Retorna error
     */
    public function modificarRecinto_TarifaVacia_RetornaError()
    {
        $resultado = $this->modificar(['tarifa' => '']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Tarifa con letras - Retorna error
     */
    public function modificarRecinto_TarifaConLetras_RetornaError()
    {
        $resultado = $this->modificar(['tarifa' => 'abc']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Tarifa negativa - Retorna error
     */
    public function modificarRecinto_TarifaNegativa_RetornaError()
    {
        $resultado = $this->modificar(['tarifa' => '-100']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Tarifa igual a cero - Retorna error
     */
    public function modificarRecinto_TarifaCero_RetornaError()
    {
        $resultado = $this->modificar(['tarifa' => '0']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE DESCRIPCIÓN
    // ================================================================

    /**
     * @test
     * @testdox Descripción vacía - Retorna error
     */
    public function modificarRecinto_DescripcionVacia_RetornaError()
    {
        $resultado = $this->modificar(['descripcion' => '']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Descripción menor a 3 caracteres - Retorna error
     */
    public function modificarRecinto_DescripcionMenorA3Caracteres_RetornaError()
    {
        $resultado = $this->modificar(['descripcion' => 'Ab']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Descripción mayor a 50 caracteres - Retorna error
     */
    public function modificarRecinto_DescripcionMayorA50Caracteres_RetornaError()
    {
        $resultado = $this->modificar(['descripcion' => str_repeat('A', 51)]);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Estado inválido - Retorna error
     */
    public function modificarRecinto_EstadoInvalido_RetornaError()
    {
        $resultado = $this->modificar(['estado_recinto' => 'pausado']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('estado_recinto', $resultado['errores']);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function datosRecintoValido(): array
    {
        // id_recinto = 1 -> recinto sembrado por RecintoSeeder.
        return [
            'id_recinto'      => 1,
            'tarifa'          => '2000',
            'descripcion'     => 'Cancha de tenis',
            'id_tipo_recinto' => 1,
            'estado_recinto'  => 'activo',
        ];
    }

    private function modificar(array $overrides = []): array
    {
        $d = array_merge($this->datosRecintoValido(), $overrides);

        return $this->recintoModel->modificarRecinto(
            (int) $d['id_recinto'],
            (string) $d['tarifa'],
            (string) $d['descripcion'],
            (int) $d['id_tipo_recinto'],
            (string) $d['estado_recinto']
        );
    }
}
