<?php
    function make_request(pdo $connection) {
        function cmp($a, $b) {
            return strcmp($a["distance"], $b["distance"]);
        }

        $current_date = date('Y-m-d H:i:s');
        $statement = $connection->prepare("INSERT INTO requests(id_user_app, latitude_depart, longitude_depart, latitude_arrivee, longitude_arrivee,
                       statut, creer, distance, montant, duree, id_conducteur_accepter, statut_course, modifier) VALUES 
                        (:user_id, :lat1, :lng1, :lat2, :lng2,'en cours', :date_heure, :distance, :cout, :duree, 0, '', :date_heure)");

        $statement->bindParam(':lat1', $_POST['lat1']);
        $statement->bindParam(':lng1', $_POST['lng1']);
        $statement->bindParam(':lat2', $_POST['lat2']);
        $statement->bindParam(':lng2', $_POST['lng2']);
        $statement->bindParam(':cout', $_POST['cout']);
        $statement->bindParam(':duree', $_POST['duree']);
        $statement->bindParam(':distance', $_POST['distance']);
        $statement->bindParam(':date_heure', $current_date);
        $statement->bindParam(':user_id', $_POST['user_id']);
        $statement->execute();
        $request_id = $connection->lastInsertId();

        $statement = $connection->prepare("SELECT t.id, t.statut, driver.latitude, driver.longitude, driver.driver_id AS driver_id, fcm_id
                        FROM tj_taxi t, tj_type_vehicule tv, tj_affectation a, driver_info driver
                        WHERE t.id_type_vehicule = tv.id AND a.id_taxi = t.id AND a.id_conducteur = driver.driver_id AND t.statut = 'yes' 
                            AND driver.online != 'no' AND  fcm_id <> ''");
        $statement->execute();

        $output = [];
        foreach ($statement->fetchAll() as $row) {
            $row['distance'] = distance($row['latitude'], $row['longitude'], $_POST['lat1'], $_POST['lng1'], 'K');
            $output[] = $row;
        }
        usort($output, "cmp");

        $tokens = [];
        for ($index = 0; $index < 100 && $index < count($output); $index++) {
            $tokens[] = $output[$index]['fcm_id'];
        }

        $message = ["body" => ['msg' => "You have just received a request from a client", "title" => "Driver Request", "tag" => "request", 'id' => $request_id]];
        if (count($tokens) > 0) {
            send_notification($tokens, $message);
        }

        echo json_encode(['state' => 1]);
    }
