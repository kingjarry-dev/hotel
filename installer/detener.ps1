$base = $PSScriptRoot
try { & "$base\mariadb\bin\mysqladmin.exe" -u root -h 127.0.0.1 -P 3307 shutdown 2>$null } catch {}
Start-Sleep 2
Get-Process php, mysqld, mariadbd -ErrorAction SilentlyContinue |
    Where-Object { $_.Path -and $_.Path.StartsWith($base, [StringComparison]::OrdinalIgnoreCase) } |
    Stop-Process -Force -ErrorAction SilentlyContinue
