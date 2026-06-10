@echo off
setlocal enabledelayedexpansion

rem ============================================================
rem  Runner de pruebas unitarias - EJECUTABLE MULTIPLES VECES
rem
rem  Un test por metodo, corrible TODO junto o INDIVIDUALMENTE:
rem    persona       -> PersonaModel::altaPersona
rem    cliente       -> ClienteModel::altaCliente
rem    alta-recinto  -> RecintoModel::altaRecinto
rem    mod-recinto   -> RecintoModel::modificarRecinto
rem    usuario       -> UsuarioModel::modificarUsuario
rem    all           -> los cinco (default)
rem
rem  Cada prueba usa $refresh = true: la base de datos de test
rem  (SQLite) se reconstruye antes de cada prueba, por lo que el
rem  script puede correrse N veces seguidas sin limpieza manual.
rem
rem  Uso:   run_pruebas.bat [TARGET] [N]
rem           TARGET = all | persona | cliente | alta-recinto | mod-recinto | usuario
rem                    (o el nombre exacto de la clase, ej. AltaRecintoTest)
rem           N      = cantidad de veces a repetir (default 1)
rem
rem  Ejemplos:
rem    run_pruebas.bat                  (todos, 1 vez)
rem    run_pruebas.bat all 5            (todos, 5 veces)
rem    run_pruebas.bat alta-recinto     (solo Alta Recinto, 1 vez)
rem    run_pruebas.bat usuario 3        (solo Modificar Usuario, 3 veces)
rem ============================================================

set "PHP=C:\xampp\php\php.exe"
if not exist "%PHP%" set "PHP=php"

set "TARGET=%~1"
if "%TARGET%"=="" set "TARGET=all"
set "N=%~2"
if "%N%"=="" set "N=1"

set "DIR=tests\_support\Models"

rem --- Resolver TARGET -> lista de clases de test ---
set "TESTS="
if /i "%TARGET%"=="all"                   set "TESTS=AltaPersonaTest AltaClienteTest AltaRecintoTest ModificarRecintoTest ModificarUsuarioTest"
if /i "%TARGET%"=="persona"               set "TESTS=AltaPersonaTest"
if /i "%TARGET%"=="cliente"               set "TESTS=AltaClienteTest"
if /i "%TARGET%"=="alta-recinto"          set "TESTS=AltaRecintoTest"
if /i "%TARGET%"=="mod-recinto"           set "TESTS=ModificarRecintoTest"
if /i "%TARGET%"=="usuario"               set "TESTS=ModificarUsuarioTest"
rem tambien se acepta el nombre exacto de la clase:
if /i "%TARGET%"=="AltaPersonaTest"       set "TESTS=AltaPersonaTest"
if /i "%TARGET%"=="AltaClienteTest"       set "TESTS=AltaClienteTest"
if /i "%TARGET%"=="AltaRecintoTest"       set "TESTS=AltaRecintoTest"
if /i "%TARGET%"=="ModificarRecintoTest"  set "TESTS=ModificarRecintoTest"
if /i "%TARGET%"=="ModificarUsuarioTest"  set "TESTS=ModificarUsuarioTest"

if "%TESTS%"=="" (
    echo TARGET invalido: "%TARGET%"
    echo Validos: all ^| persona ^| cliente ^| alta-recinto ^| mod-recinto ^| usuario
    exit /b 2
)

set /a TOTAL_FALLOS=0

for /L %%i in (1,1,%N%) do (
    echo.
    echo ============================================================
    echo   ITERACION %%i / %N%   ^(target: %TARGET%^)
    echo ============================================================
    for %%T in (%TESTS%) do (
        echo.
        echo --- %%T ---
        "%PHP%" vendor\bin\phpunit "%DIR%\%%T.php" --no-coverage --testdox
        if errorlevel 1 set /a TOTAL_FALLOS+=1
    )
)

echo.
echo ============================================================
if !TOTAL_FALLOS! GTR 0 (
    echo   RESULTADO FINAL: !TOTAL_FALLOS! ejecucion^(es^) con fallos
    echo ============================================================
    exit /b 1
) else (
    echo   RESULTADO FINAL: TODAS LAS PRUEBAS OK ^(%N% iteracion^(es^)^)
    echo ============================================================
    exit /b 0
)
