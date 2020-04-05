<?php
    function get_request(pdo $connection) {
        $statement = $connection->prepare("SELECT requests.request_id, requests.customer_id, requests.departure_lat, requests.departure_lon,
                                            requests.destination_lat, requests.destination_lon, requests.statut, requests.request_route, 
                                            requests.customer_id, requests.date_requested, user_data.first_name, user_data.last_name, requests.distance, 
                                            requests.statut_course, requests.request_status,requests.duree
                                        FROM requests, user_data
                                        WHERE requests.customer_id = user_data.user_id  AND requests.statut='en cours'
                                        ORDER BY requests.request_id DESC");
        $statement->execute();
        $data = $statement->fetchAll();
        echo json_encode(['msg' => $data, 'state' => (count($data) == 0) ? 0 : 1]);
    }