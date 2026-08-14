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
   2. RÉCUPÉRER LES OBLIGATIONS
===================================================== */

$stmt = $pdo->query("
    SELECT
        id,
        title
    FROM obligations
    ORDER BY title ASC
");

$obligations = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   3. RÉCUPÉRER LES UTILISATEURS
===================================================== */

$stmt = $pdo->query("
    SELECT
        id,
        full_name
    FROM users
    ORDER BY full_name ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   4. VARIABLES
===================================================== */

$errors = [];

$obligation_id = "";
$controlled_by = $_SESSION["user_id"];
$control_date = date("Y-m-d");
$result = "";
$comment = "";


/* =====================================================
   5. TRAITEMENT DU FORMULAIRE
===================================================== */

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

    $control_date = trim(
        $_POST["control_date"] ?? ""
    );

    $result = trim(
        $_POST["result"] ?? ""
    );

    $comment = trim(
        $_POST["comment"] ?? ""
    );


    /* =================================================
       6. VALIDATION
    ================================================= */

    if (!$obligation_id) {

        $errors[] =
            "Veuillez sélectionner une obligation.";

    }


    if (!$controlled_by) {

        $errors[] =
            "Veuillez sélectionner la personne responsable du contrôle.";

    }


    if ($control_date === "") {

        $errors[] =
            "Veuillez sélectionner une date.";

    }


    $allowed_results = [

        "Conforme",

        "Non conforme",

        "Partiellement conforme"

    ];


    if (!in_array(
        $result,
        $allowed_results,
        true
    )) {

        $errors[] =
            "Veuillez sélectionner un résultat valide.";

    }


    if ($comment === "") {

        $errors[] =
            "Veuillez saisir un commentaire.";

    }


    /* =================================================
       7. INSERTION
    ================================================= */

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("

                INSERT INTO controls

                (
                    obligation_id,
                    controlled_by,
                    control_date,
                    result,
                    comment
                )

                VALUES

                (
                    :obligation_id,
                    :controlled_by,
                    :control_date,
                    :result,
                    :comment
                )

            ");


            $stmt->execute([

                ":obligation_id" =>
                    $obligation_id,

                ":controlled_by" =>
                    $controlled_by,

                ":control_date" =>
                    $control_date,

                ":result" =>
                    $result,

                ":comment" =>
                    $comment

            ]);


            /* =================================================
               8. JOURNAL D'AUDIT
            ================================================= */

            $control_id =
                $pdo->lastInsertId();


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

                ":user_id" =>
                    $_SESSION["user_id"],

                ":action" =>
                    "Création d'un contrôle",

                ":table_name" =>
                    "controls",

                ":record_id" =>
                    $control_id,

                ":ip_address" =>
                    $_SERVER["REMOTE_ADDR"] ?? null

            ]);


            /* =================================================
               9. REDIRECTION
            ================================================= */

            header(
                "Location: index.php?success=created"
            );

            exit;


        } catch (PDOException $e) {

            $errors[] =
                "Une erreur est survenue lors de l'enregistrement.";

        }

    }

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

    <title>
        Nouveau contrôle - COMPLY-SN
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
            resize: vertical;
        }

        .required {
            color: red;
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
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
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

        @media (max-width: 600px) {

            .container {
                margin: 15px;
                padding: 20px;
            }

            .btn {
                width: 100%;
                margin: 5px 0;
                text-align: center;
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
        Ajouter un contrôle de conformité
    </p>

</div>


<div class="container">


    <h2>
        Nouveau contrôle
    </h2>


    <?php if (!empty($errors)): ?>

        <div class="error">

            <strong>
                Veuillez corriger les erreurs :
            </strong>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        id="controlForm"
    >


        <!-- OBLIGATION -->

        <div class="form-group">

            <label for="obligation_id">

                Obligation concernée

                <span class="required">*</span>

            </label>


            <select
                name="obligation_id"
                id="obligation_id"
                required
            >

                <option value="">
                    -- Sélectionner une obligation --
                </option>


                <?php foreach ($obligations as $obligation): ?>

                    <option
                        value="<?= (int)$obligation["id"] ?>"

                        <?= (
                            $obligation_id
                            == $obligation["id"]
                        )
                        ? "selected"
                        : ""
                        ?>
                    >

                        <?= htmlspecialchars(
                            $obligation["title"]
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- CONTRÔLEUR -->

        <div class="form-group">

            <label for="controlled_by">

                Contrôlé par

                <span class="required">*</span>

            </label>


            <select
                name="controlled_by"
                id="controlled_by"
                required
            >

                <option value="">
                    -- Sélectionner un utilisateur --
                </option>


                <?php foreach ($users as $user): ?>

                    <option
                        value="<?= (int)$user["id"] ?>"

                        <?= (
                            $controlled_by
                            == $user["id"]
                        )
                        ? "selected"
                        : ""
                        ?>
                    >

                        <?= htmlspecialchars(
                            $user["full_name"]
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- DATE -->

        <div class="form-group">

            <label for="control_date">

                Date du contrôle

                <span class="required">*</span>

            </label>


            <input
                type="date"
                name="control_date"
                id="control_date"
                value="<?= htmlspecialchars(
                    $control_date
                ) ?>"
                required
            >

        </div>


        <!-- RÉSULTAT -->

        <div class="form-group">

            <label for="result">

                Résultat

                <span class="required">*</span>

            </label>


            <select
                name="result"
                id="result"
                required
            >

                <option value="">
                    -- Sélectionner un résultat --
                </option>

                <option
                    value="Conforme"
                    <?= $result === "Conforme"
                        ? "selected"
                        : "" ?>
                >
                    Conforme
                </option>

                <option
                    value="Partiellement conforme"
                    <?= $result === "Partiellement conforme"
                        ? "selected"
                        : "" ?>
                >
                    Partiellement conforme
                </option>

                <option
                    value="Non conforme"
                    <?= $result === "Non conforme"
                        ? "selected"
                        : "" ?>
                >
                    Non conforme
                </option>

            </select>

        </div>


        <!-- COMMENTAIRE -->

        <div class="form-group">

            <label for="comment">

                Commentaire

                <span class="required">*</span>

            </label>


            <textarea
                name="comment"
                id="comment"
                required
            ><?= htmlspecialchars(
                $comment
            ) ?></textarea>

        </div>


        <!-- BOUTONS -->

        <button
            type="submit"
            class="btn btn-primary"
        >
            Enregistrer le contrôle
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

/* =====================================================
   VALIDATION CÔTÉ CLIENT
===================================================== */

document
.getElementById("controlForm")
.addEventListener("submit", function(event) {

    const obligation =
        document
        .getElementById("obligation_id")
        .value;

    const result =
        document
        .getElementById("result")
        .value;

    const comment =
        document
        .getElementById("comment")
        .value
        .trim();


    if (obligation === "") {

        alert(
            "Veuillez sélectionner une obligation."
        );

        event.preventDefault();

        return;
    }


    if (result === "") {

        alert(
            "Veuillez sélectionner le résultat du contrôle."
        );

        event.preventDefault();

        return;
    }


    if (comment.length < 5) {

        alert(
            "Le commentaire doit contenir au moins 5 caractères."
        );

        event.preventDefault();

        return;
    }

});

</script>


</body>

</html>