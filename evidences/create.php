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
   2. RÉCUPÉRER LES CONTRÔLES
===================================================== */

$stmt = $pdo->query("

    SELECT
        c.id,
        c.result,
        o.title AS obligation_title

    FROM controls c

    INNER JOIN obligations o
        ON c.obligation_id = o.id

    ORDER BY c.control_date DESC

");

$controls = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   3. TRAITEMENT DU FORMULAIRE
===================================================== */

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* ===============================
       RÉCUPÉRATION DES DONNÉES
    =============================== */

    $control_id = filter_input(
        INPUT_POST,
        "control_id",
        FILTER_VALIDATE_INT
    );


    /* ===============================
       VÉRIFICATION DU CONTRÔLE
    =============================== */

    if (!$control_id) {

        $error =
            "Veuillez sélectionner un contrôle.";

    }


    /* ===============================
       VÉRIFICATION DU FICHIER
    =============================== */

    elseif (
        !isset($_FILES["evidence"])
        ||
        $_FILES["evidence"]["error"]
        !== UPLOAD_ERR_OK
    ) {

        $error =
            "Veuillez sélectionner un fichier.";

    }


    else {


        $file = $_FILES["evidence"];


        /* ===============================
           TAILLE MAXIMALE : 5 MB
        =============================== */

        $maxSize = 5 * 1024 * 1024;


        if ($file["size"] > $maxSize) {

            $error =
                "Le fichier ne doit pas dépasser 5 Mo.";

        }


        else {


            /* ===============================
               EXTENSIONS AUTORISÉES
            =============================== */

            $allowedExtensions = [

                "pdf",
                "doc",
                "docx",
                "xls",
                "xlsx",
                "jpg",
                "jpeg",
                "png"

            ];


            $extension = strtolower(
                pathinfo(
                    $file["name"],
                    PATHINFO_EXTENSION
                )
            );


            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                $error =
                    "Type de fichier non autorisé.";

            }


            else {


                /* ===============================
                   TYPES MIME AUTORISÉS
                =============================== */

                $allowedMimeTypes = [

                    "application/pdf",

                    "application/msword",

                    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",

                    "application/vnd.ms-excel",

                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",

                    "image/jpeg",

                    "image/png"

                ];


                $finfo = new finfo(
                    FILEINFO_MIME_TYPE
                );


                $mimeType = $finfo->file(
                    $file["tmp_name"]
                );


                if (
                    !in_array(
                        $mimeType,
                        $allowedMimeTypes,
                        true
                    )
                ) {

                    $error =
                        "Le contenu du fichier n'est pas autorisé.";

                }


                else {


                    /* ===============================
                       CRÉER LE DOSSIER UPLOADS
                    =============================== */

                    $uploadDirectory =
                        __DIR__ . "/uploads/";


                    if (
                        !is_dir(
                            $uploadDirectory
                        )
                    ) {

                        mkdir(
                            $uploadDirectory,
                            0755,
                            true
                        );

                    }


                    /* ===============================
                       NOM DE FICHIER SÉCURISÉ
                    =============================== */

                    $newFileName =
                        bin2hex(
                            random_bytes(16)
                        )
                        . "."
                        . $extension;


                    $destination =
                        $uploadDirectory
                        . $newFileName;


                    /* ===============================
                       DÉPLACER LE FICHIER
                    =============================== */

                    if (
                        !move_uploaded_file(
                            $file["tmp_name"],
                            $destination
                        )
                    ) {

                        $error =
                            "Erreur lors de l'enregistrement du fichier.";

                    }


                    else {


                        /* ===============================
                           CHEMIN ENREGISTRÉ EN BDD
                        =============================== */

                        $filePath =
                            "evidences/uploads/"
                            . $newFileName;


                        /* ===============================
                           NOM ORIGINAL SÉCURISÉ
                        =============================== */

                        $originalName =
                            basename(
                                $file["name"]
                            );


                        /* ===============================
                           INSERTION BDD
                        =============================== */

                        try {


                            $stmt = $pdo->prepare("

                                INSERT INTO evidences

                                (
                                    control_id,
                                    file_name,
                                    file_path,
                                    uploaded_by
                                )

                                VALUES

                                (
                                    :control_id,
                                    :file_name,
                                    :file_path,
                                    :uploaded_by
                                )

                            ");


                            $stmt->execute([

                                ":control_id" =>
                                    $control_id,

                                ":file_name" =>
                                    $originalName,

                                ":file_path" =>
                                    $filePath,

                                ":uploaded_by" =>
                                    $_SESSION[
                                        "user_id"
                                    ]

                            ]);


                            /* ===============================
                               AUDIT TRAIL
                            =============================== */

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
                                    $_SESSION[
                                        "user_id"
                                    ],

                                ":action" =>
                                    "Ajout d'une preuve",

                                ":table_name" =>
                                    "evidences",

                                ":record_id" =>
                                    $pdo->lastInsertId(),

                                ":ip_address" =>
                                    $_SERVER[
                                        "REMOTE_ADDR"
                                    ] ?? null

                            ]);


                            /* ===============================
                               REDIRECTION
                            =============================== */

                            header(
                                "Location: index.php?success=created"
                            );

                            exit;


                        }

                        catch (
                            PDOException $e
                        ) {


                            /* Si la BDD échoue,
                               supprimer le fichier */

                            if (
                                file_exists(
                                    $destination
                                )
                            ) {

                                unlink(
                                    $destination
                                );

                            }


                            $error =
                                "Erreur lors de l'enregistrement dans la base de données.";

                        }

                    }

                }

            }

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
        Ajouter une preuve - COMPLY-SN
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


        .header p {

            margin: 0;

        }


        .container {

            max-width: 800px;

            margin: 30px auto;

            padding: 20px;

        }


        .card {

            background: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        h2 {

            margin-top: 0;

        }


        .form-group {

            margin-bottom: 20px;

        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;

        }


        select,
        input[type="file"] {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            background: white;

        }


        .help {

            margin-top: 7px;

            font-size: 13px;

            color: #666;

        }


        .error {

            background: #f8d7da;

            color: #842029;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        .buttons {

            margin-top: 25px;

        }


        .btn {

            display: inline-block;

            padding: 11px 18px;

            border-radius: 6px;

            text-decoration: none;

            border: none;

            cursor: pointer;

            font-size: 14px;

        }


        .btn-primary {

            background: #0d6efd;

            color: white;

        }


        .btn-secondary {

            background: #6c757d;

            color: white;

            margin-right: 5px;

        }


        .btn-primary:hover {

            background: #0b5ed7;

        }


        @media (max-width: 600px) {

            .container {

                margin: 10px;

                padding: 10px;

            }


            .card {

                padding: 20px;

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
        Gestion des preuves de conformité
    </p>

</div>


<div class="container">


    <div class="card">


        <h2>
            Ajouter une preuve
        </h2>


        <p>
            Sélectionnez le contrôle concerné et ajoutez le document justificatif.
        </p>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
            id="evidenceForm"
        >


            <!-- ===============================
                 CONTRÔLE
            =============================== -->

            <div class="form-group">

                <label for="control_id">

                    Contrôle concerné *

                </label>


                <select
                    name="control_id"
                    id="control_id"
                    required
                >

                    <option value="">

                        -- Sélectionner un contrôle --

                    </option>


                    <?php foreach (
                        $controls
                        as $control
                    ): ?>


                        <option
                            value="<?= (int)
                                $control["id"] ?>"
                        >

                            <?= htmlspecialchars(
                                $control[
                                    "obligation_title"
                                ]
                            ) ?>

                            -
                            
                            <?= htmlspecialchars(
                                $control["result"]
                            ) ?>

                        </option>


                    <?php endforeach; ?>


                </select>

            </div>


            <!-- ===============================
                 FICHIER
            =============================== -->

            <div class="form-group">

                <label for="evidence">

                    Document de preuve *

                </label>


                <input
                    type="file"
                    name="evidence"
                    id="evidence"
                    required
                    accept="
                        .pdf,
                        .doc,
                        .docx,
                        .xls,
                        .xlsx,
                        .jpg,
                        .jpeg,
                        .png
                    "
                >


                <div class="help">

                    Formats autorisés :
                    PDF, Word, Excel, JPG et PNG.

                    <br>

                    Taille maximale :
                    5 Mo.

                </div>

            </div>


            <!-- ===============================
                 BOUTONS
            =============================== -->

            <div class="buttons">


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >

                    Annuler

                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    Ajouter la preuve

                </button>


            </div>


        </form>


    </div>


</div>


<script>

/* =====================================================
   VALIDATION JAVASCRIPT
===================================================== */

document
    .getElementById("evidenceForm")
    .addEventListener(
        "submit",
        function(event) {


            const control =
                document.getElementById(
                    "control_id"
                );


            const file =
                document.getElementById(
                    "evidence"
                );


            if (
                control.value === ""
            ) {

                alert(
                    "Veuillez sélectionner un contrôle."
                );

                event.preventDefault();

                return;

            }


            if (
                file.files.length === 0
            ) {

                alert(
                    "Veuillez sélectionner un fichier."
                );

                event.preventDefault();

                return;

            }


            const maxSize =
                5 * 1024 * 1024;


            if (
                file.files[0].size
                >
                maxSize
            ) {

                alert(
                    "Le fichier ne doit pas dépasser 5 Mo."
                );

                event.preventDefault();

                return;

            }


            const allowed =
                [
                    "pdf",
                    "doc",
                    "docx",
                    "xls",
                    "xlsx",
                    "jpg",
                    "jpeg",
                    "png"
                ];


            const fileName =
                file.files[0].name;


            const extension =
                fileName
                    .split(".")
                    .pop()
                    .toLowerCase();


            if (
                !allowed.includes(
                    extension
                )
            ) {

                alert(
                    "Ce type de fichier n'est pas autorisé."
                );

                event.preventDefault();

                return;

            }

        }
    );

</script>


</body>

</html>