<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT id, name, description, created_at
    FROM domains
    ORDER BY id DESC
");

$domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Domaines - COMPLY-SN</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .header {
            background: #172b4d;
            color: white;
            padding: 20px;
        }

        .container {
            padding: 30px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin-bottom: 20px;
        }

        .btn-add {
            background: #198754;
        }

        .btn-edit {
            background: #0d6efd;
        }

        .btn-delete {
            background: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #172b4d;
            color: white;
        }

        @media(max-width:700px) {

            .container {
                padding: 10px;
                overflow-x: auto;
            }

            table {
                min-width: 700px;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <h1>COMPLY-SN</h1>

    <p>Gestion des domaines réglementaires</p>

</div>

<div class="container">

    <a href="create.php" class="btn btn-add">
        + Ajouter un domaine
    </a>

    <a href="../dashboard.php" class="btn btn-edit">
        ← Dashboard
    </a>

    <h2>Liste des domaines</h2>

    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Nom</th>

                <th>Description</th>

                <th>Date de création</th>

                <th>Actions</th>

            </tr>

        </thead>

        <tbody>

        <?php foreach ($domains as $domain): ?>

            <tr>

                <td>
                    <?= (int)$domain["id"] ?>
                </td>

                <td>
                    <?= htmlspecialchars($domain["name"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($domain["description"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($domain["created_at"]) ?>
                </td>

                <td>

                    <a
                        href="edit.php?id=<?= (int)$domain["id"] ?>"
                        class="btn btn-edit"
                    >
                        Modifier
                    </a>

                    <a
                        href="delete.php?id=<?= (int)$domain["id"] ?>"
                        class="btn btn-delete"
                        onclick="return confirm('Voulez-vous vraiment supprimer ce domaine ?');"
                    >
                        Supprimer
                    </a>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

</body>

</html>