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
   2. RÉCUPÉRER LES PREUVES
===================================================== */

$stmt = $pdo->query("

    SELECT
        e.id,
        e.file_name,
        e.file_path,
        e.uploaded_at,

        c.id AS control_id,
        c.result,

        o.title AS obligation_title,

        u.full_name AS uploader_name

    FROM evidences e

    INNER JOIN controls c
        ON e.control_id = c.id

    INNER JOIN obligations o
        ON c.obligation_id = o.id

    INNER JOIN users u
        ON e.uploaded_by = u.id

    ORDER BY e.uploaded_at DESC

");

$evidences = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Preuves - COMPLY-SN
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


        .container {

            max-width: 1200px;

            margin: 30px auto;

            padding: 20px;

        }


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


        .btn-success {

            background: #198754;

            color: white;

        }


        .btn-primary:hover {

            background: #0b5ed7;

        }


        .btn-danger:hover {

            background: #bb2d3b;

        }


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

        }


        tr:hover {

            background: #f8f9fa;

        }


        .badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

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


        .empty {

            text-align: center;

            padding: 30px;

            color: #666;

        }


        .success {

            background: #d1e7dd;

            color: #0f5132;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


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


            th,
            td {

                padding: 8px;

                font-size: 13px;

            }

        }

    </style>

</head>


<body>


<div class="header">

    <h1>
        COMPLY-SN
    </h1>

    <p>
        Gestion des preuves de conformité
    </p>

</div>


<div class="container">


    <div class="top">

        <div>

            <h2>
                Preuves
            </h2>

            <p>
                Documents permettant de justifier les contrôles de conformité.
            </p>

        </div>


        <div>

            <a
                href="../dashboard.php"
                class="btn btn-secondary"
            >
                Tableau de bord
            </a>


            <a
                href="create.php"
                class="btn btn-primary"
            >
                + Ajouter une preuve
            </a>

        </div>

    </div>


    <?php if (
        isset($_GET["success"])
        &&
        $_GET["success"] === "created"
    ): ?>

        <div class="success">

            La preuve a été ajoutée avec succès.

        </div>

    <?php endif; ?>


    <?php if (
        isset($_GET["success"])
        &&
        $_GET["success"] === "deleted"
    ): ?>

        <div class="success">

            La preuve a été supprimée avec succès.

        </div>

    <?php endif; ?>


    <div class="table-container">


        <?php if (empty($evidences)): ?>


            <div class="empty">

                Aucune preuve enregistrée.

            </div>


        <?php else: ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Fichier
                        </th>

                        <th>
                            Obligation
                        </th>

                        <th>
                            Résultat du contrôle
                        </th>

                        <th>
                            Ajouté par
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach (
                    $evidences
                    as $evidence
                ): ?>


                    <tr>


                        <td>

                            <?= (int)
                                $evidence["id"]
                            ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $evidence["file_name"]
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $evidence[
                                    "obligation_title"
                                ]
                            ) ?>

                        </td>


                        <td>


                            <?php

                            if (
                                $evidence["result"]
                                === "Conforme"
                            ) {

                                $resultClass =
                                    "conforme";

                            } elseif (
                                $evidence["result"]
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
                                    $evidence["result"]
                                ) ?>

                            </span>


                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $evidence[
                                    "uploader_name"
                                ]
                            ) ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $evidence[
                                    "uploaded_at"
                                ]
                            ) ?>

                        </td>


                        <td>


                            <a
                                href="../<?= htmlspecialchars(
                                    $evidence["file_path"]
                                ) ?>"
                                target="_blank"
                                class="btn btn-success"
                            >

                                Voir

                            </a>


                            <a
                                href="delete.php?id=<?= (int)
                                    $evidence["id"] ?>"
                                class="btn btn-danger"

                                onclick="
                                    return confirm(
                                        'Voulez-vous vraiment supprimer cette preuve ?'
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