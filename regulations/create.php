<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$error = "";

/* Récupérer les domaines */
$stmt = $pdo->query("
    SELECT id, name
    FROM domains
    ORDER BY name
");

$domains = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Traitement du formulaire */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $domain_id = filter_input(
        INPUT_POST,
        "domain_id",
        FILTER_VALIDATE_INT
    );

    $title = trim($_POST["title"] ?? "");
    $reference = trim($_POST["reference"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $effective_date = $_POST["effective_date"] ?? "";


    /* Validation */

    if (!$domain_id) {

        $error = "Veuillez sélectionner un domaine.";

    } elseif ($title === "") {

        $error = "Le titre est obligatoire.";

    } elseif ($effective_date === "") {

        $error = "La date d'entrée en vigueur est obligatoire.";

    } else {

        /* Insertion sécurisée avec PDO */

        $sql = "
            INSERT INTO regulations
            (
                domain_id,
                title,
                reference,
                description,
                effective_date
            )

            VALUES
            (
                :domain_id,
                :title,
                :reference,
                :description,
                :effective_date
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ":domain_id" => $domain_id,

            ":title" => $title,

            ":reference" => $reference,

            ":description" => $description,

            ":effective_date" => $effective_date

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
        Ajouter une réglementation - COMPLY-SN
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

            box-shadow: 0 2px 10px rgba(0,0,0,0.1);

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

            background: #198754;

            color: white;

            font-size: 16px;

            cursor: pointer;

        }


        button:hover {

            background: #146c43;

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
        Ajouter une réglementation
    </h1>


    <?php if ($error): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form method="POST" id="regulationForm">


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
                        isset($_POST["domain_id"])
                        &&
                        $_POST["domain_id"] == $domain["id"]
                    )
                    ? "selected"
                    : ""
                    ?>
                >

                    <?= htmlspecialchars($domain["name"]) ?>

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
                $_POST["title"] ?? ""
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

            placeholder="Exemple : CGI-SN"

            value="<?= htmlspecialchars(
                $_POST["reference"] ?? ""
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
            $_POST["description"] ?? ""
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
                $_POST["effective_date"] ?? ""
            ) ?>"

        >


        <button type="submit">

            Enregistrer

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

        const domain =
            document.getElementById("domain_id").value;

        const title =
            document.getElementById("title").value.trim();

        const date =
            document.getElementById("effective_date").value;


        if (domain === "") {

            alert("Veuillez sélectionner un domaine.");

            event.preventDefault();

            return;

        }


        if (title === "") {

            alert("Le titre est obligatoire.");

            event.preventDefault();

            return;

        }


        if (date === "") {

            alert(
                "La date d'entrée en vigueur est obligatoire."
            );

            event.preventDefault();

        }

    });

</script>


</body>

</html>