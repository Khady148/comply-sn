<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

// Récupérer le domaine
$stmt = $pdo->prepare("
    SELECT id, name, description
    FROM domains
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$domain = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$domain) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "") {

        $error = "Le nom du domaine est obligatoire.";

    } else {

        $stmt = $pdo->prepare("
            UPDATE domains
            SET name = :name,
                description = :description
            WHERE id = :id
        ");

        $stmt->execute([
            ":name" => $name,
            ":description" => $description,
            ":id" => $id
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

    <title>Modifier un domaine</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
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
        }

        textarea {
            height: 120px;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .error {
            color: #842029;
            background: #f8d7da;
            padding: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Modifier le domaine</h1>

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
            value="<?= htmlspecialchars($domain["name"]) ?>"
            required
        >

        <label for="description">
            Description
        </label>

        <textarea
            id="description"
            name="description"
        ><?= htmlspecialchars($domain["description"]) ?></textarea>

        <button type="submit">
            Enregistrer les modifications
        </button>

    </form>

    <br>

    <a href="index.php">
        ← Retour
    </a>

</div>

</body>

</html>