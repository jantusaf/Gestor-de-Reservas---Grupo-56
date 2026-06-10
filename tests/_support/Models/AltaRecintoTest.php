<?php

namespace Tests\Support\Models;

use App\Models\RecintoModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\RecintoSeeder;

/**
 * Pruebas Unitarias - Alta de Recinto
 * Método: RecintoModel::altaRecinto(string $tarifa, string $descripcion, int $idTipoRecinto)
 */
class AltaRecintoTest extends CIUnitTestCase
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
     * @testdox Todos los datos válidos - Crea el alta
     */
    public function altaRecinto_TodosLosDatosValidos()
    {
        $resultado = $this->alta();

        $this->assertTrue($resultado['ok']);
    }

    /**
     * @test
     * @testdox Tarifa con decimales - Crea el alta
     */
    public function altaRecinto_TarifaConDecimales_CreaElAlta()
    {
        $resultado = $this->alta(['tarifa' => '1500.50']);

        $this->assertTrue($resultado['ok']);
    }

    // ================================================================
    // VALIDACIONES DE TARIFA
    // ================================================================

    /**
     * @test
     * @testdox Tarifa vacía - Retorna error
     */
    public function altaRecinto_TarifaVacia_RetornaError()
    {
        $resultado = $this->alta(['tarifa' => '']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Tarifa con letras - Retorna error
     */
    public function altaRecinto_TarifaConLetras_RetornaError()
    {
        $resultado = $this->alta(['tarifa' => 'abc']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Tarifa negativa - Retorna error
     */
    public function altaRecinto_TarifaNegativa_RetornaError()
    {
        $resultado = $this->alta(['tarifa' => '-100']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Tarifa igual a cero - Retorna error
     */
    public function altaRecinto_TarifaCero_RetornaError()
    {
        $resultado = $this->alta(['tarifa' => '0']);

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
    public function altaRecinto_DescripcionVacia_RetornaError()
    {
        $resultado = $this->alta(['descripcion' => '']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Descripción menor a 3 caracteres - Retorna error
     */
    public function altaRecinto_DescripcionMenorA3Caracteres_RetornaError()
    {
        $resultado = $this->alta(['descripcion' => 'Ab']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Descripción mayor a 50 caracteres - Retorna error
     */
    public function altaRecinto_DescripcionMayorA50Caracteres_RetornaError()
    {
        $resultado = $this->alta(['descripcion' => str_repeat('A', 51)]);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function datosRecintoValido(): array
    {
        return [
            'tarifa'          => '1500',
            'descripcion'     => 'Cancha de fútbol',
            'id_tipo_recinto' => 1,
        ];
    }

    private function alta(array $overrides = []): array
    {
        $d = array_merge($this->datosRecintoValido(), $overrides);

        return $this->recintoModel->altaRecinto(
            (string) $d['tarifa'],
            (string) $d['descripcion'],
            (int) $d['id_tipo_recinto']
        );
    }
}
