<?php
    function get_client_request(pdo $connection) {
        $driver_data = $connection->prepare("SELECT first_name, last_name, total_rating, total_raters 
                FROM user_data  INNER JOIN user_drivers ON driver_id = user_id WHERE user_id = :user_id LIMIT 1");

        $statement = $connection->prepare("SELECT requests.request_id, requests.customer_id, requests.departure_lat, requests.departure_lon,
                            requests.destination_lat, requests.destination_lon, requests.statut, requests.statut_course, requests.request_route,
                            requests.date_requested, first_name, last_name, requests.distance, requests.request_status, requests.duree
        FROM requests, user_data
        WHERE requests.customer_id = user_data.user_id AND requests.customer_id= :client_id AND requests.statut_course <> 'clôturer'
        ORDER BY requests.request_id DESC");

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