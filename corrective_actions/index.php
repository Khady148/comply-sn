<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}


/* =====================================================
   RÉCUPÉRER LES ACTIONS CORRECTIVES
===================================================== */

$stmt = $pdo->query("
    SELECT
        ca.id,
        ca.title,
        ca.description,
        ca.due_date,
        ca.status,
        u.full_name AS responsible_name,
        o.title AS obligation_title
    FROM corrective_actions ca

    LEFT JOIN users u
        ON ca.responsible_user_id = u.id

    LEFT JOIN controls c
        ON ca.control_id = c.id

    LEFT JOIN obligations o
        ON c.obligation_id = o.id

    ORDER BY ca.id DESC
");

$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    Actions correctives - COMPLY-SN
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


.container {

    max-width: 1200px;

    margin: 30px auto;

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow:
        0 2px 10px rgba(0,0,0,0.08);

}


.top-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.btn {

    display: inline-block;

    padding: 10px 16px;

    border-radius: 6px;

    text-decoration: none;

    color: white;

}


.btn-back {

    background: #6c757d;

}


.btn-back:hover {

    background: #5c636a;

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

    vertical-align: top;

}


th {

    background: #172b4d;

    color: white;

}


tr:hover {

    background: #f5f5f5;

}


.badge {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 15px;

    font-size: 13px;

    font-weight: bold;

}


.status-en-cours {

    background: #fff3cd;

    color: #664d03;

}


.status-a-faire {

    background: #cfe2ff;

    color: #084298;

}


.status-termine {

    background: #d1e7dd;

    color: #0f5132;

}


.status-autre {

    background: #e2e3e5;

    color: #41464b;

}


.empty {

    text-align: center;

    padding: 40px;

    color: #777;

}


.description {

    max-width: 300px;

}


@media (max-width: 900px) {

    .container {

        margin: 15px;

        padding: 15px;

        overflow-x: auto;

    }


    table {

        min-width: 1000px;

    }

}

</style>

</head>


<body>


<div class="header">

    <h1>
        COMPLY-SN
    </h1>

    <div>
        Gestion des actions correctives
    </div>

</div>



<div class="container">


    <div class="top-bar">

        <h2>
            Actions correctives
        </h2>


        <a
            href="../dashboard.php"
            class="btn btn-back"
        >
            ← Retour au Dashboard
        </a>

    </div>



    <?php if (empty($actions)): ?>


        <div class="empty">

            <h3>
                Aucune action corrective
            </h3>

            <p>
                Aucune action corrective n'est actuellement enregistrée.
            </p>

        </div>


    <?php else: ?>


        <table>


            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Action
                    </th>

                    <th>
                        Description
                    </th>

                    <th>
                        Obligation concernée
                    </th>

                    <th>
                        Responsable
                    </th>

                    <th>
                        Date limite
                    </th>

                    <th>
                        Statut
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php foreach (
                $actions
                as $action
            ): ?>


                <tr>


                    <td>

                        <?= (int)$action["id"] ?>

                    </td>


                    <td>

                        <strong>

                            <?= htmlspecialchars(
                                $action["title"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </strong>

                    </td>


                    <td class="description">

                        <?= nl2br(
                            htmlspecialchars(
                                $action["description"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $action["obligation_title"]
                            ?? "Non renseignée",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $action["responsible_name"]
                            ?? "Non renseigné",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $action["due_date"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </td>


                    <td>


                        <?php

                        $status =
                            strtolower(
                                trim(
                                    $action["status"]
                                )
                            );


                        if (
                            $status === "en cours"
                        ) {

                            $class =
                                "status-en-cours";

                        } elseif (
                            $status === "à faire"
                        ) {

                            $class =
                                "status-a-faire";

                        } elseif (
                            $status === "terminé"
                            ||
                            $status === "termine"
                        ) {

                            $class =
                                "status-termine";

                        } else {

                            $class =
                                "status-autre";

                        }

                        ?>


                        <span
                            class="badge <?= $class ?>"
                        >

                            <?= htmlspecialchars(
                                $action["status"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </span>


                    </td>


                </tr>


            <?php endforeach; ?>


            </tbody>


        </table>


    <?php endif; ?>


</div>


</body>

</html>