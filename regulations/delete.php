<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}


/* Récupérer l'ID */

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: index.php");
    exit;
}


/* Supprimer */

$stmt = $pdo->prepare("
    DELETE FROM regulations
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);


/* Retour à la liste */

header("Location: index.php");

exit;