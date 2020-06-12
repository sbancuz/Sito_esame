<?php 
$address = 'localhost';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$address;dbname=esame", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statemet = $pdo->prepare('INSERT  INTO '.$_GET['tabella'].'(Country, year, '.$_GET['campo'].')
    VALUES (:c, :y, :d)  ON DUPLICATE KEY UPDATE '.$_GET['campo'].'=:d');

    $file = fopen("ciao.csv", 'r');
    while ($line = fgetcsv($file)) {
        $data[] = $line;
    }
    fclose($file);
    set_time_limit(60*10);
    foreach ($data as $row) {
        try {
            $statemet->execute(['c' => $row[0],'y' => $row[1],'d' => $row[2]]);
        } catch (PDOException $e) {
            print_r($e);
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}