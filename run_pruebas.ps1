<#
============================================================
 Runner de pruebas unitarias - EJECUTABLE MULTIPLES VECES

 Un test por metodo, corrible TODO junto o INDIVIDUALMENTE:
   persona       -> PersonaModel::altaPersona
   cliente       -> ClienteModel::altaCliente
   alta-recinto  -> RecintoModel::altaRecinto
   mod-recinto   -> RecintoModel::modificarRecinto
   usuario       -> UsuarioModel::modificarUsuario
   all           -> los cinco (default)

 Cada prueba usa $refresh = true: la base de datos de test
 (SQLite) se reconstruye antes de cada prueba, por lo que el
 script puede correrse N veces seguidas sin limpieza manual.

 Uso:   .\run_pruebas.ps1 [-Target <t>] [-N <veces>]
 Ej.:   .\run_pruebas.ps1                       (todos, 1 vez)
        .\run_pruebas.ps1 -N 5                   (todos, 5 veces)
        .\run_pruebas.ps1 -Target alta-recinto   (solo Alta Recinto)
        .\run_pruebas.ps1 -Target usuario -N 3   (solo Modificar Usuario, 3 veces)
============================================================
#>
param(
    [string]$Target = 'all',
    [int]$N = 1
)

$php = 'C:\xampp\php\php.exe'
if (-not (Test-Path $php)) { $php = 'php' }

$dir = 'tests\_support\Models'

$map = @{
    'all'                   = @('AltaPersonaTest', 'AltaClienteTest', 'AltaRecintoTest', 'ModificarRecintoTest', 'ModificarUsuarioTest')
    'persona'               = @('AltaPersonaTest')
    'cliente'               = @('AltaClienteTest')
    'alta-recinto'          = @('AltaRecintoTest')
    'mod-recinto'           = @('ModificarRecintoTest')
    'usuario'               = @('ModificarUsuarioTest')
    'AltaPersonaTest'       = @('AltaPersonaTest')
    'AltaClienteTest'       = @('AltaClienteTest')
    'AltaRecintoTest'       = @('AltaRecintoTest')
    'ModificarRecintoTest'  = @('ModificarRecintoTest')
    'ModificarUsuarioTest'  = @('ModificarUsuarioTest')
}

if (-not $map.ContainsKey($Target)) {
    Write-Host "Target invalido: '$Target'" -ForegroundColor Red
    Write-Host "Validos: all | persona | cliente | alta-recinto | mod-recinto | usuario"
    exit 2
}
$tests = $map[$Target]

$fallos = 0

for ($i = 1; $i -le $N; $i++) {
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host "  ITERACION $i / $N   (target: $Target)" -ForegroundColor Cyan
    Write-Host "============================================================" -ForegroundColor Cyan

    foreach ($t in $tests) {
        Write-Host ""
        Write-Host "--- $t ---" -ForegroundColor Yellow
        & $php vendor\bin\phpunit "$dir\$t.php" --no-coverage --testdox
        if ($LASTEXITCODE -ne 0) { $fallos++ }
    }
}

Write-Host ""
Write-Host "============================================================"
if ($fallos -gt 0) {
    Write-Host "  RESULTADO FINAL: $fallos ejecucion(es) con fallos" -ForegroundColor Red
    exit 1
} else {
    Write-Host "  RESULTADO FINAL: TODAS LAS PRUEBAS OK ($N iteracion(es))" -ForegroundColor Green
    exit 0
}
