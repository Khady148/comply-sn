<?php

session_start();

require_once "../config/database.php";
require_once "../config/audit.php";


/* =====================================================
   1. VÉRIFICATION DE LA CONNEXION
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


/* =====================================================
   2. RÉCUPÉRER L'ID DE L'OBLIGATION
===================================================== */

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {

    header("Location: index.php");
    exit;

}


/* =====================================================
   3. RÉCUPÉRER L'OBLIGATION
===================================================== */

$stmt = $pdo->prepare("
    SELECT *
    FROM obligations
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);

$obligation = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$obligation) {

    header("Location: index.php");
    exit;

}


/* =====================================================
   4. RÉCUPÉRER LES RÉGLEMENTATIONS
===================================================== */

$stmt = $pdo->query("
    SELECT id, title
    FROM regulations
    ORDER BY title ASC
");

$regulations = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   5. RÉCUPÉRER LES UTILISATEURS
===================================================== */

$stmt = $pdo->query("
    SELECT id, full_name
    FROM users
    ORDER BY full_name ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   6. LISTES DES CHOIX
===================================================== */

$allowed_criticalities = [
    "Faible",
    "Moyenne",
    "Élevée",
    "Critique"
];


$allowed_statuses = [
    "Conforme",
    "Non conforme",
    "En cours",
    "À vérifier"
];


$frequencies = [
    "Quotidienne",
    "Hebdomadaire",
    "Mensuelle",
    "Trimestrielle",
    "Semestrielle",
    "Annuelle",
    "Continue"
];


/* =====================================================
   7. VARIABLES
===================================================== */

$errors = [];


/* =====================================================
   8. TRAITEMENT DU FORMULAIRE
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $regulation_id = filter_input(
        INPUT_POST,
        "regulation_id",
        FILTER_VALIDATE_INT
    );


    $title = trim(
        $_POST["title"] ?? ""
    );


    $description = trim(
        $_POST["description"] ?? ""
    );


    $frequency = trim(
        $_POST["frequency"] ?? ""
    );


    $due_date = trim(
        $_POST["due_date"] ?? ""
    );


    $criticality = trim(
        $_POST["criticality"] ?? ""
    );


    $status = trim(
        $_POST["status"] ?? ""
    );


    $responsible_user_id = filter_input(
        INPUT_POST,
        "responsible_user_id",
        FILTER_VALIDATE_INT
    );


    /* =================================================
       9. VALIDATION
    ================================================= */


    if (!$regulation_id) {

        $errors[] =
            "Veuillez sélectionner une réglementation.";

    }


    if ($title === "") {

        $errors[] =
            "Le titre est obligatoire.";

    }


    if (strlen($title) > 200) {

        $errors[] =
            "Le titre ne doit pas dépasser 200 caractères.";

    }


    if ($description === "") {

        $errors[] =
            "La description est obligatoire.";

    }


    if ($frequency === "") {

        $errors[] =
            "Veuillez sélectionner une fréquence.";

    }


    if ($due_date === "") {

        $errors[] =
            "La date limite est obligatoire.";

    }


    if (
        !in_array(
            $criticality,
            $allowed_criticalities,
            true
        )
    ) {

        $errors[] =
            "La criticité sélectionnée est invalide.";

    }


    if (
        !in_array(
            $status,
            $allowed_statuses,
            true
        )
    ) {

        $errors[] =
            "Le statut sélectionné est invalide.";

    }


    if (!$responsible_user_id) {

        $errors[] =
            "Veuillez sélectionner un responsable.";

    }


    /* =================================================
       10. MODIFICATION
    ================================================= */

    if (empty($errors)) {

        try {


            /* =============================================
               MISE À JOUR DE L'OBLIGATION
            ============================================= */

            $sql = "

                UPDATE obligations

                SET

                    regulation_id =
                        :regulation_id,

                    title =
                        :title,

                    description =
                        :description,

                    frequency =
                        :frequency,

                    due_date =
                        :due_date,

                    criticality =
                        :criticality,

                    status =
                        :status,

                    responsible_user_id =
                        :responsible_user_id

                WHERE id = :id

            ";


            $stmt = $pdo->prepare($sql);


            $stmt->execute([

                ":regulation_id" =>
                    $regulation_id,

                ":title" =>
                    $title,

                ":description" =>
                    $description,

                ":frequency" =>
                    $frequency,

                ":due_date" =>
                    $due_date,

                ":criticality" =>
                    $criticality,

                ":status" =>
                    $status,

                ":responsible_user_id" =>
                    $responsible_user_id,

                ":id" =>
                    $id

            ]);


            /* =============================================
               JOURNAL D'AUDIT
            ============================================= */

            logAudit(

                $pdo,

                (int) $_SESSION["user_id"],

                "MODIFICATION D'UNE OBLIGATION",

                "obligations",

                (int) $id

            );


            /* =============================================
               REDIRECTION
            ============================================= */

            header(
                "Location: index.php?success=updated"
            );

            exit;


        } catch (PDOException $e) {


            $errors[] =
                "Une erreur est survenue lors de la modification.";

        }

    }


    /* =================================================
       11. CONSERVER LES DONNÉES SAISIES
    ================================================= */

    $obligation["regulation_id"] =
        $regulation_id;

    $obligation["title"] =
        $title;

    $obligation["description"] =
        $description;

    $obligation["frequency"] =
        $frequency;

    $obligation["due_date"] =
        $due_date;

    $obligation["criticality"] =
        $criticality;

    $obligation["status"] =
        $status;

    $obligation["responsible_user_id"] =
        $responsible_user_id;

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
        Modifier une obligation - COMPLY-SN
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

            max-width: 900px;

            margin: 30px auto;

            background: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 2px 10px rgba(0,0,0,0.08);

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

            min-height: 120px;

            resize: vertical;

        }


        input:focus,
        select:focus,
        textarea:focus {

            outline: none;

            border-color: #0d6efd;

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


        .error ul {

            margin: 8px 0 0 20px;

        }


        .buttons {

            margin-top: 25px;

        }


        .btn {

            display: inline-block;

            padding: 12px 20px;

            border: none;

            border-radius: 6px;

            cursor: pointer;

            text-decoration: none;

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


        .btn-primary:hover {

            background: #0b5ed7;

        }


        .btn-secondary:hover {

            background: #5c636a;

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


    <div>
        Modification d'une obligation de conformité
    </div>

</div>



<div class="container">


    <h2>
        Modifier l'obligation
    </h2>



    <?php if (!empty($errors)): ?>

        <div class="error">

            <strong>
                Veuillez corriger les erreurs :
            </strong>


            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>

                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>



    <form
        method="POST"
        id="obligationForm"
    >


        <!-- ==========================================
             RÉGLEMENTATION
        =========================================== -->

        <div class="form-group">


            <label for="regulation_id">

                Réglementation

                <span class="required">*</span>

            </label>



            <select
                name="regulation_id"
                id="regulation_id"
                required
            >


                <option value="">

                    -- Sélectionner une réglementation --

                </option>



                <?php foreach (
                    $regulations
                    as $regulation
                ): ?>


                    <option

                        value="<?= (int)
                            $regulation["id"] ?>"

                        <?= (

                            $obligation["regulation_id"]

                            ==

                            $regulation["id"]

                        )
                        ? "selected"
                        : ""
                        ?>

                    >

                        <?= htmlspecialchars(
                            $regulation["title"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </option>


                <?php endforeach; ?>


            </select>


        </div>



        <!-- ==========================================
             TITRE
        =========================================== -->

        <div class="form-group">


            <label for="title">

                Titre de l'obligation

                <span class="required">*</span>

            </label>



            <input

                type="text"

                name="title"

                id="title"

                maxlength="200"

                required

                value="<?= htmlspecialchars(
                    $obligation["title"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"

            >


        </div>



        <!-- ==========================================
             DESCRIPTION
        =========================================== -->

        <div class="form-group">


            <label for="description">

                Description

                <span class="required">*</span>

            </label>



            <textarea

                name="description"

                id="description"

                required

            ><?= htmlspecialchars(
                $obligation["description"],
                ENT_QUOTES,
                "UTF-8"
            ) ?></textarea>


        </div>



        <!-- ==========================================
             FRÉQUENCE
        =========================================== -->

        <div class="form-group">


            <label for="frequency">

                Fréquence

                <span class="required">*</span>

            </label>



            <select

                name="frequency"

                id="frequency"

                required

            >


                <option value="">

                    -- Sélectionner une fréquence --

                </option>



                <?php foreach (
                    $frequencies
                    as $frequency
                ): ?>


                    <option

                        value="<?= htmlspecialchars(
                            $frequency,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"

                        <?= (

                            $obligation["frequency"]

                            ===

                            $frequency

                        )
                        ? "selected"
                        : ""
                        ?>

                    >

                        <?= htmlspecialchars(
                            $frequency,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </option>


                <?php endforeach; ?>


            </select>


        </div>



        <!-- ==========================================
             DATE LIMITE
        =========================================== -->

        <div class="form-group">


            <label for="due_date">

                Date limite

                <span class="required">*</span>

            </label>



            <input

                type="date"

                name="due_date"

                id="due_date"

                required

                value="<?= htmlspecialchars(
                    $obligation["due_date"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"

            >


        </div>



        <!-- ==========================================
             CRITICITÉ
        =========================================== -->

        <div class="form-group">


            <label for="criticality">

                Criticité

                <span class="required">*</span>

            </label>



            <select

                name="criticality"

                id="criticality"

                required

            >


                <option value="">

                    -- Sélectionner une criticité --

                </option>



                <?php foreach (
                    $allowed_criticalities
                    as $criticality
                ): ?>


                    <option

                        value="<?= htmlspecialchars(
                            $criticality,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"

                        <?= (

                            $obligation["criticality"]

                            ===

                            $criticality

                        )
                        ? "selected"
                        : ""
                        ?>

                    >

                        <?= htmlspecialchars(
                            $criticality,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </option>


                <?php endforeach; ?>


            </select>


        </div>



        <!-- ==========================================
             STATUT
        =========================================== -->

        <div class="form-group">


            <label for="status">

                Statut

                <span class="required">*</span>

            </label>



            <select

                name="status"

                id="status"

                required

            >


                <option value="">

                    -- Sélectionner un statut --

                </option>



                <?php foreach (
                    $allowed_statuses
                    as $status
                ): ?>


                    <option

                        value="<?= htmlspecialchars(
                            $status,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"

                        <?= (

                            $obligation["status"]

                            ===

                            $status

                        )
                        ? "selected"
                        : ""
                        ?>

                    >

                        <?= htmlspecialchars(
                            $status,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </option>


                <?php endforeach; ?>


            </select>


        </div>



        <!-- ==========================================
             RESPONSABLE
        =========================================== -->

        <div class="form-group">


            <label for="responsible_user_id">

                Responsable

                <span class="required">*</span>

            </label>



            <select

                name="responsible_user_id"

                id="responsible_user_id"

                required

            >


                <option value="">

                    -- Sélectionner un responsable --

                </option>



                <?php foreach (
                    $users
                    as $user
                ): ?>


                    <option

                        value="<?= (int)
                            $user["id"] ?>"

                        <?= (

                            $obligation[
                                "responsible_user_id"
                            ]

                            ==

                            $user["id"]

                        )
                        ? "selected"
                        : ""
                        ?>

                    >

                        <?= htmlspecialchars(
                            $user["full_name"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </option>


                <?php endforeach; ?>


            </select>


        </div>



        <!-- ==========================================
             BOUTONS
        =========================================== -->

        <div class="buttons">


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


        </div>


    </form>


</div>



<script>


/* =====================================================
   VALIDATION JAVASCRIPT
===================================================== */

document
    .getElementById("obligationForm")
    .addEventListener(
        "submit",
        function(event) {


            const title =

                document
                    .getElementById("title")
                    .value
                    .trim();


            const description =

                document
                    .getElementById("description")
                    .value
                    .trim();


            const regulation =

                document
                    .getElementById("regulation_id")
                    .value;


            const criticality =

                document
                    .getElementById("criticality")
                    .value;


            const status =

                document
                    .getElementById("status")
                    .value;



            if (regulation === "") {

                alert(
                    "Veuillez sélectionner une réglementation."
                );

                event.preventDefault();

                return;

            }



            if (title.length < 3) {

                alert(
                    "Le titre doit contenir au moins 3 caractères."
                );

                event.preventDefault();

                return;

            }



            if (description.length < 10) {

                alert(
                    "La description doit contenir au moins 10 caractères."
                );

                event.preventDefault();

                return;

            }



            if (criticality === "") {

                alert(
                    "Veuillez sélectionner une criticité."
                );

                event.preventDefault();

                return;

            }



            if (status === "") {

                alert(
                    "Veuillez sélectionner un statut."
                );

                event.preventDefault();

                return;

            }

        }
    );


</script>


</body>

</html>