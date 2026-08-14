<?php

/*
|--------------------------------------------------------------------------
| Fonction d'enregistrement des actions dans le journal d'audit
|--------------------------------------------------------------------------
*/

function logAudit(
    PDO $pdo,
    int $userId,
    string $action,
    string $tableName = null,
    int $recordId = null
): bool {

    try {

        /*
        |--------------------------------------------------------------------------
        | Récupération de l'adresse IP
        |--------------------------------------------------------------------------
        */

        $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "UNKNOWN";


        /*
        |--------------------------------------------------------------------------
        | Insertion dans audit_logs
        |--------------------------------------------------------------------------
        */

        $sql = "
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
        ";


        $stmt = $pdo->prepare($sql);


        $stmt->execute([

            ":user_id" => $userId,

            ":action" => $action,

            ":table_name" => $tableName,

            ":record_id" => $recordId,

            ":ip_address" => $ipAddress

        ]);


        return true;


    } catch (PDOException $e) {

        /*
        |--------------------------------------------------------------------------
        | On ne bloque pas l'application si le journal d'audit rencontre
        | un problème.
        |--------------------------------------------------------------------------
        */

        error_log(
            "Erreur Audit : " .
            $e->getMessage()
        );

        return false;
    }
}