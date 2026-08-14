<?php

require_once "../config/database.php";

$users = [
    [
        "full_name" => "Administrateur COMPLY-SN",
        "email" => "admin@comply-sn.local",
        "password" => "Admin123!",
        "role" => "admin"
    ],
    [
        "full_name" => "Fatou Diop",
        "email" => "fatou@comply-sn.local",
        "password" => "Fatou123!",
        "role" => "advanced"
    ],
    [
        "full_name" => "Awa Ndiaye",
        "email" => "awa@comply-sn.local",
        "password" => "Awa123!",
        "role" => "standard"
    ]
];

$sql = "INSERT INTO users 
        (full_name, email, password, role)
        VALUES
        (:full_name, :email, :password, :role)";

$stmt = $pdo->prepare($sql);

foreach ($users as $user) {

    $hashedPassword = password_hash(
        $user["password"],
        PASSWORD_DEFAULT
    );

    $stmt->execute([
        ":full_name" => $user["full_name"],
        ":email" => $user["email"],
        ":password" => $hashedPassword,
        ":role" => $user["role"]
    ]);
}

echo "Les 3 utilisateurs ont été créés avec succès.";

?>