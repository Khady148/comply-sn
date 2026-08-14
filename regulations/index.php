<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   RECHERCHE ET FILTRE
========================= */

$search = trim($_GET["search"] ?? "");
$domain_id = filter_input(INPUT_GET, "domain_id", FILTER_VALIDATE_INT);

/* =========================
   PAGINATION
========================= */

$limit = 20;
$page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT);

if (!$page || $page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

/* =========================
   CONSTRUCTION DE LA REQUÊTE
========================= */

$where = [];
$params = [];

if ($search !== "") {

    $where[] = "(r.title LIKE :search
                 OR r.reference LIKE :search
                 OR r.description LIKE :search)";

    $params[":search"] = "%" . $search . "%";
}

if ($domain_id) {

    $where[] = "r.domain_id = :domain_id";

    $params[":domain_id"] = $domain_id;
}

$whereSQL = "";

if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* =========================
   NOMBRE TOTAL
========================= */

$countSQL = "
    SELECT COUNT(*)
    FROM regulations r
    $whereSQL
";

$stmt = $pdo->prepare($countSQL);
$stmt->execute($params);

$total = $stmt->fetchColumn();

$totalPages = ceil($total / $limit);

/* =========================
   RÉGLEMENTATIONS
========================= */

$sql = "
    SELECT
        r.id,
        r.title,
        r.reference,
        r.description,
        r.effective_date,
        d.name AS domain_name
    FROM regulations r

    INNER JOIN domains d
        ON r.domain_id = d.id

    $whereSQL

    ORDER BY r.id DESC

    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();

$regulations = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   DOMAINES POUR LE FILTRE
========================= */

$stmtDomains = $pdo->query("
    SELECT id, name
    FROM domains
    ORDER BY name
");

$domains = $stmtDomains->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Réglementations - COMPLY-SN</title>

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
            padding: 30px;
        }

        .actions {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin-right: 5px;
        }

        .btn-primary {
            background: #198754;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-edit {
            background: #0d6efd;
        }

        .btn-delete {
            background: #dc3545;
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .search-box form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        input,
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input {
            flex: 1;
            min-width: 220px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background: #172b4d;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #172b4d;
            color: white;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            background: white;
            color: #172b4d;
            border-radius: 4px;
        }

        .pagination .active {
            background: #172b4d;
            color: white;
        }

        @media(max-width: 800px) {

            .container {
                padding: 10px;
                overflow-x: auto;
            }

            table {
                min-width: 1000px;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <h1>COMPLY-SN</h1>

    <p>Gestion des réglementations</p>

</div>

<div class="container">

    <div class="actions">

        <a href="create.php" class="btn btn-primary">
            + Ajouter une réglementation
        </a>

        <a href="../dashboard.php" class="btn btn-secondary">
            ← Dashboard
        </a>

    </div>


    <!-- RECHERCHE -->

    <div class="search-box">

        <h3>Recherche et filtres</h3>

        <form method="GET">

            <input
                type="text"
                name="search"
                placeholder="Titre, référence ou description..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="domain_id">

                <option value="">
                    Tous les domaines
                </option>

                <?php foreach ($domains as $domain): ?>

                    <option
                        value="<?= (int)$domain["id"] ?>"
                        <?= ($domain_id == $domain["id"]) ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($domain["name"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit">
                Rechercher
            </button>

            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Réinitialiser
            </a>

        </form>

    </div>


    <h2>
        Liste des réglementations
    </h2>

    <p>
        <?= (int)$total ?> réglementation(s) trouvée(s)
    </p>


    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Titre</th>

                <th>Référence</th>

                <th>Domaine</th>

                <th>Date d'entrée en vigueur</th>

                <th>Actions</th>

            </tr>

        </thead>

        <tbody>

        <?php if (empty($regulations)): ?>

            <tr>

                <td colspan="6">
                    Aucune réglementation trouvée.
                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($regulations as $regulation): ?>

                <tr>

                    <td>
                        <?= (int)$regulation["id"] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($regulation["title"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($regulation["reference"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($regulation["domain_name"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($regulation["effective_date"]) ?>
                    </td>

                    <td>

                        <a
                            href="edit.php?id=<?= (int)$regulation["id"] ?>"
                            class="btn btn-edit"
                        >
                            Modifier
                        </a>

                        <a
                            href="delete.php?id=<?= (int)$regulation["id"] ?>"
                            class="btn btn-delete"
                            onclick="return confirm('Voulez-vous vraiment supprimer cette réglementation ?');"
                        >
                            Supprimer
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>


    <!-- PAGINATION -->

    <?php if ($totalPages > 1): ?>

        <div class="pagination">

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <a
                    href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&domain_id=<?= (int)$domain_id ?>"
                    class="<?= ($i == $page) ? 'active' : '' ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>

        </div>

    <?php endif; ?>

</div>

</body>

</html>