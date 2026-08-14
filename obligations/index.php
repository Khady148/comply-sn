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
   2. RÉCUPÉRER LES FILTRES
===================================================== */

$search = trim($_GET["search"] ?? "");

$regulation_id = filter_input(
    INPUT_GET,
    "regulation_id",
    FILTER_VALIDATE_INT
);

$criticality = $_GET["criticality"] ?? "";

$status = $_GET["status"] ?? "";


/* =====================================================
   3. PAGINATION
===================================================== */

$perPage = 20;

$page = filter_input(
    INPUT_GET,
    "page",
    FILTER_VALIDATE_INT
);

if (!$page || $page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $perPage;


/* =====================================================
   4. RÉCUPÉRER LES RÉGLEMENTATIONS
===================================================== */

$stmtRegulations = $pdo->query("
    SELECT id, title
    FROM regulations
    ORDER BY title ASC
");

$regulations = $stmtRegulations->fetchAll(
    PDO::FETCH_ASSOC
);


/* =====================================================
   5. CONSTRUIRE LES CONDITIONS DE RECHERCHE
===================================================== */

$where = [];

$params = [];


/* Recherche */

if ($search !== "") {

    $where[] = "
        (
            o.title LIKE :search
            OR o.description LIKE :search
        )
    ";

    $params[":search"] = "%" . $search . "%";
}


/* Réglementation */

if ($regulation_id) {

    $where[] = "o.regulation_id = :regulation_id";

    $params[":regulation_id"] = $regulation_id;
}


/* Criticité */

if ($criticality !== "") {

    $where[] = "o.criticality = :criticality";

    $params[":criticality"] = $criticality;
}


/* Statut */

if ($status !== "") {

    $where[] = "o.status = :status";

    $params[":status"] = $status;
}


/* =====================================================
   6. WHERE FINAL
===================================================== */

$whereSQL = "";

if (!empty($where)) {

    $whereSQL = "WHERE " . implode(
        " AND ",
        $where
    );
}


/* =====================================================
   7. COMPTER LES OBLIGATIONS
===================================================== */

$countSQL = "

    SELECT COUNT(*)

    FROM obligations o

    INNER JOIN regulations r
        ON o.regulation_id = r.id

    $whereSQL

";

$stmtCount = $pdo->prepare($countSQL);

$stmtCount->execute($params);

$total = (int) $stmtCount->fetchColumn();


/* =====================================================
   8. CALCULER LE NOMBRE DE PAGES
===================================================== */

$totalPages = max(
    1,
    (int) ceil($total / $perPage)
);


/* =====================================================
   9. CORRIGER LA PAGE
===================================================== */

if ($page > $totalPages) {

    $page = $totalPages;

    $offset = ($page - 1) * $perPage;
}


/* =====================================================
   10. RÉCUPÉRER LES OBLIGATIONS
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

    $whereSQL

    ORDER BY o.due_date ASC

    LIMIT :limit
    OFFSET :offset

";

$stmt = $pdo->prepare($sql);


/* Paramètres de recherche */

foreach ($params as $key => $value) {

    $stmt->bindValue(
        $key,
        $value
    );
}


/* LIMIT */

$stmt->bindValue(
    ":limit",
    $perPage,
    PDO::PARAM_INT
);


/* OFFSET */

$stmt->bindValue(
    ":offset",
    $offset,
    PDO::PARAM_INT
);


$stmt->execute();


$obligations = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);


/* =====================================================
   11. PARAMÈTRES DE PAGINATION
===================================================== */

$queryParams = [

    "search" => $search,

    "regulation_id" => $regulation_id,

    "criticality" => $criticality,

    "status" => $status

];

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Obligations - COMPLY-SN
    </title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f4f6f9;

        }


        /* =================================================
           HEADER
        ================================================= */

        .header {

            background: #172b4d;

            color: white;

            padding: 20px;

        }

        .header h1 {

            margin: 0 0 5px 0;

        }

        .header p {

            margin: 0;

        }


        /* =================================================
           CONTAINER
        ================================================= */

        .container {

            max-width: 1400px;

            margin: 30px auto;

            padding: 20px;

        }


        /* =================================================
           TITRE
        ================================================= */

        .top {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

            gap: 15px;

        }

        .top h2 {

            margin-bottom: 5px;

        }

        .top p {

            margin-top: 0;

            color: #666;

        }


        /* =================================================
           BOUTONS
        ================================================= */

        .btn {

            display: inline-block;

            padding: 10px 15px;

            border-radius: 6px;

            text-decoration: none;

            border: none;

            cursor: pointer;

            font-size: 14px;

            margin: 2px;

        }


        .btn-primary {

            background: #0d6efd;

            color: white;

        }


        .btn-secondary {

            background: #6c757d;

            color: white;

        }


        .btn-danger {

            background: #dc3545;

            color: white;

        }


        .btn-primary:hover {

            background: #0b5ed7;

        }


        .btn-secondary:hover {

            background: #5c636a;

        }


        /* =================================================
           FILTRES
        ================================================= */

        .filters {

            background: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .filters h3 {

            margin-top: 0;

        }


        .filter-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;

        }


        .form-group input,
        .form-group select {

            width: 100%;

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 6px;

        }


        .filter-buttons {

            margin-top: 15px;

        }


        /* =================================================
           TABLEAU
        ================================================= */

        .table-container {

            background: white;

            border-radius: 10px;

            padding: 20px;

            overflow-x: auto;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th,
        td {

            padding: 12px;

            border-bottom: 1px solid #ddd;

            text-align: left;

            vertical-align: middle;

        }


        th {

            background: #f1f3f5;

            font-weight: bold;

        }


        tr:hover {

            background: #f8f9fa;

        }


        /* =================================================
           BADGES
        ================================================= */

        .badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

            white-space: nowrap;

        }


        .faible {

            background: #d1e7dd;

            color: #0f5132;

        }


        .moyenne {

            background: #fff3cd;

            color: #664d03;

        }


        .elevee {

            background: #ffe5d0;

            color: #984c0c;

        }


        .critique {

            background: #f8d7da;

            color: #842029;

        }


        .conforme {

            background: #d1e7dd;

            color: #0f5132;

        }


        .non-conforme {

            background: #f8d7da;

            color: #842029;

        }


        .en-cours {

            background: #cfe2ff;

            color: #084298;

        }


        .a-verifier {

            background: #fff3cd;

            color: #664d03;

        }


        /* =================================================
           PAGINATION
        ================================================= */

        .pagination {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 5px;

            margin-top: 25px;

            flex-wrap: wrap;

        }


        .pagination a {

            display: inline-block;

            padding: 8px 12px;

            border: 1px solid #ddd;

            border-radius: 5px;

            text-decoration: none;

            color: #0d6efd;

            background: white;

        }


        .pagination a:hover {

            background: #e9ecef;

        }


        .pagination .active {

            background: #0d6efd;

            color: white;

            border-color: #0d6efd;

        }


        .pagination .disabled {

            color: #999;

            background: #eee;

            pointer-events: none;

            padding: 8px 12px;

            border-radius: 5px;

        }


        .result-count {

            margin-bottom: 15px;

            color: #555;

        }


        .empty {

            text-align: center;

            padding: 30px;

            color: #666;

        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 1000px) {

            .filter-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 700px) {

            .top {

                flex-direction: column;

                align-items: flex-start;

            }

            .filter-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<div class="header">

    <h1>
        COMPLY-SN
    </h1>

    <p>
        Plateforme de gestion de la conformité réglementaire
    </p>

</div>


<div class="container">


    <!-- =================================================
         TITRE + BOUTONS
    ================================================= -->

    <div class="top">

        <div>

            <h2>
                Obligations réglementaires
            </h2>

            <p>
                Recherche, filtrage et suivi des obligations.
            </p>

        </div>


        <div>

            <!-- TABLEAU DE BORD -->

            <a
                href="../dashboard.php"
                class="btn btn-secondary"
            >
                🏠 Tableau de bord
            </a>


            <!-- NOUVELLE OBLIGATION -->

            <a
                href="create.php"
                class="btn btn-primary"
            >
                ➕ Nouvelle obligation
            </a>


            <!-- =================================================
                 EXPORT CSV
            ================================================= -->

            <a
                href="export_csv.php"
                class="btn btn-primary"
            >
                📊 Exporter CSV
            </a>


            <!-- =================================================
                 EXPORT PDF
            ================================================= -->

            <a
                href="export_pdf.php"
                class="btn btn-primary"
            >
                📄 Exporter PDF
            </a>

        </div>

    </div>


    <!-- =================================================
         FILTRES
    ================================================= -->

    <div class="filters">

        <h3>
            🔎 Recherche et filtres
        </h3>


        <form
            method="GET"
            action="index.php"
        >

            <div class="filter-grid">


                <!-- RECHERCHE -->

                <div class="form-group">

                    <label for="search">
                        Recherche
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        placeholder="Titre ou description..."
                        value="<?= htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >

                </div>


                <!-- RÉGLEMENTATION -->

                <div class="form-group">

                    <label for="regulation_id">
                        Réglementation
                    </label>


                    <select
                        id="regulation_id"
                        name="regulation_id"
                    >

                        <option value="">
                            Toutes les réglementations
                        </option>


                        <?php foreach (
                            $regulations
                            as $regulation
                        ): ?>

                            <option
                                value="<?= (int)
                                    $regulation["id"] ?>"

                                <?= (
                                    $regulation_id ==
                                    $regulation["id"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $regulation["title"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>


                <!-- CRITICITÉ -->

                <div class="form-group">

                    <label for="criticality">
                        Criticité
                    </label>


                    <select
                        id="criticality"
                        name="criticality"
                    >

                        <option value="">
                            Toutes
                        </option>


                        <option
                            value="Faible"
                            <?= $criticality === "Faible"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Faible
                        </option>


                        <option
                            value="Moyenne"
                            <?= $criticality === "Moyenne"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Moyenne
                        </option>


                        <option
                            value="Élevée"
                            <?= $criticality === "Élevée"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Élevée
                        </option>


                        <option
                            value="Critique"
                            <?= $criticality === "Critique"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Critique
                        </option>


                    </select>

                </div>


                <!-- STATUT -->

                <div class="form-group">

                    <label for="status">
                        Statut
                    </label>


                    <select
                        id="status"
                        name="status"
                    >

                        <option value="">
                            Tous
                        </option>


                        <option
                            value="Conforme"
                            <?= $status === "Conforme"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Conforme
                        </option>


                        <option
                            value="Non conforme"
                            <?= $status === "Non conforme"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Non conforme
                        </option>


                        <option
                            value="En cours"
                            <?= $status === "En cours"
                                ? "selected"
                                : ""
                            ?>
                        >
                            En cours
                        </option>


                        <option
                            value="À vérifier"
                            <?= $status === "À vérifier"
                                ? "selected"
                                : ""
                            ?>
                        >
                            À vérifier
                        </option>


                    </select>

                </div>


            </div>


            <div class="filter-buttons">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    🔎 Rechercher
                </button>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    🔄 Réinitialiser
                </a>

            </div>

        </form>

    </div>


    <!-- =================================================
         NOMBRE DE RÉSULTATS
    ================================================= -->

    <div class="result-count">

        <strong>
            <?= $total ?>
        </strong>

        obligation(s) trouvée(s).


        <?php if ($total > 0): ?>

            Page

            <strong>
                <?= $page ?>
            </strong>

            sur

            <strong>
                <?= $totalPages ?>
            </strong>

        <?php endif; ?>

    </div>


    <!-- =================================================
         TABLEAU
    ================================================= -->

    <div class="table-container">


        <?php if (empty($obligations)): ?>


            <div class="empty">

                Aucune obligation ne correspond
                à votre recherche.

            </div>


        <?php else: ?>


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

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach (
                    $obligations
                    as $obligation
                ): ?>


                    <tr>


                        <td>

                            <?= (int)
                                $obligation["id"]
                            ?>

                        </td>


                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $obligation["title"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>

                            </strong>

                            <br>

                            <small>

                                <?= htmlspecialchars(
                                    $obligation["description"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>

                            </small>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $obligation["regulation_title"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $obligation["frequency"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $obligation["due_date"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <!-- CRITICITÉ -->

                        <td>

                            <?php

                            $criticalityClass = match (
                                $obligation["criticality"]
                            ) {

                                "Faible" =>
                                    "faible",

                                "Moyenne" =>
                                    "moyenne",

                                "Élevée" =>
                                    "elevee",

                                "Critique" =>
                                    "critique",

                                default =>
                                    ""

                            };

                            ?>


                            <span
                                class="badge
                                <?= $criticalityClass ?>"
                            >

                                <?= htmlspecialchars(
                                    $obligation["criticality"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>

                            </span>

                        </td>


                        <!-- STATUT -->

                        <td>

                            <?php

                            $statusClass = match (
                                $obligation["status"]
                            ) {

                                "Conforme" =>
                                    "conforme",

                                "Non conforme" =>
                                    "non-conforme",

                                "En cours" =>
                                    "en-cours",

                                "À vérifier" =>
                                    "a-verifier",

                                default =>
                                    ""

                            };

                            ?>


                            <span
                                class="badge
                                <?= $statusClass ?>"
                            >

                                <?= htmlspecialchars(
                                    $obligation["status"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>

                            </span>

                        </td>


                        <!-- RESPONSABLE -->

                        <td>

                            <?= htmlspecialchars(
                                $obligation["responsible_name"]
                                ?? "Non attribué",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </td>


                        <!-- ACTIONS -->

                        <td>

                            <a
                                href="edit.php?id=<?= (int)
                                    $obligation["id"] ?>"
                                class="btn btn-primary"
                            >
                                ✏️ Modifier
                            </a>


                            <a
                                href="delete.php?id=<?= (int)
                                    $obligation["id"] ?>"
                                class="btn btn-danger"

                                onclick="
                                    return confirm(
                                        'Voulez-vous vraiment supprimer cette obligation ?'
                                    );
                                "
                            >
                                🗑️ Supprimer
                            </a>

                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>

            </table>


        <?php endif; ?>


    </div>


    <!-- =================================================
         PAGINATION
    ================================================= -->

    <?php if ($totalPages > 1): ?>


        <div class="pagination">


            <!-- PRÉCÉDENT -->

            <?php

            $previousParams = array_merge(
                $queryParams,
                [
                    "page" => $page - 1
                ]
            );

            ?>


            <?php if ($page > 1): ?>

                <a
                    href="?<?= http_build_query(
                        $previousParams
                    ) ?>"
                >
                    « Précédent
                </a>

            <?php else: ?>

                <span class="disabled">
                    « Précédent
                </span>

            <?php endif; ?>


            <!-- NUMÉROS DE PAGE -->

            <?php for (
                $i = 1;
                $i <= $totalPages;
                $i++
            ): ?>


                <?php

                $pageParams = array_merge(
                    $queryParams,
                    [
                        "page" => $i
                    ]
                );

                ?>


                <a
                    href="?<?= http_build_query(
                        $pageParams
                    ) ?>"

                    class="<?= (
                        $i == $page
                    )
                        ? "active"
                        : ""
                    ?>"
                >

                    <?= $i ?>

                </a>


            <?php endfor; ?>


            <!-- SUIVANT -->

            <?php

            $nextParams = array_merge(
                $queryParams,
                [
                    "page" => $page + 1
                ]
            );

            ?>


            <?php if (
                $page < $totalPages
            ): ?>

                <a
                    href="?<?= http_build_query(
                        $nextParams
                    ) ?>"
                >
                    Suivant »
                </a>

            <?php else: ?>

                <span class="disabled">
                    Suivant »
                </span>

            <?php endif; ?>


        </div>


    <?php endif; ?>


</div>

</body>

</html>