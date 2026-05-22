<?php
$host = '127.0.0.1';
$db   = 'project_sewa_mobil';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    // store in $GLOBALS so it's available even if this file is included inside a function
    $GLOBALS['pdo'] = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If DB doesn't exist yet, try to create it from the SQL file
    try {
        $dsnNoDb = "mysql:host=$host;charset=$charset";
        $tmpPdo = new PDO($dsnNoDb, $user, $pass, $options);

        // create database
        $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE ${charset}_unicode_ci");

        // try to load schema file
        $sqlFile = __DIR__ . '/../db/sewa_mobil.sql';
        if (is_file($sqlFile) && is_readable($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            // Execute whole SQL; exec can run multiple statements for MySQL
            $tmpPdo->exec($sql);
        }

        // finally connect to the new DB
        $GLOBALS['pdo'] = new PDO($dsn, $user, $pass, $options);
    } catch (Exception $ex) {
        // In production, avoid exposing DB error details. Throw for development.
        throw new RuntimeException('DB connection/setup failed: ' . $ex->getMessage());
    }
}

function getPDO() {
    return $GLOBALS['pdo'] ?? null;
}

?>
