<?php

include 'dbconnection.php';

$from = $_GET['dateFrom'];
$until = $_GET['dateUntil'];
$timezone = $_GET['timezone'];

$sql = '
    SELECT  date,
    		to_char(date, \'YYYY-MM-DD"T"HH24:MI:SS.MSOF:"00"\') as "formatted_utc"
    FROM dates
    WHERE TO_CHAR(date at time zone :timezone, \'yyyy-mm-dd HH24:MI\') BETWEEN :from AND :until
    ORDER BY date
';
$stmt = $pdo->prepare($sql);
$stmt->execute([':from' => $from, ':until' => $until, ':timezone' => $timezone]);
$dates = $stmt->fetchAll();


header('Content-Type: application/json');
$data = [
    'status' => 200,
    'dates' => $dates
];
echo json_encode($data);
