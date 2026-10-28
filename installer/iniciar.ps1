$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'
$base    = $PSScriptRoot
$php     = Join-Path $base 'php'
$maria   = Join-Path $base 'mariadb'
$data    = Join-Path $base 'data'
$app     = Join-Path $base 'Hotel'
$dbPort  = 3307
$webPort = 8080

function Puerto-Abierto($p) {
    try { $c = New-Object Net.Sockets.TcpClient; $c.Connect('127.0.0.1', $p); $c.Close(); return $true }
    catch { return $false }
}
function Esperar-Puerto($p, $seg) {
    for ($i = 0; $i -lt $seg; $i++) { if (Puerto-Abierto $p) { return $true }; Start-Sleep 1 }
    return $false
}

try {
    # Primera vez: crear los datos de MariaDB
    if (-not (Test-Path "$data\mysql")) {
        & "$maria\bin\mysql_install_db.exe" "--datadir=$data" | Out-Null
    }

    # Base de datos
    if (-not (Puerto-Abierto $dbPort)) {
        Start-Process "$maria\bin\mysqld.exe" -WindowStyle Hidden -ArgumentList "--datadir=`"$data`"", "--port=$dbPort", '--bind-address=127.0.0.1'
        if (-not (Esperar-Puerto $dbPort 60)) { throw 'La base de datos no arranco.' }
        Start-Sleep 2
    }

    # Importar database.sql si la base "hotel" no existe
    $mysql = "$maria\bin\mysql.exe"
    $existe = & $mysql -u root -h 127.0.0.1 -P $dbPort -N -e "SHOW DATABASES LIKE 'hotel'"
    if (-not $existe) {
        Push-Location "$app\database"
        & $mysql -u root -h 127.0.0.1 -P $dbPort --default-character-set=utf8mb4 -e 'source database.sql'
        Pop-Location
    }

    # Servidor web
    $env:HOTEL_DB_PORT = $dbPort
    if (-not (Puerto-Abierto $webPort)) {
        Start-Process "$php\php.exe" -WindowStyle Hidden -ArgumentList '-S', "localhost:$webPort", '-t', "`"$app`""
        if (-not (Esperar-Puerto $webPort 20)) { throw 'El servidor web no arranco.' }
    }

    Start-Process "http://localhost:$webPort"
}
catch {
    Add-Type -AssemblyName System.Windows.Forms
    [void][System.Windows.Forms.MessageBox]::Show("No se pudo abrir el sistema: $($_.Exception.Message)", 'Hotel')
}
