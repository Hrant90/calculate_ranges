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
$endDate = '2024-10-03';

//if cache exists get data from cache else do query

$sql = "
    SELECT result, daily_count, date
    FROM results_count
    WHERE date BETWEEN :startDate AND :endDate AND site_id = 1
    ORDER BY date
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
$rows = $stmt->fetchAll();

// store rows in cache

$ranges = [
    'range 1-3' => range(1, 3),
    'range 4-8' => range(4, 8),
    'range 9-20' => range(9, 20),
    'range 21-100' => range(21, 100)
];

//calculate ranges dynamically every time

$countsByDate = [];

foreach ($rows as $row) {

    if (!isset($countsByDate[$row['date']])) {
        $countsByDate[$row['date']] = [
            'range 1-3' => 0,
            'range 4-8' => 0,
            'range 9-20' => 0,
            'range 21-100' => 0
        ];
    }

    foreach ($ranges as $rangeKey => $rangeArray) {
        if (in_array($row['result'], $rangeArray)) {
            $countsByDate[$row['date']][$rangeKey] += $row['daily_count'];
            break;
        }
    }
}

print_r($countsByDate);
