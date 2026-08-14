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
   2. RÉCUPÉRER LES CONTRÔLES
===================================================== */

$stmt = $pdo->query("

    SELECT
        c.id,
        c.control_date,
        c.result,
        c.comment,

        o.title AS obligation_title,

        u.full_name AS controller_name

    FROM controls c

    INNER JOIN obligations o
        ON c.obligation_id = o.id

    INNER JOIN users u
        ON c.controlled_by = u.id

    ORDER BY c.control_date DESC

");

$controls = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Contrôles - COMPLY-SN
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


        /* ===============================
           HEADER
        =============================== */

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


        /* ===============================
           CONTAINER
        =============================== */

        .container {

            max-width: 1200px;

            margin: 30px auto;

            padding: 20px;

        }


        /* ===============================
           TOP
        =============================== */

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


        /* ===============================
           BOUTONS
        =============================== */

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


        .btn-danger:hover {

            background: #bb2d3b;

        }


        /* ===============================
           TABLEAU
        =============================== */

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


        /* ===============================
           BADGES DES RÉSULTATS
        =============================== */

        .badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

            white-space: nowrap;

        }


        .conforme {

            background: #d1e7dd;

            color: #0f5132;

        }


        .non-conforme {

            background: #f8d7da;

            color: #842029;

        }


        .partiel {

            background: #fff3cd;

            color: #664d03;

        }


        /* ===============================
           MESSAGE VIDE
        =============================== */

        .empty {

            text-align: center;

            padding: 30px;

            color: #666;

        }


        /* ===============================
           MESSAGE SUCCÈS
        =============================== */

        .success {

            background: #d1e7dd;

            color: #0f5132;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        /* ===============================
           RESPONSIVE
        =============================== */

        @media (max-width: 800px) {

            .top {

                flex-direction: column;

                align-items: flex-start;

            }

        }


        @media (max-width: 600px) {

            .container {

                margin: 15px;

                padding: 10px;

            }


            .btn {

                font-size: 13px;

                padding: 8px 10px;

            }


            th,
            td {

                padding: 8px;

                font-size: 13px;

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
        Gestion des contrôles de conformité
    </p>

</div>


<!-- =====================================================
     CONTENU PRINCIPAL
===================================================== -->

<div class="container">


    <!-- ===============================
         TITRE ET BOUTONS
    =============================== -->

    <div class="top">

        <div>

            <h2>
                Contrôles
            </h2>

            <p>
                Liste des contrôles de conformité
            </p>

        </div>


        <div>

            <a
                href="../dashboard.php"
                class="btn btn-secondary"
            >
                Retour au tableau de bord
            </a>


            <a
                href="create.php"
                class="btn btn-primary"
            >
                + Nouveau contrôle
            </a>

        </div>

    </div>


    <!-- =================================================
         MESSAGE DE SUCCÈS
    ================================================= -->

    <?php if (
        isset($_GET["success"])
        &&
        $_GET["success"] === "created"
    ): ?>

        <div class="success">

            Le contrôle a été ajouté avec succès.

        </div>

    <?php endif; ?>


    <?php if (
        isset($_GET["success"])
        &&
        $_GET["success"] === "updated"
    ): ?>

        <div class="success">

            Le contrôle a été modifié avec succès.

        </div>

    <?php endif; ?>


    <?php if (
        isset($_GET["success"])
        &&
        $_GET["success"] === "deleted"
    ): ?>

        <div class="success">

            Le contrôle a été supprimé avec succès.

        </div>

    <?php endif; ?>


    <!-- =================================================
         TABLEAU
    ================================================= -->

    <div class="table-container">


        <?php if (empty($controls)): ?>


            <div class="empty">

                Aucun contrôle enregistré.

            </div>


        <?php else: ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>


                        <th>
                            Obligation
                        </th>


                        <th>
                            Contrôlé par
                        </th>


                        <th>
                            Date
                        </th>


                        <th>
                            Résultat
                        </th>


                        <th>
                            Commentaire
                        </th>


                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach (
                    $controls
                    as $control
                ): ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <?= (int)
                                $control["id"]
                            ?>

                        </td>


                        <!-- OBLIGATION -->

                        <td>

                            <?= htmlspecialchars(
                                $control[
                                    "obligation_title"
                                ]
                            ) ?>

                        </td>


                        <!-- UTILISATEUR -->

                        <td>

                            <?= htmlspecialchars(
                                $control[
                                    "controller_name"
                                ]
                            ) ?>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?= htmlspecialchars(
                                $control[
                                    "control_date"
                                ]
                            ) ?>

                        </td>


                        <!-- RÉSULTAT -->

                        <td>


                            <?php

                            $resultClass = "";


                            if (
                                $control["result"]
                                === "Conforme"
                            ) {

                                $resultClass =
                                    "conforme";

                            } elseif (
                                $control["result"]
                                === "Non conforme"
                            ) {

                                $resultClass =
                                    "non-conforme";

                            } else {

                                $resultClass =
                                    "partiel";

                            }

                            ?>


                            <span
                                class="
                                    badge
                                    <?= $resultClass ?>
                                "
                            >

                                <?= htmlspecialchars(
                                    $control["result"]
                                ) ?>

                            </span>


                        </td>


                        <!-- COMMENTAIRE -->

                        <td>

                            <?= htmlspecialchars(
                                $control["comment"]
                            ) ?>

                        </td>


                        <!-- ACTIONS -->

                        <td>


                            <a
                                href="
                                    edit.php?id=
                                    <?= (int)
                                        $control["id"]
                                    ?>
                                "
                                class="btn btn-primary"
                            >

                                Modifier

                            </a>


                            <a
                                href="
                                    delete.php?id=
                                    <?= (int)
                                        $control["id"]
                                    ?>
                                "
                                class="btn btn-danger"

                                onclick="
                                    return confirm(
                                        'Voulez-vous vraiment supprimer ce contrôle ?'
                                    );
                                "
                            >

                                Supprimer

                            </a>


                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>


        <?php endif; ?>


    </div>


</div>


</body>

</html>