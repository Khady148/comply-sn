<?php

session_start();

require_once "../config/database.php";


/* =====================================================
   1. SI L'UTILISATEUR EST DÉJÀ CONNECTÉ
===================================================== */

if (isset($_SESSION["user_id"])) {

    header("Location: ../dashboard.php");
    exit;

}


$error = "";


/* =====================================================
   2. TRAITEMENT DE LA CONNEXION
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim(
        $_POST["email"] ?? ""
    );

    $password = $_POST["password"] ?? "";


    /* ================================================
       VALIDATION
    ================================================ */

    if ($email === "" || $password === "") {

        $error =
            "Veuillez remplir tous les champs.";

    } else {


        /* ============================================
           RECHERCHER L'UTILISATEUR
        ============================================ */

        $stmt = $pdo->prepare("
            SELECT
                id,
                full_name,
                email,
                password,
                role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");


        $stmt->execute([
            ":email" => $email
        ]);


        $user = $stmt->fetch(
            PDO::FETCH_ASSOC
        );


        /* ============================================
           VÉRIFICATION DU MOT DE PASSE
        ============================================ */

        if (
            $user &&
            password_verify(
                $password,
                $user["password"]
            )
        ) {


            /* ========================================
               CRÉER LA SESSION
            ======================================== */

            $_SESSION["user_id"] =
                $user["id"];

            $_SESSION["full_name"] =
                $user["full_name"];

            $_SESSION["email"] =
                $user["email"];

            $_SESSION["role"] =
                $user["role"];


            /* ========================================
               REDIRECTION
            ======================================== */

            header(
                "Location: ../dashboard.php"
            );

            exit;


        } else {

            $error =
                "Adresse e-mail ou mot de passe incorrect.";

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
    Connexion - COMPLY-SN
</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* =====================================================
   BODY
===================================================== */

body {

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #0f172a 0%,
            #172b4d 50%,
            #0d6efd 100%
        );

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

}


/* =====================================================
   ARRIÈRE-PLAN
===================================================== */

.background-circle {

    position: fixed;

    border-radius: 50%;

    filter: blur(2px);

    opacity: 0.15;

    pointer-events: none;

}


.circle-one {

    width: 350px;

    height: 350px;

    background: white;

    top: -120px;

    left: -100px;

}


.circle-two {

    width: 450px;

    height: 450px;

    background: #ffffff;

    bottom: -200px;

    right: -150px;

}


/* =====================================================
   CONTAINER
===================================================== */

.login-wrapper {

    width: 100%;

    max-width: 450px;

    position: relative;

    z-index: 2;

}


/* =====================================================
   CARTE
===================================================== */

.login-card {

    background: rgba(
        255,
        255,
        255,
        0.98
    );

    border-radius: 20px;

    padding: 40px;

    box-shadow:
        0 25px 60px
        rgba(
            0,
            0,
            0,
            0.25
        );

    animation:
        fadeIn 0.6s ease;

}


/* =====================================================
   ANIMATION
===================================================== */

@keyframes fadeIn {

    from {

        opacity: 0;

        transform:
            translateY(20px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/* =====================================================
   LOGO
===================================================== */

.logo-container {

    text-align: center;

    margin-bottom: 30px;

}


.logo {

    width: 75px;

    height: 75px;

    margin: auto;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            #172b4d,
            #0d6efd
        );

    display: flex;

    align-items: center;

    justify-content: center;

    color: white;

    font-size: 34px;

    box-shadow:
        0 10px 25px
        rgba(
            13,
            110,
            253,
            0.3
        );

}


.logo-container h1 {

    margin-top: 15px;

    color: #172b4d;

    font-size: 28px;

    letter-spacing: 1px;

}


.logo-container p {

    margin-top: 7px;

    color: #6c757d;

    font-size: 14px;

}


/* =====================================================
   TITRE
===================================================== */

.login-title {

    text-align: center;

    margin-bottom: 25px;

}


.login-title h2 {

    color: #212529;

    font-size: 23px;

    margin-bottom: 7px;

}


.login-title p {

    color: #6c757d;

    font-size: 14px;

}


/* =====================================================
   MESSAGE ERREUR
===================================================== */

.error {

    background: #f8d7da;

    border: 1px solid #f1aeb5;

    color: #842029;

    padding: 12px 14px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 14px;

}


/* =====================================================
   CHAMPS
===================================================== */

.form-group {

    margin-bottom: 20px;

}


label {

    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #343a40;

    font-size: 14px;

}


.input-wrapper {

    position: relative;

}


.input-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform:
        translateY(-50%);

    font-size: 17px;

    color: #6c757d;

}


input {

    width: 100%;

    padding: 13px 45px;

    border: 1px solid #ced4da;

    border-radius: 9px;

    font-size: 15px;

    outline: none;

    transition: 0.2s;

    background: #fff;

}


input:focus {

    border-color: #0d6efd;

    box-shadow:
        0 0 0 3px
        rgba(
            13,
            110,
            253,
            0.12
        );

}


/* =====================================================
   BOUTON AFFICHER MOT DE PASSE
===================================================== */

.password-toggle {

    position: absolute;

    right: 12px;

    top: 50%;

    transform:
        translateY(-50%);

    border: none;

    background: transparent;

    cursor: pointer;

    font-size: 17px;

    color: #6c757d;

}


.password-toggle:hover {

    color: #0d6efd;

}


/* =====================================================
   OPTIONS
===================================================== */

.form-options {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

    font-size: 13px;

}


.remember {

    display: flex;

    align-items: center;

    gap: 7px;

    color: #6c757d;

}


.remember input {

    width: auto;

    padding: 0;

}


.forgot {

    color: #0d6efd;

    text-decoration: none;

}


.forgot:hover {

    text-decoration: underline;

}


/* =====================================================
   BOUTON CONNEXION
===================================================== */

.btn-login {

    width: 100%;

    border: none;

    padding: 14px;

    border-radius: 9px;

    background:
        linear-gradient(
            135deg,
            #172b4d,
            #0d6efd
        );

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;

    box-shadow:
        0 8px 20px
        rgba(
            13,
            110,
            253,
            0.25
        );

}


.btn-login:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 12px 25px
        rgba(
            13,
            110,
            253,
            0.35
        );

}


.btn-login:active {

    transform:
        translateY(0);

}


/* =====================================================
   PIED DE PAGE
===================================================== */

.login-footer {

    text-align: center;

    margin-top: 25px;

    color: #6c757d;

    font-size: 12px;

}


.security {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 6px;

    margin-top: 10px;

    color: #198754;

    font-weight: bold;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 500px) {

    body {

        padding: 15px;

    }


    .login-card {

        padding: 28px 22px;

        border-radius: 16px;

    }


    .logo {

        width: 65px;

        height: 65px;

        font-size: 28px;

    }


    .logo-container h1 {

        font-size: 24px;

    }

}

</style>

</head>


<body>


<div class="background-circle circle-one"></div>

<div class="background-circle circle-two"></div>



<div class="login-wrapper">


    <div class="login-card">


        <!-- ==========================================
             LOGO
        =========================================== -->

        <div class="logo-container">


            <div class="logo">

                🛡️

            </div>


            <h1>
                COMPLY-SN
            </h1>


            <p>
                Plateforme de gestion de conformité
            </p>


        </div>



        <!-- ==========================================
             TITRE
        =========================================== -->

        <div class="login-title">

            <h2>
                Bienvenue
            </h2>

            <p>
                Connectez-vous à votre espace
            </p>

        </div>



        <!-- ==========================================
             ERREUR
        =========================================== -->

        <?php if ($error !== ""): ?>

            <div class="error">

                ⚠️

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </div>

        <?php endif; ?>



        <!-- ==========================================
             FORMULAIRE
        =========================================== -->

        <form
            method="POST"
            autocomplete="on"
        >


            <!-- EMAIL -->

            <div class="form-group">


                <label for="email">

                    Adresse e-mail

                </label>


                <div class="input-wrapper">


                    <span class="input-icon">

                        ✉️

                    </span>


                    <input

                        type="email"

                        name="email"

                        id="email"

                        placeholder="exemple@email.com"

                        autocomplete="email"

                        required

                        value="<?= htmlspecialchars(
                            $_POST["email"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"

                    >


                </div>


            </div>



            <!-- MOT DE PASSE -->

            <div class="form-group">


                <label for="password">

                    Mot de passe

                </label>


                <div class="input-wrapper">


                    <span class="input-icon">

                        🔒

                    </span>


                    <input

                        type="password"

                        name="password"

                        id="password"

                        placeholder="Votre mot de passe"

                        autocomplete="current-password"

                        required

                    >


                    <button

                        type="button"

                        class="password-toggle"

                        id="togglePassword"

                        aria-label="Afficher le mot de passe"

                    >

                        👁️

                    </button>


                </div>


            </div>



            <!-- OPTIONS -->

            <div class="form-options">


                <label class="remember">


                    <input
                        type="checkbox"
                        name="remember"
                    >


                    Se souvenir de moi


                </label>


                <a
                    href="#"
                    class="forgot"
                    onclick="
                        alert(
                            'Contactez l’administrateur pour réinitialiser votre mot de passe.'
                        );
                        return false;
                    "
                >

                    Mot de passe oublié ?

                </a>


            </div>



            <!-- BOUTON -->

            <button
                type="submit"
                class="btn-login"
            >

                🔐

                Se connecter

            </button>


        </form>



        <!-- ==========================================
             FOOTER
        =========================================== -->

        <div class="login-footer">

            <div>

                © 2026 COMPLY-SN

            </div>


            <div class="security">

                🔒 Connexion sécurisée

            </div>

        </div>


    </div>


</div>



<script>

/* =====================================================
   AFFICHER / MASQUER LE MOT DE PASSE
===================================================== */

const passwordInput =
    document.getElementById(
        "password"
    );


const togglePassword =
    document.getElementById(
        "togglePassword"
    );


togglePassword.addEventListener(
    "click",
    function() {


        if (
            passwordInput.type ===
            "password"
        ) {

            passwordInput.type =
                "text";

            togglePassword.textContent =
                "🙈";

            togglePassword.setAttribute(
                "aria-label",
                "Masquer le mot de passe"
            );


        } else {

            passwordInput.type =
                "password";

            togglePassword.textContent =
                "👁️";

            togglePassword.setAttribute(
                "aria-label",
                "Afficher le mot de passe"
            );

        }

    }
);

</script>


</body>

</html>