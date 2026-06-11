<?php

namespace Tests\Support\Models;

use App\Models\RecintoModel;
use CodeIgniter\Test\CIUnitTestCase;


class ModificarRecintoTest extends CIUnitTestCase
{
    private RecintoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();

        // Mock parcial: solo se stubea update() — el resto de la lógica es real.
        $this->model = $this->getMockBuilder(RecintoModel::class)
            ->onlyMethods(['update'])
            ->getMock();

        $this->model->method('update')->willReturn(true);
    }


    public function modificarRecinto_TodosLosDatosValidos()
    {
        $this->model->expects($this->once())->method('update');

        $resultado = $this->modificar();

        $this->assertTrue($resultado['ok']);
    }


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


    public function modificarRecinto_TarifaVacia_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['tarifa' => '']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }


    public function modificarRecinto_TarifaConLetras_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['tarifa' => 'abc']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }


    public function modificarRecinto_TarifaNegativa_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['tarifa' => '-100']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }


    public function modificarRecinto_TarifaCero_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['tarifa' => '0']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
    }


    public function modificarRecinto_DescripcionVacia_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['descripcion' => '']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }


    public function modificarRecinto_DescripcionMenorA3Caracteres_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['descripcion' => 'Ab']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }


    public function modificarRecinto_DescripcionMayorA50Caracteres_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['descripcion' => str_repeat('A', 51)]);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('descripcion', $resultado['errores']);
    }


    public function modificarRecinto_EstadoInvalido_RetornaError()
    {
        $this->model->expects($this->never())->method('update');

        $resultado = $this->modificar(['estado_recinto' => 'pausado']);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('estado_recinto', $resultado['errores']);
    }


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
