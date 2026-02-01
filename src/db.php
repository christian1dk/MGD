<?php
require_once 'config.php';

/*
    * Database forbindelse funktion
    * Returnerer et mysqli objekt eller hvis forbindelsen mislykkes, returneres null.
*/

function getDbConnection() {
    // Deaktiver automatisj PHP-fejlbeskeder for at beskytte mod læk af system-info til brugeren
    mysqli_report(MYSQLI_REPORT_OFF);

    // Vi bruger @ foran new mysqli for helt at undertrykke PHP warnings
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            return handleDbConnectionError();
        }

        return $conn;
    } catch (Exception $e) {
        return handleDbConnectionError();
    }
}

function handleDbConnectionError() {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kunne ikke oprette forbindelse til databasen.'
    ], JSON_UNESCAPED_UNICODE);
    exit;

}
?>