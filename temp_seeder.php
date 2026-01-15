<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Activity;

try {
    $cat = Category::firstOrCreate(['nama' => 'Intake Activity']);
    $activities = [
        ['nama' => 'Database baru', 'target_bulanan' => 100, 'bobot' => 20],
        ['nama' => 'Follow-up aktif', 'target_bulanan' => 100, 'bobot' => 20],
        ['nama' => 'Presentasi', 'target_bulanan' => 10, 'bobot' => 20],
        ['nama' => 'Visit lokasi', 'target_bulanan' => 10, 'bobot' => 15],
        ['nama' => 'Closing', 'target_bulanan' => 1, 'bobot' => 25],
    ];

    foreach ($activities as $act) {
        Activity::updateOrCreate(
            ['nama' => $act['nama'], 'categories_id' => $cat->id],
            [
                'target_bulanan' => $act['target_bulanan'],
                'target_daily' => $act['target_bulanan'] / 26,
                'bobot' => $act['bobot']
            ]
        );
    }
    echo "Seeding successful\n";
} catch (\Exception $e) {
    echo "Error seeding: " . $e->getMessage() . "\n";
}
