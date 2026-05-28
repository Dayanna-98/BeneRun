<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mldPath = __DIR__ . '/docs/MLD_TEXTUEL_BENERUN.md';
$mld = file_get_contents($mldPath);
$mldTables = [];
$current = null;
foreach (preg_split('/\R/', $mld) as $line) {
    if (preg_match('/^###\s+([a-zA-Z0-9_]+)\s*$/', $line, $m)) {
        $current = $m[1];
        $mldTables[$current] = $mldTables[$current] ?? [];
        continue;
    }
    if ($current && preg_match('/^-\s+Champs:\s+(.+)$/', $line, $m)) {
        $cols = array_filter(array_map('trim', explode(',', $m[1])));
        foreach ($cols as $c) {
            $mldTables[$current][$c] = true;
        }
    }
}

$driver = DB::connection()->getDriverName();
$dbTables = [];

if ($driver === 'mysql') {
    $dbName = DB::selectOne('select database() as db')->db;
    $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name', [$dbName]);
    foreach ($tables as $tObj) {
        $t = $tObj->table_name;
        $dbTables[$t] = [];
        $cols = DB::select('SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ordinal_position', [$dbName, $t]);
        foreach ($cols as $cObj) {
            $dbTables[$t][$cObj->column_name] = true;
        }
    }
} elseif ($driver === 'sqlite') {
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
    foreach ($tables as $tObj) {
        $t = $tObj->name;
        $dbTables[$t] = [];
        $cols = DB::select("PRAGMA table_info('$t')");
        foreach ($cols as $cObj) {
            $dbTables[$t][$cObj->name] = true;
        }
    }
} else {
    echo json_encode(['error' => 'Unsupported driver: '.$driver], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit(0);
}

$missingTablesInMld = array_values(array_diff(array_keys($dbTables), array_keys($mldTables)));
sort($missingTablesInMld);
$extraTablesInMld = array_values(array_diff(array_keys($mldTables), array_keys($dbTables)));
sort($extraTablesInMld);

$columnDiffs = [];
$commonTables = array_intersect(array_keys($dbTables), array_keys($mldTables));
sort($commonTables);
foreach ($commonTables as $t) {
    $dbCols = array_keys($dbTables[$t]);
    $mldCols = array_keys($mldTables[$t]);
    $missingCols = array_values(array_diff($dbCols, $mldCols));
    sort($missingCols);
    $extraCols = array_values(array_diff($mldCols, $dbCols));
    sort($extraCols);
    if ($missingCols || $extraCols) {
        $columnDiffs[] = [
            'table' => $t,
            'missing_in_mld' => $missingCols,
            'extra_in_mld' => $extraCols,
        ];
    }
}

$out = [
    'driver' => $driver,
    'db_table_count' => count($dbTables),
    'mld_table_count' => count($mldTables),
    'missing_tables_in_mld' => $missingTablesInMld,
    'extra_tables_in_mld' => $extraTablesInMld,
    'column_diffs' => $columnDiffs,
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
