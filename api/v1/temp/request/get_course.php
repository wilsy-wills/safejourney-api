<?php
    function get_course(pdo $connection) {
        $driver_statement = $connection->prepare("SELECT first_name, last_name FROM user_data WHERE user_id = :driver_id LIMIT 1");

        $statement = $connection->prepare("SELECT requests.request_id, requests.customer_id, requests.departure_lat, requests.departure_lon,
                                requests.destination_lat, requests.destination_lon, requests.statut_course, requests.request_route,
                                requests.customer_id, requests.date_requested, first_name, last_name, requests.distance, requests.request_status, requests.duree
                            FROM requests, user_data
                            WHERE requests.customer_id = customer_id  AND requests.statut_course != ''
                            ORDER BY requests.request_id DESC");
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