<?php

include 'dbconnection.php';

$from = $_GET['dateFrom'];
$until = $_GET['dateUntil'];


$sql = '
    SELECT date,
           to_char(date, \'YYYY-MM-DD"T"HH24:MI:SS.MS"Z"\') as "dayjsutc"
    FROM dates
    WHERE date BETWEEN :from AND :until
    ORDER BY date
';
$stmt = $pdo->prepare($sql);
$stmt->execute([':from' => $from, ':until' => $until]);
$dates = $stmt->fetchAll();


header('Content-Type: application/json');
$data = [
    'status' => 200,
    'dates' => $dates
];
echo json_encode($data);
