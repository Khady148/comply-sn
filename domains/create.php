<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "") {

        $error = "Le nom du domaine est obligatoire.";

    } else {

        $sql = "INSERT INTO domains (name, description)
                VALUES (:name, :description)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":name" => $name,
            ":description" => $description
        ]);

        header("Location: index.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ajouter un domaine - COMPLY-SN</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            height: 120px;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Ajouter un domaine</h1>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label for="name">
            Nom du domaine *
        </label>

        <input
            type="text"
            id="name"
            name="name"
            required
            maxlength="100"
        >

        <label for="description">
            Description
        </label>

        <textarea
            id="description"
            name="description"
        ></textarea>

        <button type="submit">
            Enregistrer
        </button>

    </form>

    <a href="index.php" class="back">
        ← Retour aux domaines
    </a>

</div>

</body>

</html>