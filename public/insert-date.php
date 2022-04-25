<?php

include 'dbconnection.php';


// One way of saving UTC string is creating it in PHP
$serverDate = date('Y-m-d H:i:s');              // e.g. "2021-12-30 04:12:18"
$date = new DateTime($serverDate);              // DateTime object with server date
$date->setTimezone(new DateTimeZone('UTC'));    // DateTime object with UTC date
$utcDate = $date->format('c');                  // e.g. "2021-12-30T12:12:18+00:00"

$sql = "INSERT INTO dates (date) VALUES (:date)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['date' => $utcDate]);

/* Another way of saving a UTC string is creating it right from PostgreSQL
$sql = "INSERT INTO dates (date) VALUES (NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([]); */

// DATES FROM DATABASE
$selectSql = '
    SELECT date,
           to_char(date, \'YYYY-MM-DD"T"HH24:MI:SS.MS"Z"\') as "dayjsutc",
           to_char(date, \'YYYY-MM-DD"T"HH24:MI:SS.MSOF:"00"\') as "formatted_utc"
    FROM dates
    ORDER BY date
';
$stmt = $pdo->query($selectSql);
$dates = $stmt->fetchAll();


header('Content-Type: application/json');
$data = [
    'status' => 200,
    'dates' => $dates
];
echo json_encode($data);
