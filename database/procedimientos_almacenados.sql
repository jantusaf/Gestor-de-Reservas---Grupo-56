-- ============================================================
--  Procedimientos almacenados — TDC Grupo 56
--  Base de datos : tdc_grupo_56
--  Motor         : MariaDB 10.4.24
--  PHP           : 7.4 / CodeIgniter 4
--
--  CÓMO EJECUTAR:
--    phpMyAdmin   → pestaña SQL → pegar todo → Ejecutar
--    mysql CLI    → mysql -u root tdc_grupo_56 < procedimientos_almacenados.sql
-- ============================================================

USE tdc_grupo_56;

-- ============================================================
--  SP 1 — CONSULTA
--  Invocado por : ReservaModel::listarReservas(string $dni)
--
--  Parámetro:
--    p_dni VARCHAR(20)
--      '' (vacío)  → devuelve todas las reservas
--      '123'       → filtra: persona.dni LIKE '123%'
--
--  Equivalencia exacta con el QueryBuilder original:
--    ->like('persona.dni', $dni, 'after')  ≡  LIKE CONCAT(p_dni,'%')
--    rama sin filtro (dni='')              ≡  condición p_dni = ''
--
--  Retorna: result set con las mismas columnas que el SELECT
--           original de CodeIgniter (reserva.* + alias).
-- ============================================================

DROP PROCEDURE IF EXISTS sp_listar_reservas;

DELIMITER $$

CREATE PROCEDURE sp_listar_reservas(IN p_dni VARCHAR(20))
BEGIN
    SELECT
        reserva.id_reserva,
        reserva.fecha_reserva,
        reserva.monto,
        reserva.estado_reserva,
        reserva.id_horario,
        reserva.id_cliente,
        reserva.id_recinto,
        reserva.id_usuario,
        -- El estado de pago se DERIVA de la tabla 'pago' (única fuente de verdad):
        --   sin pago        -> 'pendiente'
        --   pago vigente    -> 'pagada'
        --   pago reembolsado-> 'reembolsado'
        COALESCE(MAX(pago.estado), 'pendiente') AS estado_pago,
        persona.nombre,
        persona.apellido,
        persona.dni,
        horario.horario                  AS hora,
        recinto.descripcion              AS recinto_desc,
        tipo_recinto.nombre_tipo_recinto,
        medio_pago.nombre_medio_pago
    FROM reserva
    INNER JOIN cliente
           ON cliente.id_cliente             = reserva.id_cliente
    INNER JOIN persona
           ON persona.id_persona             = cliente.id_persona
    INNER JOIN horario
           ON horario.id_horario             = reserva.id_horario
    INNER JOIN recinto
           ON recinto.id_recinto             = reserva.id_recinto
    INNER JOIN tipo_recinto
           ON tipo_recinto.id_tipo_recinto   = recinto.id_tipo_recinto
    LEFT  JOIN pago
           ON pago.id_reserva               = reserva.id_reserva
    LEFT  JOIN medio_pago
           ON medio_pago.id_medio_pago       = pago.id_medio_pago
    WHERE p_dni = '' OR persona.dni LIKE CONCAT(p_dni, '%')
    GROUP BY reserva.id_reserva
    ORDER BY reserva.id_reserva DESC;
END$$

DELIMITER ;


-- ============================================================
--  SP 2 — ACTUALIZACIÓN
--  Invocado por : RecintoModel::deshabilitar(int $id)
--
--  Parámetro:
--    p_id INT  — id_recinto a deshabilitar
--
--  Retorna: result set de una fila:
--    encontrado = 1 → registro existía → UPDATE ejecutado
--    encontrado = 0 → registro no existe → UPDATE omitido
--
--  Equivalencia exacta con el código PHP original:
--    $this->find($id)                ≡  SELECT COUNT(*) INTO v_existe
--    !find → return ['ok'=>false]    ≡  condición en PHP sobre 'encontrado'
--    $this->update(...'inactivo')    ≡  UPDATE dentro del IF
-- ============================================================

DROP PROCEDURE IF EXISTS sp_deshabilitar_recinto;

DELIMITER $$

CREATE PROCEDURE sp_deshabilitar_recinto(IN p_id INT)
BEGIN
    DECLARE v_existe INT DEFAULT 0;

    SELECT COUNT(*)
    INTO   v_existe
    FROM   recinto
    WHERE  id_recinto = p_id;

    IF v_existe > 0 THEN
        UPDATE recinto
        SET    estado_recinto = 'inactivo'
        WHERE  id_recinto     = p_id;
    END IF;

    SELECT v_existe AS encontrado;
END$$

DELIMITER ;


-- ============================================================
--  SP 3 — CONSULTA
--  Invocado por : PagoModel::datosFactura(int $idReserva)
--
--  Parámetro:
--    p_id_reserva INT — id de la reserva cuya factura se consulta
--
--  Retorna: result set de una fila (o vacío si no existe pago
--           para esa reserva). PHP toma la primera fila con
--           getRowArray(), que devuelve null si el set está vacío.
--           Comportamiento idéntico al original.
--
--  Todos los JOINs son INNER (equivalente al ->join() por defecto
--  de CodeIgniter 4 sin tercer parámetro de tipo).
-- ============================================================

DROP PROCEDURE IF EXISTS sp_datos_factura;

DELIMITER $$

CREATE PROCEDURE sp_datos_factura(IN p_id_reserva INT)
BEGIN
    SELECT
        pago.id_pago,
        pago.fecha_pago,
        pago.monto_total,
        medio_pago.nombre_medio_pago,
        persona.nombre,
        persona.apellido,
        persona.dni,
        reserva.fecha_reserva,
        recinto.descripcion              AS recinto_desc,
        tipo_recinto.nombre_tipo_recinto,
        horario.horario                  AS hora,
        usuario.nombre_usuario
    FROM pago
    INNER JOIN reserva
           ON reserva.id_reserva             = pago.id_reserva
    INNER JOIN cliente
           ON cliente.id_cliente             = reserva.id_cliente
    INNER JOIN persona
           ON persona.id_persona             = cliente.id_persona
    INNER JOIN recinto
           ON recinto.id_recinto             = reserva.id_recinto
    INNER JOIN tipo_recinto
           ON tipo_recinto.id_tipo_recinto   = recinto.id_tipo_recinto
    INNER JOIN horario
           ON horario.id_horario             = reserva.id_horario
    INNER JOIN medio_pago
           ON medio_pago.id_medio_pago       = pago.id_medio_pago
    INNER JOIN usuario
           ON usuario.id_usuario             = pago.id_usuario
    WHERE pago.id_reserva = p_id_reserva;
END$$

DELIMITER ;


-- ============================================================
--  VERIFICACIÓN — ejecutar después de crear los procedimientos
-- ============================================================

-- 1. Confirmar existencia de los tres SPs:
-- SELECT routine_name, routine_type, created
-- FROM   information_schema.routines
-- WHERE  routine_schema = 'tdc_grupo_56'
--   AND  routine_type   = 'PROCEDURE';

-- 2. Prueba de consulta sin filtro (debe retornar 7 filas):
-- CALL sp_listar_reservas('');

-- 3. Prueba de consulta con filtro DNI '26' (reservas de Carolina Jantus):
-- CALL sp_listar_reservas('26');

-- 4. Prueba de actualización — recinto activo (retorna encontrado=1):
-- CALL sp_deshabilitar_recinto(2);

-- 5. Prueba de actualización — id inexistente (retorna encontrado=0):
-- CALL sp_deshabilitar_recinto(9999);

-- 6. Prueba factura — reserva existente con pago (debe retornar 1 fila):
-- CALL sp_datos_factura(1);

-- 7. Prueba factura — reserva sin pago o inexistente (debe retornar 0 filas):
-- CALL sp_datos_factura(9999);
