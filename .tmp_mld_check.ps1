$ErrorActionPreference = "Stop"

$mldPath = "docs/MLD_TEXTUEL_BENERUN.md"
$migrationFiles = Get-ChildItem "database/migrations" -File | Sort-Object Name

$mld = Get-Content $mldPath -Raw
$mldTables = @{}
$current = $null
foreach ($line in ($mld -split "`r?`n")) {
    if ($line -match '^###\s+([a-zA-Z0-9_]+)\s*$') {
        $current = $Matches[1]
        if (-not $mldTables.ContainsKey($current)) { $mldTables[$current] = New-Object System.Collections.Generic.HashSet[string] }
        continue
    }
    if ($current -and $line -match '^-\s+Champs:\s+(.+)$') {
        $cols = $Matches[1].Split(',') | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne '' }
        foreach ($c in $cols) { [void]$mldTables[$current].Add($c) }
    }
}

$dbTables = @{}
function Ensure-Table([string]$name) {
    if (-not $dbTables.ContainsKey($name)) {
        $dbTables[$name] = New-Object System.Collections.Generic.HashSet[string]
    }
}
function Add-Col([string]$table, [string]$col) {
    Ensure-Table $table
    [void]$dbTables[$table].Add($col)
}

$blockRegex = 'Schema::(?:create|table)\(\s*''([a-zA-Z0-9_]+)''\s*,\s*function\s*\(Blueprint\s*\$table\)\s*\{([\s\S]*?)\}\s*\);'
$createRegex = 'Schema::create\(\s*''([a-zA-Z0-9_]+)''\s*,'
$tableRegex = 'Schema::table\(\s*''([a-zA-Z0-9_]+)''\s*,'
$colRegex = '\$table->(?:id|increments|bigIncrements|integer|bigInteger|unsignedBigInteger|tinyInteger|smallInteger|mediumInteger|float|double|decimal|boolean|string|char|text|mediumText|longText|json|jsonb|date|dateTime|timestamp|time|year|uuid|ipAddress|binary|enum|set)\(\s*''([a-zA-Z0-9_]+)'''
$foreignIdRegex = '\$table->foreignId\(\s*''([a-zA-Z0-9_]+)''\s*\)'
$morphsRegex = '\$table->(?:morphs|nullableMorphs)\(\s*''([a-zA-Z0-9_]+)''\s*\)'
$idNoArgRegex = '\$table->id\(\s*\)'

foreach ($f in $migrationFiles) {
    $content = Get-Content $f.FullName -Raw

    foreach ($m in [regex]::Matches($content, $createRegex)) { Ensure-Table $m.Groups[1].Value }
    foreach ($m in [regex]::Matches($content, $tableRegex)) { Ensure-Table $m.Groups[1].Value }

    $blocks = [regex]::Matches($content, $blockRegex)
    foreach ($b in $blocks) {
        $t = $b.Groups[1].Value
        $body = $b.Groups[2].Value
        Ensure-Table $t

        if ($body -match '\$table->timestamps\(') {
            Add-Col $t 'created_at'
            Add-Col $t 'updated_at'
        }
        if ($body -match '\$table->softDeletes\(') { Add-Col $t 'deleted_at' }
        if ($body -match '\$table->rememberToken\(') { Add-Col $t 'remember_token' }

        foreach ($c in [regex]::Matches($body, $colRegex)) { Add-Col $t $c.Groups[1].Value }
        foreach ($x in [regex]::Matches($body, $idNoArgRegex)) { Add-Col $t 'id' }
        foreach ($c in [regex]::Matches($body, $foreignIdRegex)) { Add-Col $t $c.Groups[1].Value }
        foreach ($m2 in [regex]::Matches($body, $morphsRegex)) {
            $base = $m2.Groups[1].Value
            Add-Col $t ("{0}_type" -f $base)
            Add-Col $t ("{0}_id" -f $base)
        }
    }
}

$missingTablesInMld = @($dbTables.Keys | Where-Object { -not $mldTables.ContainsKey($_) } | Sort-Object)
$extraTablesInMld = @($mldTables.Keys | Where-Object { -not $dbTables.ContainsKey($_) } | Sort-Object)

$columnDiffs = @()
foreach ($t in ($dbTables.Keys | Sort-Object)) {
    if (-not $mldTables.ContainsKey($t)) { continue }
    $dbCols = @($dbTables[$t])
    $mldCols = @($mldTables[$t])
    $missingCols = @($dbCols | Where-Object { $_ -notin $mldCols } | Sort-Object)
    $extraCols = @($mldCols | Where-Object { $_ -notin $dbCols } | Sort-Object)
    if ($missingCols.Count -gt 0 -or $extraCols.Count -gt 0) {
        $columnDiffs += [pscustomobject]@{ table=$t; missing_in_mld=$missingCols; extra_in_mld=$extraCols }
    }
}

[pscustomobject]@{
    db_table_count = $dbTables.Keys.Count
    mld_table_count = $mldTables.Keys.Count
    missing_tables_in_mld = $missingTablesInMld
    extra_tables_in_mld = $extraTablesInMld
    column_diffs = $columnDiffs
} | ConvertTo-Json -Depth 10
