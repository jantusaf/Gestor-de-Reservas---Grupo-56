<?php

namespace Tests\Support\Models;

use App\Models\RecintoModel;
use CodeIgniter\Test\CIUnitTestCase;


class AltaRecintoTest extends CIUnitTestCase
{
    private RecintoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();

        // Mock parcial: solo se stubea insert() — el resto de la lógica es real.
        $this->model = $this->getMockBuilder(RecintoModel::class)
            ->onlyMethods(['insert'])
            ->getMock();

        $this->model->method('insert')->willReturn(true);
    }


    /**
     * @test
     * @testdox Todos los datos válidos - Recinto registrado correctamente
     */
    public function altaRecinto_TodosLosDatosValidos()
    {
        $this->model->expects($this->once())->method('insert');

        $resultado = $this->alta();

        $this->assertTrue($resultado['ok']);
    }


    /**
     * @test
     * @testdox Tarifa con decimales - Recinto registrado correctamente
     */
    public function altaRecinto_TarifaConDecimales_CreaElAlta()
    {
        $this->model->expects($this->once())->method('insert');

        $resultado = $this->alta(['tarifa' => '1500.50']);

        $this->assertTrue($resultado['ok']);
    }


    /**
     * @test
     * @testdox Tarifa vacía - Retorna error
     */
    public function altaRecinto_TarifaVacia_RetornaError()
    {
        $this->model->expects($this->never())->method('insert');

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
        $this->model->expects($this->never())->method('insert');

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
        $this->model->expects($this->never())->method('insert');

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
        $this->model->expects($this->never())->method('insert');

        $resultado = $this->alta(['tarifa' => '0']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }


    /**
     * @test
     * @testdox Descripción vacía - Retorna error
     */
    public function altaRecinto_DescripcionVacia_RetornaError()
    {
        $this->model->expects($this->never())->method('insert');

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
        $this->model->expects($this->never())->method('insert');

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
        $this->model->expects($this->never())->method('insert');

        $resultado = $this->alta(['descripcion' => str_repeat('A', 51)]);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }


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

        return $this->model->altaRecinto(
            (string) $d['tarifa'],
            (string) $d['descripcion'],
            (int) $d['id_tipo_recinto']
        );
    }
}
