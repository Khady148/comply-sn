<?php

session_start();

require_once "../config/database.php";
require_once "../config/audit.php";


/* =====================================================
   1. VÉRIFIER LA CONNEXION
===================================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


$errors = [];


/* =====================================================
   2. RÉCUPÉRER LES RÉGLEMENTATIONS
===================================================== */

$stmt = $pdo->query("
    SELECT id, title
    FROM regulations
    ORDER BY title
");

$regulations = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   3. RÉCUPÉRER LES UTILISATEURS
===================================================== */

$stmt = $pdo->query("
    SELECT id, full_name
    FROM users
    ORDER BY full_name
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   4. TRAITEMENT DU FORMULAIRE
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
       5. VALIDATION
    ================================================= */


    if (!$regulation_id) {

        $errors[] =
            "Veuillez sélectionner une réglementation.";

    }


    if ($title === "") {

        $errors[] =
            "Le titre de l'obligation est obligatoire.";

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


    $allowed_criticalities = [

        "Faible",
        "Moyenne",
        "Élevée",
        "Critique"

    ];


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


    $allowed_statuses = [

        "Conforme",
        "Non conforme",
        "En cours",
        "À vérifier"

    ];


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
       6. INSERTION
    ================================================= */

    if (empty($errors)) {

        try {


            /* =============================================
               INSERTION DE L'OBLIGATION
            ============================================= */

            $sql = "

                INSERT INTO obligations

                (
                    regulation_id,
                    title,
                    description,
                    frequency,
                    due_date,
                    criticality,
                    status,
                    responsible_user_id
                )

                VALUES

                (
                    :regulation_id,
                    :title,
                    :description,
                    :frequency,
                    :due_date,
                    :criticality,
                    :status,
                    :responsible_user_id
                )

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
                    $responsible_user_id

            ]);


            /* =============================================
               RÉCUPÉRER L'ID DE L'OBLIGATION
            ============================================= */

            $new_id = (int) $pdo->lastInsertId();


            /* =============================================
               JOURNAL D'AUDIT
            ============================================= */

            logAudit(

                $pdo,

                (int) $_SESSION["user_id"],

                "CRÉATION D'UNE OBLIGATION",

                "obligations",

                $new_id

            );


            /* =============================================
               REDIRECTION
            ============================================= */

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
    Ajouter une obligation - COMPLY-SN
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

    max-width: 900px;

    margin: 30px auto;

    background: white;

    padding: 30px;

    border-radius: 10px;

}


.form-group {

    margin-bottom: 20px;

}


label {

    display: block;

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

    min-height: 120px;

    resize: vertical;

}


button {

    background: #198754;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 5px;

    cursor: pointer;

    font-size: 15px;

}


button:hover {

    background: #157347;

}


.btn-back {

    display: inline-block;

    margin-left: 10px;

    padding: 12px 20px;

    background: #6c757d;

    color: white;

    text-decoration: none;

    border-radius: 5px;

}


.error {

    background: #f8d7da;

    color: #842029;

    padding: 15px;

    border-radius: 5px;

    margin-bottom: 20px;

}


.error ul {

    margin-bottom: 0;

}


.required {

    color: red;

}

</style>

</head>


<body>


<div class="header">

<h1>
    COMPLY-SN
</h1>


<p>
    Ajouter une obligation de conformité
</p>

</div>



<div class="container">


<h2>
    Nouvelle obligation
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


<!-- =================================================
     RÉGLEMENTATION
================================================= -->

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



<?php foreach ($regulations as $regulation): ?>


<option

    value="<?= (int) $regulation["id"] ?>"

    <?= (

        isset($_POST["regulation_id"])

        &&

        $_POST["regulation_id"]
        == $regulation["id"]

    )
    ? "selected"
    : "" ?>

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



<!-- =================================================
     TITRE
================================================= -->

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
        $_POST["title"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    ) ?>"

    placeholder="Exemple : Déclaration fiscale mensuelle"

>


</div>



<!-- =================================================
     DESCRIPTION
================================================= -->

<div class="form-group">


<label for="description">

Description

<span class="required">*</span>

</label>



<textarea

    name="description"

    id="description"

    required

    placeholder="Décrivez l'obligation..."

><?= htmlspecialchars(
    $_POST["description"] ?? "",
    ENT_QUOTES,
    "UTF-8"
) ?></textarea>


</div>



<!-- =================================================
     FRÉQUENCE
================================================= -->

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

-- Sélectionner --

</option>


<option value="Quotidienne">
    Quotidienne
</option>


<option value="Hebdomadaire">
    Hebdomadaire
</option>


<option value="Mensuelle">
    Mensuelle
</option>


<option value="Trimestrielle">
    Trimestrielle
</option>


<option value="Semestrielle">
    Semestrielle
</option>


<option value="Annuelle">
    Annuelle
</option>


<option value="Continue">
    Continue
</option>


</select>


</div>



<!-- =================================================
     DATE LIMITE
================================================= -->

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
        $_POST["due_date"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    ) ?>"

>


</div>



<!-- =================================================
     CRITICITÉ
================================================= -->

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

-- Sélectionner --

</option>


<option value="Faible">
    Faible
</option>


<option value="Moyenne">
    Moyenne
</option>


<option value="Élevée">
    Élevée
</option>


<option value="Critique">
    Critique
</option>


</select>


</div>



<!-- =================================================
     STATUT
================================================= -->

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


<option value="À vérifier">
    À vérifier
</option>


<option value="En cours">
    En cours
</option>


<option value="Conforme">
    Conforme
</option>


<option value="Non conforme">
    Non conforme
</option>


</select>


</div>



<!-- =================================================
     RESPONSABLE
================================================= -->

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



<?php foreach ($users as $user): ?>


<option

    value="<?= (int) $user["id"] ?>"

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



<!-- =================================================
     BOUTONS
================================================= -->

<button type="submit">

    Enregistrer l'obligation

</button>



<a
    href="index.php"
    class="btn-back"
>

    Annuler

</a>


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


        }
    );


</script>


</body>

</html>