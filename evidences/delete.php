<?php

session_start();

require_once "../config/database.php";


/* =====================================================
   1. VÉRIFICATION DE LA CONNEXION
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


/* =====================================================
   2. RÉCUPÉRER L'ID
===================================================== */

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id) {

    header("Location: index.php");
    exit;

}


/* =====================================================
   3. RÉCUPÉRER LA PREUVE
===================================================== */

$stmt = $pdo->prepare("

    SELECT
        id,
        file_path,
        file_name

    FROM evidences

    WHERE id = :id

");

$stmt->execute([
    ":id" => $id
]);

$evidence = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$evidence) {

    header("Location: index.php");
    exit;

}


/* =====================================================
   4. SUPPRESSION
===================================================== */

try {


    /* ===============================
       SUPPRIMER DE LA BASE DE DONNÉES
    =============================== */

    $stmt = $pdo->prepare("

        DELETE FROM evidences

        WHERE id = :id

    ");

    $stmt->execute([
        ":id" => $id
    ]);


    /* ===============================
       SUPPRIMER LE FICHIER PHYSIQUE
    =============================== */

    $filePath =
        dirname(__DIR__)
        . "/"
        . $evidence["file_path"];


    if (
        file_exists($filePath)
    ) {

        unlink($filePath);

    }


    /* ===============================
       AUDIT TRAIL
    =============================== */

    $audit = $pdo->prepare("

        INSERT INTO audit_logs

        (
            user_id,
            action,
            table_name,
            record_id,
            ip_address
        )

        VALUES

        (
            :user_id,
            :action,
            :table_name,
            :record_id,
            :ip_address
        )

    ");


    $audit->execute([

        ":user_id" =>
            $_SESSION["user_id"],

        ":action" =>
            "Suppression d'une preuve",

        ":table_name" =>
            "evidences",

        ":record_id" =>
            $id,

        ":ip_address" =>
            $_SERVER["REMOTE_ADDR"] ?? null

    ]);


    /* ===============================
       REDIRECTION
    =============================== */

    header(
        "Location: index.php?success=deleted"
    );

    exit;


}

catch (PDOException $e) {

    die(
        "Impossible de supprimer cette preuve."
    );

}