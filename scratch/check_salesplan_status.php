<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('DESCRIBE salesplans');
foreach($columns as $col) {
    if ($col->Field == 'status') {
        echo "Field: " . $col->Field . "\n";
        echo "Type: " . $col->Type . "\n";
    }
}
