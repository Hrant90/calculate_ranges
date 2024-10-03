<?php

$host = 'localhost';
$db = '';
$user = '';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

$startDate = '2024-10-01';
$endDate = '2024-10-04';

// get result from cache by ranges if it exists otherwise do query

// get ranges from config
$ranges = [
    'range_1_3' => [1, 3],
    'range_4_8' => [4, 8],
    'range_9_20' => [9, 20],
    'range_21_100' => [21, 100]
];

$sql = "
    SELECT 
        date,";

foreach ($ranges as $rangeKey => $range) {
    $sql .= "SUM(CASE WHEN result BETWEEN {$range[0]} AND {$range[1]} THEN daily_count ELSE 0 END) AS $rangeKey,";
}

$sql = rtrim($sql, ",");

$sql .= "
    FROM results_count
    WHERE date BETWEEN :startDate AND :endDate
    AND site_id = 1
    GROUP BY date
    ORDER BY date;
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);

$rows = $stmt->fetchAll();
// store rows in cache by ranges
// every time when user changed ranges we need to refresh cache
print_r($rows);
