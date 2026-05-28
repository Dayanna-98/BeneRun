<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mldPath = __DIR__ . '/docs/MLD_TEXTUEL_BENERUN.md';
$mld = file_get_contents($mldPath);

$mldFks = [];
foreach (preg_split('/\R/', $mld) as $line) {
    if (preg_match('/^-\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*->\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)/', $line, $m)) {
        $mldFks[$m[1].'.'.$m[2].'->'.$m[3].'.'.$m[4]] = true;
    }
}

$driver = DB::connection()->getDriverName();
if ($driver !== 'mysql') {
    echo json_encode(['error' => 'FK compare implemented for mysql only', 'driver' => $driver], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    exit(0);
}

$dbName = DB::selectOne('select database() as db')->db;
$rows = DB::select(
    'SELECT table_name, column_name, referenced_table_name, referenced_column_name
     FROM information_schema.key_column_usage
     WHERE table_schema = ?
       AND referenced_table_name IS NOT NULL
     ORDER BY table_name, column_name',
    [$dbName]
);

$dbFks = [];
foreach ($rows as $r) {
    $dbFks[$r->table_name.'.'.$r->column_name.'->'.$r->referenced_table_name.'.'.$r->referenced_column_name] = true;
}

$missingInMld = array_values(array_diff(array_keys($dbFks), array_keys($mldFks)));
sort($missingInMld);
$extraInMld = array_values(array_diff(array_keys($mldFks), array_keys($dbFks)));
sort($extraInMld);

echo json_encode([
    'db_fk_count' => count($dbFks),
    'mld_fk_count' => count($mldFks),
    'missing_fk_in_mld' => $missingInMld,
    'extra_fk_in_mld' => $extraInMld,
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
