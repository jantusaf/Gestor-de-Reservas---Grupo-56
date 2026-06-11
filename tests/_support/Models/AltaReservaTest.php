<?php

namespace Tests\Support\Models;

use App\Models\ReservaModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas Unitarias - Alta de Reserva
 * Método: ReservaModel::crearReserva(string $fecha, int $idCliente, int $idRecinto, int $idHorario, int $idUsuario)
 *
 * Pruebas FANTASMAS: no se conectan a la base de datos.
 * Se mockean validarReserva(), insert() y getInsertID() en ReservaModel.
 * validarReserva() concentra toda la lógica de validación y consultas a BD;
 * al mockearlo podemos probar crearReserva() de forma completamente aislada.
 */
class AltaReservaTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();
    }

    /**
     * Crea un mock parcial de ReservaModel con comportamiento configurable.
     *
     * @param array|null $validarResult  Lo que devuelve validarReserva() (null = validación exitosa).
     */
    private function makeModel(?array $validarResult = null): ReservaModel
    {
        $model = $this->getMockBuilder(ReservaModel::class)
            ->onlyMethods(['validarReserva', 'insert', 'getInsertID'])
            ->getMock();

        $exitoso = ['ok' => true, 'recinto' => ['tarifa' => '1500']];
        $model->method('validarReserva')->willReturn($validarResult ?? $exitoso);
        $model->method('insert')->willReturn(true);
        $model->method('getInsertID')->willReturn(1);

        return $model;
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Todos los datos válidos - Reserva registrada correctamente
     */
    public function crearReserva_TodosLosDatosValidos()
    {
        $model = $this->makeModel();
        $model->expects($this->once())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 1, 1, 1, 1);

        $this->assertTrue($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado);
        $this->assertEquals(1, $resultado['id']);
    }

    // ================================================================
    // VALIDACIONES DE FECHA
    // ================================================================

    /**
     * @test
     * @testdox Fecha vacía - Retorna error
     */
    public function crearReserva_FechaVacia_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['fecha_reserva' => 'El campo Fecha es obligatorio.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('', 1, 1, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_reserva', $resultado['mensajes']);
    }

    /**
     * @test
     * @testdox Fecha anterior a hoy - Retorna error
     */
    public function crearReserva_FechaAnteriorAHoy_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['fecha_reserva' => 'La fecha no puede ser anterior a hoy.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2000-01-01', 1, 1, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_reserva', $resultado['mensajes']);
    }

    // ================================================================
    // VALIDACIONES DE CLIENTE
    // ================================================================

    /**
     * @test
     * @testdox Cliente no seleccionado - Retorna error
     */
    public function crearReserva_ClienteNoSeleccionado_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['id_cliente' => 'El campo Cliente es obligatorio.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 0, 1, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_cliente', $resultado['mensajes']);
    }

    /**
     * @test
     * @testdox Cliente inactivo o inexistente - Retorna error
     */
    public function crearReserva_ClienteInactivo_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['id_cliente' => 'Cliente inválido o inactivo.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 99, 1, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_cliente', $resultado['mensajes']);
    }

    // ================================================================
    // VALIDACIONES DE RECINTO
    // ================================================================

    /**
     * @test
     * @testdox Recinto no seleccionado - Retorna error
     */
    public function crearReserva_RecintoNoSeleccionado_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['id_recinto' => 'El campo Recinto es obligatorio.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 1, 0, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_recinto', $resultado['mensajes']);
    }

    /**
     * @test
     * @testdox Recinto inválido o no habilitado - Retorna error
     */
    public function crearReserva_RecintoInhabilitado_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['id_recinto' => 'Recinto inválido o no habilitado.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 1, 99, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_recinto', $resultado['mensajes']);
    }

    // ================================================================
    // VALIDACIONES DE HORARIO
    // ================================================================

    /**
     * @test
     * @testdox Horario no seleccionado - Retorna error
     */
    public function crearReserva_HorarioNoSeleccionado_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['id_horario' => 'El campo Horario es obligatorio.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 1, 1, 0, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_horario', $resultado['mensajes']);
    }

    /**
     * @test
     * @testdox Horario ya reservado - Retorna error
     */
    public function crearReserva_HorarioOcupado_RetornaError()
    {
        $model = $this->makeModel([
            'ok'       => false,
            'mensajes' => ['disponibilidad' => 'Ese horario ya está reservado.'],
        ]);
        $model->expects($this->never())->method('insert');

        $resultado = $model->crearReserva('2026-12-01', 1, 1, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('disponibilidad', $resultado['mensajes']);
    }
}
