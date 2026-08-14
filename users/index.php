<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* Récupérer les utilisateurs */
$stmt = $pdo->query("
    SELECT
        id,
        full_name,
        email,
        role,
        created_at
    FROM users
    ORDER BY id DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Utilisateurs - COMPLY-SN</title>

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
    margin: 0;
}

.container {
    max-width: 1100px;
    margin: 30px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
    border: none;
    cursor: pointer;
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

.admin {
    background: #f8d7da;
    color: #842029;
}

.user {
    background: #d1e7dd;
    color: #0f5132;
}

.empty {
    text-align: center;
    padding: 30px;
    color: #777;
}

@media (max-width: 700px) {

    .container {
        margin: 15px;
        padding: 15px;
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

    <p>Gestion des utilisateurs</p>

</div>


<div class="container">

    <div class="top-bar">

        <h2>Utilisateurs</h2>

        <a
            href="../dashboard.php"
            class="btn btn-back"
        >
            ← Retour au Dashboard
        </a>

    </div>


    <?php if (empty($users)): ?>

        <div class="empty">

            Aucun utilisateur trouvé.

        </div>

    <?php else: ?>


        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Nom complet</th>

                    <th>Email</th>

                    <th>Rôle</th>

                    <th>Date de création</th>

                </tr>

            </thead>


            <tbody>

            <?php foreach ($users as $user): ?>

                <tr>

                    <td>
                        <?= (int)$user["id"] ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $user["full_name"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $user["email"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </td>


                    <td>

                        <?php if (
                            strtolower($user["role"]) === "admin"
                        ): ?>

                            <span class="badge admin">
                                Administrateur
                            </span>

                        <?php else: ?>

                            <span class="badge user">
                                <?= htmlspecialchars(
                                    $user["role"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $user["created_at"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>


    <?php endif; ?>

</div>

</body>

</html>