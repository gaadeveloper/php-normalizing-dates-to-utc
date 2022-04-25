<?php

include 'dbconnection.php';

// DATES FROM DATABASE
$selectSql = '
    SELECT date,
           to_char(date, \'YYYY-MM-DD"T"HH24:MI:SS.MS"Z"\') as "dayjsutc"
    FROM dates
    ORDER BY date
';
$stmt = $pdo->query($selectSql);
$dates = $stmt->fetchAll();

// POSSIBLE TIMEZONES
$timezone_identifiers = DateTimeZone::listIdentifiers();


header('Content-Type: application/json');
$data = [
    'status' => 200,
    'dates' => $dates,
    'availableTimezones' => $timezone_identifiers
];
echo json_encode($data);
