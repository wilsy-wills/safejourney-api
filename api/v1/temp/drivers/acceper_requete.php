<?php
    function accept_request(pdo $connection) {
        try {
            $current_time = date('Y-m-d H:i:s');

            $statement = $connection->prepare("SELECT id, id_user_app, statut, fcm_token 
                    FROM requests INNER JOIN user_data ON id_user_app = user_id WHERE id = :request_id LIMIT 1");
            $statement->bindParam(':request_id', $_POST['id_requete']);
            $statement->execute();
            $request_data = $statement->fetch();
            if (!$request_data) {
                echo json_encode(['state' => 3, 'msg' => "No request was found with the request ID"]);
                die();
            }

            if ($request_data['statut'] == 'en cours') {
                echo json_encode(['state' => 2, 'msg' => 'Request was already taken up by someone else']);
                die();
            }

            $statement = $connection->prepare("UPDATE requests SET statut='accepter', statut_course = 'en cours', id_conducteur_accepter = :driver_id, 
                      modifier = :date_heure WHERE id = :request_id");
            $statement->bindParam(':driver_id', $_POST['id_conducteur']);
            $statement->bindParam(':request_id', $_POST['id_requete']);
            $statement->bindParam(':date_heure', $current_time);
            $statement->execute();

            $driver_tokens = [];
            $statement = $connection->prepare("SELECT fcm_id FROM driver_info WHERE fcm_id <> ''");
            $statement->execute();
            foreach ($statement->fetchAll() as $row) {
                $driver_tokens[] = $row['fcm_id'];
            }
            if (count($driver_tokens) > 0) {
                $message = ["body" => "One of your colleagues has just validated a request", "title" => "Request acceptance", "tag" => "other_validated", 'id' => $_POST['id_requete']];
                send_notification($driver_tokens, $message);
            }

            if ($request_data['fcm_token'] != '') {
                $message = ["body"  => "A driver has just validated your request. Please provide him with the necessary information",
                            "title" => "Request Accepted", "tag" => "request_accepted", "id" => $_POST['id_requete']];
                send_notification([$request_data['fcm_token']], $message);
            }
            echo json_encode(['state' => 1, 'msg' => 'Request successfully accepted']);
        } catch (Exception $error) {
            server_error($error);
        }
    }
