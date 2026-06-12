<?php

namespace Tests\Support\Models;

use App\Models\RecintoModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas Unitarias - Modificar Recinto
 * Método: RecintoModel::modificarRecinto(int $id, string $tarifa, string $descripcion, int $idTipoRecinto, string $estadoRecinto)
 *
 * Pruebas FANTASMAS: no se conectan a la base de datos.
 * Se mockea update() para simular la actualización sin tocar la BD.
 */
class ModificarRecintoTest extends CIUnitTestCase
{
    private RecintoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();

        $this->model = $this->getMockBuilder(RecintoModel::class)
            ->onlyMethods(['update'])
            ->getMock();

        $this->model->method('update')->willReturn(true);
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Todos los datos válidos - Recinto modificado correctamente
     */
    public function modificarRecinto_TodosLosDatosValidos()
    {
        $this->model->expects($this->once())->method('update');

        $resultado = $this->modificar();

        $this->assertTrue($resultado['ok']);
    }

    /**
     * @test
     * @testdox Cambiar estado a inactivo - Recinto modificado correctamente
     */
    public function modificarRecinto_CambiarEstadoAInactivo_ModificaElRecinto()
    {
        $this->model->expects($this->once())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

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
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['estado_recinto' => 'pausado']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('estado_recinto', $resultado['errores']);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function datosRecintoValido(): array
    {
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

        return $this->model->modificarRecinto(
            (int) $d['id_recinto'],
            (string) $d['tarifa'],
            (string) $d['descripcion'],
            (int) $d['id_tipo_recinto'],
            (string) $d['estado_recinto']
        );
    }
}
