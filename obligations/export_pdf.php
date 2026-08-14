<?php

session_start();

/* =====================================================
   VÉRIFICATION DE LA CONNEXION
===================================================== */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}


/* =====================================================
   CONNEXION À LA BASE DE DONNÉES
===================================================== */

require_once "../config/database.php";


/* =====================================================
   CHARGEMENT DE DOMPDF
===================================================== */

require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;


/* =====================================================
   CONFIGURATION DOMPDF
===================================================== */

$options = new Options();

$options->set(
    "isRemoteEnabled",
    true
);

$options->set(
    "defaultFont",
    "DejaVu Sans"
);


$dompdf = new Dompdf($options);


/* =====================================================
   RÉCUPÉRATION DES OBLIGATIONS
===================================================== */

$sql = "

    SELECT

        o.id,
        o.title,
        o.description,
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

$obligations = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);


/* =====================================================
   DATE DU RAPPORT
===================================================== */

$dateRapport = date("d/m/Y");


/* =====================================================
   CRÉATION DU HTML
===================================================== */

$html = '

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<style>

@page {

    margin: 25px;

}


body {

    font-family: DejaVu Sans, sans-serif;

    font-size: 10px;

    color: #222;

}


.header {

    text-align: center;

    margin-bottom: 20px;

}


.header h1 {

    margin: 0;

    font-size: 22px;

}


.header h2 {

    margin: 5px 0;

    font-size: 14px;

    font-weight: normal;

}


.date {

    text-align: right;

    font-size: 9px;

    margin-bottom: 15px;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th {

    background-color: #172b4d;

    color: white;

    padding: 7px;

    border: 1px solid #999;

    font-size: 9px;

}


td {

    padding: 6px;

    border: 1px solid #aaa;

    font-size: 8px;

    vertical-align: top;

}


.footer {

    margin-top: 20px;

    text-align: center;

    font-size: 8px;

    color: #666;

}


</style>

</head>


<body>


<div class="header">

    <h1>COMPLY-SN</h1>

    <h2>
        Rapport de conformité réglementaire
    </h2>

</div>


<div class="date">

    Date du rapport :
    ' . $dateRapport . '

</div>


<table>

<thead>

<tr>

    <th>ID</th>

    <th>Obligation</th>

    <th>Réglementation</th>

    <th>Fréquence</th>

    <th>Échéance</th>

    <th>Criticité</th>

    <th>Statut</th>

    <th>Responsable</th>

</tr>

</thead>


<tbody>
';


/* =====================================================
   AJOUT DES OBLIGATIONS AU PDF
===================================================== */

foreach ($obligations as $obligation) {

    $html .= '

    <tr>

        <td>
            ' . (int)$obligation["id"] . '
        </td>


        <td>

            <strong>
                ' .
                htmlspecialchars(
                    $obligation["title"],
                    ENT_QUOTES,
                    "UTF-8"
                )
                . '
            </strong>

            <br>

            ' .
            htmlspecialchars(
                $obligation["description"],
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>


        <td>

            ' .
            htmlspecialchars(
                $obligation["regulation_title"],
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>


        <td>

            ' .
            htmlspecialchars(
                $obligation["frequency"],
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>


        <td>

            ' .
            htmlspecialchars(
                $obligation["due_date"],
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>


        <td>

            ' .
            htmlspecialchars(
                $obligation["criticality"],
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>


        <td>

            ' .
            htmlspecialchars(
                $obligation["status"],
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>


        <td>

            ' .
            htmlspecialchars(
                $obligation["responsible_name"]
                ?? "Non attribué",
                ENT_QUOTES,
                "UTF-8"
            )
            . '

        </td>

    </tr>

    ';
}


$html .= '

</tbody>

</table>


<div class="footer">

    COMPLY-SN -
    Plateforme web de gestion de la conformité réglementaire

</div>


</body>

</html>

';


/* =====================================================
   GÉNÉRATION DU PDF
===================================================== */

$dompdf->loadHtml($html);


/* Format A4 */

$dompdf->setPaper(
    "A4",
    "landscape"
);


/* Rendu */

$dompdf->render();


/* =====================================================
   TÉLÉCHARGEMENT DU PDF
===================================================== */

$dompdf->stream(

    "rapport_conformite_COMPLY-SN.pdf",

    [
        "Attachment" => true
    ]

);

exit;