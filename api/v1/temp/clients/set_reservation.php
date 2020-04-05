<?php
    function send_reservation(pdo $connection) {
        $current_time = date('Y-m-d H:i:s', time());

        $statement = $connection->prepare("INSERT INTO tj_reservation_taxi(id_user_app, latitude_depart, longitude_depart, latitude_arrivee,
                                longitude_arrivee, cout, distance, date_depart, heure_depart, statut, creer, contact, duree) VALUES
                                (:user_id, :lat1, :lng1, :lat2, :lng2, :cout, :distance, :date_depart, :heure_depart, 'en cours', :date_heure,
                                 :contact, :duree)");

        $statement->bindParam(':user_id', $_POST['user_id']);
        $statement->bindParam(':lat1', $_POST['lat1']);
        $statement->bindParam(':lng1', $_POST['lng1']);
        $statement->bindParam(':lat2', $_POST['lat2']);
        $statement->bindParam(':lng2', $_POST['lng2']);
        $statement->bindParam(':cout', $_POST['cout']);
        $statement->bindParam(':distance', $_POST['distance']);
        $statement->bindParam(':date_depart', $_POST['date_depart']);
        $statement->bindParam(':heure_depart', $_POST['heure_depart']);
        $statement->bindParam(':date_heure', $current_time);
        $statement->bindParam(':contact', $_POST['contact']);
        $statement->bindParam(':duree', $_POST['duree']);
        $statement->execute();
        $reservation_id = $connection->lastInsertId();

        $title = str_replace("'", "\'", "Taxi booking");
        $msg = str_replace("'", "\'", "A customer has just sent a taxi reservation request");
        $message = ["body" => ['msg' => $msg, 'id' => $reservation_id, "title" => $title, "tag" => "reservation"]];

        $statement = $connection->prepare("SELECT fcm_id FROM user_drivers WHERE fcm_id <> ''");
        $statement->execute();
        $tokens = [];
        foreach ($statement->fetchAll() as $user) {
            $tokens[] = $user['fcm_id'];
        }

        if (count($tokens) > 0) {
            send_notification($tokens, $message);
        }

        echo json_encode(['state' => 1]);
    }