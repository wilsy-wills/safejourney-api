<?php
    function get_client_request(pdo $connection) {
        $driver_data = $connection->prepare("SELECT first_name, last_name, total_rating, total_raters 
                FROM user_data  INNER JOIN driver_info ON driver_id = user_id WHERE user_id = :user_id LIMIT 1");

        $statement = $connection->prepare("SELECT requests.id, requests.id_user_app, requests.latitude_depart, requests.longitude_depart,
                            requests.latitude_arrivee, requests.longitude_arrivee, requests.statut, requests.statut_course, requests.id_conducteur_accepter,
                            requests.creer, first_name, last_name, requests.distance, requests.montant, requests.duree
        FROM requests, user_data
        WHERE requests.id_user_app = user_data.user_id AND requests.id_user_app= :client_id AND requests.statut_course <> 'clôturer'
        ORDER BY requests.id DESC");

        $statement->bindParam(':client_id', $_GET['client_id']);
        $statement->execute();

        $output = [];
        foreach ($statement->fetchAll() as $row) {
            if ($row['id_conducteur_accepter'] == 0) {
                $driver_data->bindParam(':user_id', $row['id_conducteur_accepter']);
                $driver_data->execute();
                $row = array_merge($row, $driver_data->fetch());
            } else {
                $row = array_merge($row, ['first_name' => '', 'last_name' => '', 'total_rating' => '0', 'total_raters' => '0']);
            }
            $output[] = $row;
        }

        echo json_encode(['msg' => $output, 'state' => (count($output) == 0) ? 0 : 1]);
    }