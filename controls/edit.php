<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* Récupérer l'ID du contrôle */
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

/* Récupérer le contrôle */
$stmt = $pdo->prepare("
    SELECT *
    FROM controls
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$control = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$control) {
    die("Contrôle introuvable.");
}

/* Récupérer les obligations */
$stmt = $pdo->query("
    SELECT id, title
    FROM obligations
    ORDER BY title ASC
");

$obligations = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Récupérer les utilisateurs */
$stmt = $pdo->query("
    SELECT id, full_name
    FROM users
    ORDER BY full_name ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

/* Traitement du formulaire */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $obligation_id = filter_input(
        INPUT_POST,
        "obligation_id",
        FILTER_VALIDATE_INT
    );

    $controlled_by = filter_input(
        INPUT_POST,
        "controlled_by",
        FILTER_VALIDATE_INT
    );

    $control_date = trim($_POST["control_date"] ?? "");
    $result = trim($_POST["result"] ?? "");
    $comment = trim($_POST["comment"] ?? "");

    if (!$obligation_id) {
        $errors[] = "Veuillez sélectionner une obligation.";
    }

    if (!$controlled_by) {
        $errors[] = "Veuillez sélectionner un utilisateur.";
    }

    if ($control_date === "") {
        $errors[] = "Veuillez sélectionner une date.";
    }

    $allowed_results = [
        "Conforme",
        "Non conforme",
        "Partiellement conforme"
    ];

    if (!in_array($result, $allowed_results, true)) {
        $errors[] = "Résultat invalide.";
    }

    if (strlen($comment) < 5) {
        $errors[] = "Le commentaire doit contenir au moins 5 caractères.";
    }

    /* Mise à jour */
    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                UPDATE controls

                SET
                    obligation_id = :obligation_id,
                    controlled_by = :controlled_by,
                    control_date = :control_date,
                    result = :result,
                    comment = :comment

                WHERE id = :id
            ");

            $stmt->execute([
                ":obligation_id" => $obligation_id,
                ":controlled_by" => $controlled_by,
                ":control_date" => $control_date,
                ":result" => $result,
                ":comment" => $comment,
                ":id" => $id
            ]);

            /* Audit */
            $audit = $pdo->prepare("
                INSERT INTO audit_logs
                (
                    user_id,
                    action,
                    table_name,
                    record_id,
                    ip_address
                )

                VALUES
                (
                    :user_id,
                    :action,
                    :table_name,
                    :record_id,
                    :ip_address
                )
            ");

            $audit->execute([
                ":user_id" => $_SESSION["user_id"],
                ":action" => "Modification d'un contrôle",
                ":table_name" => "controls",
                ":record_id" => $id,
                ":ip_address" => $_SERVER["REMOTE_ADDR"] ?? null
            ]);

            header("Location: index.php?success=updated");
            exit;

        } catch (PDOException $e) {

            $errors[] =
                "Une erreur est survenue lors de la modification.";
        }
    }

} else {

    /* Valeurs initiales */
    $obligation_id = $control["obligation_id"];
    $controlled_by = $control["controlled_by"];
    $control_date = $control["control_date"];
    $result = $control["result"];
    $comment = $control["comment"];
}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Modifier un contrôle - COMPLY-SN</title>

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

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            min-height: 130px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 6px;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-left: 8px;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>COMPLY-SN</h1>

    <p>Modification d'un contrôle</p>

</div>


<div class="container">

    <h2>Modifier le contrôle</h2>


    <?php if (!empty($errors)): ?>

        <div class="error">

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <form method="POST" id="editControlForm">


        <div class="form-group">

            <label for="obligation_id">
                Obligation concernée
            </label>

            <select
                name="obligation_id"
                id="obligation_id"
                required
            >

                <option value="">
                    -- Sélectionner --
                </option>

                <?php foreach ($obligations as $obligation): ?>

                    <option
                        value="<?= (int)$obligation["id"] ?>"
                        <?= $obligation_id == $obligation["id"]
                            ? "selected"
                            : "" ?>
                    >

                        <?= htmlspecialchars($obligation["title"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="form-group">

            <label for="controlled_by">
                Contrôlé par
            </label>

            <select
                name="controlled_by"
                id="controlled_by"
                required
            >

                <?php foreach ($users as $user): ?>

                    <option
                        value="<?= (int)$user["id"] ?>"
                        <?= $controlled_by == $user["id"]
                            ? "selected"
                            : "" ?>
                    >

                        <?= htmlspecialchars($user["full_name"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="form-group">

            <label for="control_date">
                Date du contrôle
            </label>

            <input
                type="date"
                name="control_date"
                id="control_date"
                value="<?= htmlspecialchars($control_date) ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="result">
                Résultat
            </label>

            <select
                name="result"
                id="result"
                required
            >

                <option value="Conforme"
                    <?= $result === "Conforme" ? "selected" : "" ?>>
                    Conforme
                </option>

                <option value="Partiellement conforme"
                    <?= $result === "Partiellement conforme" ? "selected" : "" ?>>
                    Partiellement conforme
                </option>

                <option value="Non conforme"
                    <?= $result === "Non conforme" ? "selected" : "" ?>>
                    Non conforme
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="comment">
                Commentaire
            </label>

            <textarea
                name="comment"
                id="comment"
                required
            ><?= htmlspecialchars($comment) ?></textarea>

        </div>


        <button
            type="submit"
            class="btn btn-primary"
        >
            Enregistrer les modifications
        </button>


        <a
            href="index.php"
            class="btn btn-secondary"
        >
            Annuler
        </a>

    </form>

</div>


<script>

document
.getElementById("editControlForm")
.addEventListener("submit", function(event) {

    const comment =
        document
        .getElementById("comment")
        .value
        .trim();

    if (comment.length < 5) {

        alert(
            "Le commentaire doit contenir au moins 5 caractères."
        );

        event.preventDefault();
    }

});

</script>

</body>

</html>