<?php
    function get_course(pdo $connection) {
        $driver_statement = $connection->prepare("SELECT first_name, last_name FROM user_data WHERE user_id = :driver_id LIMIT 1");

        $statement = $connection->prepare("SELECT requests.id, requests.id_user_app, requests.latitude_depart, requests.longitude_depart,
                                requests.latitude_arrivee, requests.longitude_arrivee, requests.statut_course, requests.id_conducteur_accepter,
                                requests.id_user_app, requests.creer, first_name, last_name, requests.distance, requests.montant, requests.duree
                            FROM requests, user_data
                            WHERE requests.id_user_app = user_id  AND requests.statut_course != ''
                            ORDER BY requests.id DESC");
        $statement->execute();
        $output = [];

        foreach ($statement->fetchAll() as $row) {
            $driver_statement->bindParam(':driver_id', $row['id_conducteur_accepter']);
            $driver_statement->execute();
            $row['driver'] = $driver_statement->fetch();
            $output[] = $row;
        }

        echo json_encode(['msg' => $output, 'state' => (count($output) == 0) ? 0 : 1]);
    }