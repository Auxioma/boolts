@echo off
setlocal enabledelayedexpansion

echo [1/8] Drop database...
php bin/console doctrine:schema:drop --full-database --force
if %errorlevel% neq 0 ( echo ERREUR etape 1 & exit /b %errorlevel% )

echo [2/8] Update schema...
php bin/console d:s:u --force
if %errorlevel% neq 0 ( echo ERREUR etape 2 & exit /b %errorlevel% )

echo [3/8] Load fixtures...
php bin/console doctrine:fixtures:load --no-interaction
if %errorlevel% neq 0 ( echo ERREUR etape 3 & exit /b %errorlevel% )

echo [4/8] Clear cache...
php bin/console cache:clear --no-interaction
if %errorlevel% neq 0 ( echo ERREUR etape 4 & exit /b %errorlevel% )

echo [5/8] Warmup cache...
php bin/console cache:warmup --no-interaction
if %errorlevel% neq 0 ( echo ERREUR etape 5 & exit /b %errorlevel% )

echo [6/8] Install assets...
php bin/console assets:install public --no-interaction
if %errorlevel% neq 0 ( echo ERREUR etape 6 & exit /b %errorlevel% )

echo [7/8] Install importmap...
php bin/console importmap:install --no-interaction
if %errorlevel% neq 0 ( echo ERREUR etape 7 & exit /b %errorlevel% )

echo [8/8] Compile asset-map...
php bin/console asset-map:compile
if %errorlevel% neq 0 ( echo ERREUR etape 8 & exit /b %errorlevel% )

echo.
echo Done!
pause
