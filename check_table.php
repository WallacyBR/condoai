<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verificando estrutura da tabela common_areas...\n";

$columns = DB::select('DESCRIBE common_areas');
foreach($columns as $col) {
    echo $col->Field . "\n";
}

echo "\nVerificando se os campos de limite existem...\n";
$hasMaxBookingsPerDay = false;
$hasMaxBookingsPerMonth = false;
$hasMaxBookingsPerYear = false;

foreach($columns as $col) {
    if ($col->Field === 'max_bookings_per_day') $hasMaxBookingsPerDay = true;
    if ($col->Field === 'max_bookings_per_month') $hasMaxBookingsPerMonth = true;
    if ($col->Field === 'max_bookings_per_year') $hasMaxBookingsPerYear = true;
}

echo "max_bookings_per_day: " . ($hasMaxBookingsPerDay ? "EXISTE" : "NÃO EXISTE") . "\n";
echo "max_bookings_per_month: " . ($hasMaxBookingsPerMonth ? "EXISTE" : "NÃO EXISTE") . "\n";
echo "max_bookings_per_year: " . ($hasMaxBookingsPerYear ? "EXISTE" : "NÃO EXISTE") . "\n"; 