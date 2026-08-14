<?php

session_start();

require_once "../config/database.php";
require_once "../fpdf/fpdf.php";

/* =====================================================
   VÉRIFICATION DE LA CONNEXION
===================================================== */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}


/* =====================================================
   RÉCUPÉRER LES OBLIGATIONS
===================================================== */

$sql = "
    SELECT
        o.id,
        o.title,
        o.frequency,
        o.due_date,
        o.criticality,
        o.status,
        r.title AS regulation_title,
        u.full_name AS responsible_name

    FROM obligations o

    INNER JOIN regulations r
        ON o.regulation_id = r.id

    LEFT JOIN users u
        ON o.responsible_user_id = u.id

    ORDER BY o.due_date ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$obligations = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   CRÉATION DU PDF
===================================================== */

$pdf = new FPDF("L", "mm", "A4");

$pdf->AddPage();

$pdf->SetAutoPageBreak(true, 15);


/* =====================================================
   TITRE
===================================================== */

$pdf->SetFont("Arial", "B", 18);

$pdf->Cell(
    0,
    12,
    utf8_decode("COMPLY-SN"),
    0,
    1,
    "C"
);


$pdf->SetFont("Arial", "B", 14);

$pdf->Cell(
    0,
    10,
    utf8_decode("État des obligations réglementaires"),
    0,
    1,
    "C"
);


/* =====================================================
   DATE D'ÉDITION
===================================================== */

$pdf->SetFont("Arial", "", 9);

$dateEdition = date("d/m/Y H:i");

$pdf->Cell(
    0,
    8,
    utf8_decode("Date d'édition : " . $dateEdition),
    0,
    1,
    "R"
);


$pdf->Ln(5);


/* =====================================================
   INFORMATIONS
===================================================== */

$pdf->SetFont("Arial", "", 10);

$pdf->Cell(
    0,
    7,
    utf8_decode(
        "Nombre total d'obligations : "
        . count($obligations)
    ),
    0,
    1
);

$pdf->Ln(3);


/* =====================================================
   EN-TÊTE DU TABLEAU
===================================================== */

$pdf->SetFont("Arial", "B", 8);


/*
 * Largeurs des colonnes
 */

$widths = [
    10,  // ID
    55,  // Obligation
    48,  // Réglementation
    25,  // Fréquence
    28,  // Échéance
    27,  // Criticité
    30,  // Statut
    45   // Responsable
];


$headers = [
    "ID",
    "Obligation",
    "Réglementation",
    "Fréquence",
    "Échéance",
    "Criticité",
    "Statut",
    "Responsable"
];


foreach ($headers as $i => $header) {

    $pdf->Cell(
        $widths[$i],
        10,
        utf8_decode($header),
        1,
        0,
        "C"
    );
}

$pdf->Ln();


/* =====================================================
   CONTENU DU TABLEAU
===================================================== */

$pdf->SetFont("Arial", "", 7.5);


foreach ($obligations as $obligation) {

    /*
     * Nettoyage du texte
     */

    $title = utf8_decode(
        $obligation["title"]
    );

    $regulation = utf8_decode(
        $obligation["regulation_title"]
    );

    $frequency = utf8_decode(
        $obligation["frequency"]
    );

    $criticality = utf8_decode(
        $obligation["criticality"]
    );

    $status = utf8_decode(
        $obligation["status"]
    );

    $responsible = utf8_decode(
        $obligation["responsible_name"]
        ?? "Non attribué"
    );


    /* ============================
       ID
    ============================ */

    $pdf->Cell(
        $widths[0],
        9,
        $obligation["id"],
        1,
        0,
        "C"
    );


    /* ============================
       OBLIGATION
    ============================ */

    $pdf->Cell(
        $widths[1],
        9,
        $title,
        1,
        0,
        "L"
    );


    /* ============================
       RÉGLEMENTATION
    ============================ */

    $pdf->Cell(
        $widths[2],
        9,
        $regulation,
        1,
        0,
        "L"
    );


    /* ============================
       FRÉQUENCE
    ============================ */

    $pdf->Cell(
        $widths[3],
        9,
        $frequency,
        1,
        0,
        "C"
    );


    /* ============================
       ÉCHÉANCE
    ============================ */

    $date = date(
        "d/m/Y",
        strtotime($obligation["due_date"])
    );

    $pdf->Cell(
        $widths[4],
        9,
        $date,
        1,
        0,
        "C"
    );


    /* ============================
       CRITICITÉ
    ============================ */

    $pdf->Cell(
        $widths[5],
        9,
        $criticality,
        1,
        0,
        "C"
    );


    /* ============================
       STATUT
    ============================ */

    $pdf->Cell(
        $widths[6],
        9,
        $status,
        1,
        0,
        "C"
    );


    /* ============================
       RESPONSABLE
    ============================ */

    $pdf->Cell(
        $widths[7],
        9,
        $responsible,
        1,
        0,
        "L"
    );


    $pdf->Ln();
}


/* =====================================================
   PIED DE PAGE
===================================================== */

$pdf->Ln(8);

$pdf->SetFont("Arial", "I", 8);

$pdf->Cell(
    0,
    6,
    utf8_decode(
        "Document généré automatiquement par la plateforme COMPLY-SN."
    ),
    0,
    1,
    "C"
);


/* =====================================================
   ENVOYER LE PDF AU NAVIGATEUR
===================================================== */

$pdf->Output(
    "D",
    "etat_obligations_comply_sn.pdf"
);

exit;