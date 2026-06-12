$ErrorActionPreference = "Stop"

$steps = @(
    @{ label = "Drop database";     cmd = "php bin/console doctrine:schema:drop --full-database --force" },
    @{ label = "Update schema";     cmd = "php bin/console d:s:u --force" },
    @{ label = "Load fixtures";     cmd = "php bin/console doctrine:fixtures:load --no-interaction" },
    @{ label = "Clear cache";       cmd = "php bin/console cache:clear --no-interaction" },
    @{ label = "Warmup cache";      cmd = "php bin/console cache:warmup --no-interaction" },
    @{ label = "Install assets";    cmd = "php bin/console assets:install public --no-interaction" },
    @{ label = "Install importmap"; cmd = "php bin/console importmap:install --no-interaction" },
    @{ label = "Compile asset-map"; cmd = "php bin/console asset-map:compile" }
)

$total = $steps.Count
$i = 1

foreach ($step in $steps) {
    Write-Host "[$i/$total] $($step.label)..." -ForegroundColor Cyan
    Invoke-Expression $step.cmd
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERREUR a l'etape [$i/$total] : $($step.label)" -ForegroundColor Red
        exit $LASTEXITCODE
    }
    $i++
}

Write-Host ""
Write-Host "Done!" -ForegroundColor Green
