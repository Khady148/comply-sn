<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   RÉCUPÉRER L'ID
========================= */

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: index.php");
    exit;
}


/* =========================
   RÉCUPÉRER LA RÉGLEMENTATION
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM regulations
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$regulation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$regulation) {
    header("Location: index.php");
    exit;
}


/* =========================
   RÉCUPÉRER LES DOMAINES
========================= */

$stmt = $pdo->query("
    SELECT id, name
    FROM domains
    ORDER BY name
");

$domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";


/* =========================
   TRAITEMENT DU FORMULAIRE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $domain_id = filter_input(
        INPUT_POST,
        "domain_id",
        FILTER_VALIDATE_INT
    );

    $title = trim($_POST["title"] ?? "");

    $reference = trim(
        $_POST["reference"] ?? ""
    );

    $description = trim(
        $_POST["description"] ?? ""
    );

    $effective_date =
        $_POST["effective_date"] ?? "";


    /* =========================
       VALIDATION
    ========================= */

    if (!$domain_id) {

        $error =
            "Veuillez sélectionner un domaine.";

    } elseif ($title === "") {

        $error =
            "Le titre est obligatoire.";

    } elseif ($effective_date === "") {

        $error =
            "La date d'entrée en vigueur est obligatoire.";

    } else {


        /* =========================
           MODIFICATION
        ========================= */

        $sql = "

            UPDATE regulations

            SET
                domain_id = :domain_id,
                title = :title,
                reference = :reference,
                description = :description,
                effective_date = :effective_date

            WHERE id = :id

        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ":domain_id" =>
                $domain_id,

            ":title" =>
                $title,

            ":reference" =>
                $reference,

            ":description" =>
                $description,

            ":effective_date" =>
                $effective_date,

            ":id" =>
                $id

        ]);


        /* Retour à la liste */

        header("Location: index.php");

        exit;
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
        Modifier une réglementation - COMPLY-SN
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


        .container {

            max-width: 750px;

            margin: 40px auto;

            background: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 2px 10px
                rgba(0,0,0,0.1);

        }


        h1 {

            color: #172b4d;

        }


        label {

            display: block;

            margin-top: 20px;

            margin-bottom: 7px;

            font-weight: bold;

        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 15px;

        }


        textarea {

            height: 130px;

            resize: vertical;

        }


        button {

            margin-top: 25px;

            padding: 12px 25px;

            border: none;

            border-radius: 5px;

            background: #0d6efd;

            color: white;

            font-size: 16px;

            cursor: pointer;

        }


        .back {

            display: inline-block;

            margin-top: 20px;

            text-decoration: none;

            color: #172b4d;

        }


        .error {

            background: #f8d7da;

            color: #842029;

            padding: 12px;

            border-radius: 5px;

            margin-bottom: 15px;

        }

    </style>

</head>


<body>


<div class="container">

    <h1>
        Modifier une réglementation
    </h1>


    <?php if ($error): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        id="regulationForm"
    >


        <!-- DOMAINE -->

        <label for="domain_id">

            Domaine *

        </label>


        <select
            name="domain_id"
            id="domain_id"
            required
        >

            <option value="">

                -- Sélectionner un domaine --

            </option>


            <?php foreach ($domains as $domain): ?>

                <option

                    value="<?= (int)$domain["id"] ?>"

                    <?= (
                        $regulation["domain_id"]
                        == $domain["id"]
                    )
                    ? "selected"
                    : ""
                    ?>

                >

                    <?= htmlspecialchars(
                        $domain["name"]
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>


        <!-- TITRE -->

        <label for="title">

            Titre *

        </label>


        <input

            type="text"

            name="title"

            id="title"

            maxlength="200"

            required

            value="<?= htmlspecialchars(
                $regulation["title"]
            ) ?>"

        >


        <!-- RÉFÉRENCE -->

        <label for="reference">

            Référence

        </label>


        <input

            type="text"

            name="reference"

            id="reference"

            maxlength="150"

            value="<?= htmlspecialchars(
                $regulation["reference"]
            ) ?>"

        >


        <!-- DESCRIPTION -->

        <label for="description">

            Description

        </label>


        <textarea
            name="description"
            id="description"
            maxlength="1000"
        ><?= htmlspecialchars(
            $regulation["description"]
        ) ?></textarea>


        <!-- DATE -->

        <label for="effective_date">

            Date d'entrée en vigueur *

        </label>


        <input

            type="date"

            name="effective_date"

            id="effective_date"

            required

            value="<?= htmlspecialchars(
                $regulation["effective_date"]
            ) ?>"

        >


        <button type="submit">

            Enregistrer les modifications

        </button>


    </form>


    <a
        href="index.php"
        class="back"
    >

        ← Retour aux réglementations

    </a>

</div>


<script>

/* Validation côté client */

document
    .getElementById("regulationForm")
    .addEventListener("submit", function(event) {

        const title =
            document
                .getElementById("title")
                .value
                .trim();

        const domain =
            document
                .getElementById("domain_id")
                .value;

        const date =
            document
                .getElementById("effective_date")
                .value;


        if (domain === "") {

            alert(
                "Veuillez sélectionner un domaine."
            );

            event.preventDefault();

            return;
        }


        if (title === "") {

            alert(
                "Le titre est obligatoire."
            );

            event.preventDefault();

            return;
        }


        if (date === "") {

            alert(
                "La date est obligatoire."
            );

            event.preventDefault();

        }

    });

</script>


</body>

</html>