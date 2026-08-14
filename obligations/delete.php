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
   2. VÉRIFICATION DE L'ID
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
   3. RÉCUPÉRER L'OBLIGATION
===================================================== */

$stmt = $pdo->prepare("
    SELECT id, title
    FROM obligations
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$obligation = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$obligation) {

    header("Location: index.php");

    exit;
}


/* =====================================================
   4. SUPPRESSION
===================================================== */

try {

    $pdo->beginTransaction();


    /* ---------------------------------------------
       JOURNAL D'AUDIT AVANT SUPPRESSION
    --------------------------------------------- */

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
            "Suppression d'une obligation",

        ":table_name" =>
            "obligations",

        ":record_id" =>
            $id,

        ":ip_address" =>
            $_SERVER["REMOTE_ADDR"] ?? null
    ]);


    /* ---------------------------------------------
       SUPPRESSION
    --------------------------------------------- */

    $stmt = $pdo->prepare("
        DELETE FROM obligations
        WHERE id = :id
    ");

    $stmt->execute([
        ":id" => $id
    ]);


    $pdo->commit();


    /* ---------------------------------------------
       REDIRECTION
    --------------------------------------------- */

    header(
        "Location: index.php?success=deleted"
    );

    exit;


} catch (PDOException $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    die(
        "Erreur lors de la suppression de l'obligation."
    );
}

?>