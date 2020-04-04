<?php
    function get_request(pdo $connection) {
        $statement = $connection->prepare("SELECT requests.id, requests.id_user_app, requests.latitude_depart, requests.longitude_depart,
                                            requests.latitude_arrivee, requests.longitude_arrivee, requests.statut, requests.id_conducteur_accepter, 
                                            requests.id_user_app, requests.creer, user_data.first_name, user_data.last_name, requests.distance, 
                                            requests.statut_course, requests.montant,requests.duree
                                        FROM requests, user_data
                                        WHERE requests.id_user_app = user_data.user_id  AND requests.statut='en cours'
                                        ORDER BY requests.id DESC");
        $statement->execute();
        $data = $statement->fetchAll();
        echo json_encode(['msg' => $data, 'state' => (count($data) == 0) ? 0 : 1]);
    }