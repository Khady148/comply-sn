<?php

session_start();

/* =====================================================
   1. VÉRIFICATION DE LA CONNEXION
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: auth/login.php");
    exit;

}


/* =====================================================
   2. CONNEXION À LA BASE DE DONNÉES
===================================================== */

require_once "config/database.php";


/* =====================================================
   3. CHARGEMENT DU JOURNAL D'AUDIT
===================================================== */

require_once "config/audit.php";


/* =====================================================
   4. ENREGISTRER LA CONSULTATION DU DASHBOARD
===================================================== */

logAudit(
    $pdo,
    (int) $_SESSION["user_id"],
    "CONSULTATION DU TABLEAU DE BORD",
    "dashboard",
    null
);


/* =====================================================
   5. INFORMATIONS DE L'UTILISATEUR CONNECTÉ
===================================================== */

$userName = $_SESSION["full_name"] ?? "Utilisateur";

$userRole = $_SESSION["role"] ?? "standard";


/* =====================================================
   6. NOMBRE D'UTILISATEURS
===================================================== */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM users
");

$totalUsers = (int) $stmt->fetchColumn();


/* =====================================================
   7. NOMBRE DE DOMAINES
===================================================== */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM domains
");

$totalDomains = (int) $stmt->fetchColumn();


/* =====================================================
   8. NOMBRE DE RÉGLEMENTATIONS
===================================================== */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM regulations
");

$totalRegulations = (int) $stmt->fetchColumn();


/* =====================================================
   9. NOMBRE D'OBLIGATIONS
===================================================== */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM obligations
");

$totalObligations = (int) $stmt->fetchColumn();


/* =====================================================
   10. NOMBRE DE CONTRÔLES
===================================================== */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM controls
");

$totalControls = (int) $stmt->fetchColumn();


/* =====================================================
   11. NOMBRE D'ACTIONS CORRECTIVES
===================================================== */

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM corrective_actions
");

$totalActions = (int) $stmt->fetchColumn();


/* =====================================================
   12. STATISTIQUES DES OBLIGATIONS PAR STATUT
===================================================== */

$stmt = $pdo->query("
    SELECT
        status,
        COUNT(*) AS total
    FROM obligations
    GROUP BY status
");

$statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   13. PRÉPARER LES DONNÉES DU GRAPHIQUE
===================================================== */

$labels = [];

$values = [];


foreach ($statusData as $row) {

    $labels[] = $row["status"];

    $values[] = (int) $row["total"];

}


/* =====================================================
   14. CONVERSION POUR JAVASCRIPT
===================================================== */

$labelsJson = json_encode(
    $labels,
    JSON_UNESCAPED_UNICODE
);

$valuesJson = json_encode(
    $values
);

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
        Tableau de bord - COMPLY-SN
    </title>


    <!-- =================================================
         CHART.JS
    ================================================= -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


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

            padding: 20px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 15px;

        }


        .header h1 {

            margin: 0;

        }


        .user-info {

            text-align: right;

        }


        .user-info p {

            margin: 4px 0;

        }


        .logout {

            display: inline-block;

            margin-top: 5px;

            padding: 8px 14px;

            background: #dc3545;

            color: white;

            text-decoration: none;

            border-radius: 5px;

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
           BIENVENUE
        ================================================= */

        .welcome {

            background: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 25px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .welcome h2 {

            margin-top: 0;

        }


        /* =================================================
           CARTES
        ================================================= */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 30px;

        }


        .card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .card h3 {

            margin-top: 0;

            color: #666;

            font-size: 16px;

        }


        .number {

            font-size: 35px;

            font-weight: bold;

            color: #172b4d;

        }


        /* =================================================
           MENU
        ================================================= */

        .menu {

            background: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 30px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .menu h2 {

            margin-top: 0;

        }


        .menu-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 10px;

        }


        .menu a {

            display: block;

            padding: 12px;

            background: #f1f3f5;

            color: #172b4d;

            text-decoration: none;

            border-radius: 6px;

            text-align: center;

        }


        .menu a:hover {

            background: #e2e6ea;

        }


        /* =================================================
           GRAPHIQUE
        ================================================= */

        .chart-box {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .chart-box h2 {

            margin-top: 0;

        }


        .chart-container {

            position: relative;

            height: 400px;

        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 900px) {

            .cards {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .menu-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 600px) {

            .header {

                flex-direction: column;

                align-items: flex-start;

            }


            .user-info {

                text-align: left;

            }


            .cards {

                grid-template-columns: 1fr;

            }


            .menu-grid {

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


    <div>

        <h1>
            COMPLY-SN
        </h1>

        <p>
            Plateforme de gestion de la conformité réglementaire
        </p>

    </div>


    <div class="user-info">

        <p>

            👤

            <strong>

                <?= htmlspecialchars(
                    $userName,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </strong>

        </p>


        <p>

            Rôle :

            <strong>

                <?= htmlspecialchars(
                    $userRole,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </strong>

        </p>


        <a
            href="auth/logout.php"
            class="logout"
        >
            🚪 Déconnexion
        </a>

    </div>


</div>



<div class="container">


    <!-- =================================================
         BIENVENUE
    ================================================= -->

    <div class="welcome">

        <h2>
            Tableau de bord
        </h2>

        <p>

            Bienvenue dans la plateforme
            <strong>COMPLY-SN</strong>.

        </p>

        <p>

            Cette plateforme permet de suivre les
            obligations réglementaires, les contrôles
            et les actions correctives.

        </p>

    </div>



    <!-- =================================================
         CARTES STATISTIQUES
    ================================================= -->

    <div class="cards">


        <div class="card">

            <h3>
                👥 Utilisateurs
            </h3>

            <div class="number">

                <?= $totalUsers ?>

            </div>

        </div>


        <div class="card">

            <h3>
                🌐 Domaines
            </h3>

            <div class="number">

                <?= $totalDomains ?>

            </div>

        </div>


        <div class="card">

            <h3>
                📚 Réglementations
            </h3>

            <div class="number">

                <?= $totalRegulations ?>

            </div>

        </div>


        <div class="card">

            <h3>
                📋 Obligations
            </h3>

            <div class="number">

                <?= $totalObligations ?>

            </div>

        </div>


        <div class="card">

            <h3>
                🔍 Contrôles
            </h3>

            <div class="number">

                <?= $totalControls ?>

            </div>

        </div>


        <div class="card">

            <h3>
                🛠️ Actions correctives
            </h3>

            <div class="number">

                <?= $totalActions ?>

            </div>

        </div>


    </div>



    <!-- =================================================
         MENU
    ================================================= -->

    <div class="menu">

        <h2>
            📌 Modules
        </h2>


        <div class="menu-grid">


            <a href="domains/index.php">
                🌐 Domaines
            </a>


            <a href="regulations/index.php">
                📚 Réglementations
            </a>


            <a href="obligations/index.php">
                📋 Obligations
            </a>


            <a href="controls/index.php">
                🔍 Contrôles
            </a>


            <a href="corrective_actions/index.php">
                🛠️ Actions correctives
            </a>


            <a href="evidences/index.php">
                📎 Preuves
            </a>


            <?php if ($userRole === "admin"): ?>

                <a href="users/index.php">
                    👥 Utilisateurs
                </a>

            <?php endif; ?>


        </div>

    </div>



    <!-- =================================================
         GRAPHIQUE
    ================================================= -->

    <div class="chart-box">

        <h2>
            📊 État des obligations
        </h2>


        <div class="chart-container">

            <canvas id="obligationsChart"></canvas>

        </div>

    </div>


</div>



<!-- =====================================================
     CHART.JS
===================================================== -->

<script>

const labels = <?= $labelsJson ?>;

const values = <?= $valuesJson ?>;


const ctx =
    document
        .getElementById(
            "obligationsChart"
        )
        .getContext("2d");


new Chart(
    ctx,
    {

        type: "bar",

        data: {

            labels: labels,

            datasets: [

                {

                    label:
                        "Nombre d'obligations",

                    data: values,

                    borderWidth: 1

                }

            ]

        },


        options: {

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0

                    }

                }

            }

        }

    }

);

</script>


</body>

</html>