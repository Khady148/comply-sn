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
   2. RÉCUPÉRER L'ID DU CONTRÔLE
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
   3. VÉRIFIER QUE LE CONTRÔLE EXISTE
===================================================== */

$stmt = $pdo->prepare("
    SELECT id
    FROM controls
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$control = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$control) {

    header("Location: index.php");
    exit;

}


/* =====================================================
   4. SUPPRESSION
===================================================== */

try {

    $stmt = $pdo->prepare("
        DELETE FROM controls
        WHERE id = :id
    ");

    $stmt->execute([
        ":id" => $id
    ]);


    /* =================================================
       5. JOURNAL D'AUDIT
    ================================================= */

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
            "Suppression d'un contrôle",

        ":table_name" =>
            "controls",

        ":record_id" =>
            $id,

        ":ip_address" =>
            $_SERVER["REMOTE_ADDR"] ?? null

    ]);


    /* =================================================
       6. REDIRECTION
    ================================================= */

    header(
        "Location: index.php?success=deleted"
    );

    exit;


} catch (PDOException $e) {

    die(
        "Impossible de supprimer ce contrôle."
    );

}