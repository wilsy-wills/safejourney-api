<?php
    function send_message(pdo $connection) {
        $current_time = date('Y-m-d H:i:s', time());

        $statement = $connection->prepare("INSERT INTO messages(message, sender_id, receiver_id, request_id, date_created)
            VALUES(:message, :sender_id, :receiver_id, :request_id, :date_created)");
        $statement->bindParam(':message', $_POST['message']);
        $statement->bindParam(':sender_id', $_POST['id_envoyeur']);
        $statement->bindParam(':receiver_id', $_POST['id_receveur']);
        $statement->bindParam(':request_id', $_POST['id_requete']);
        $statement->bindParam(':date_created', $current_time);
        $statement->execute();
        $message_id = $connection->lastInsertId();

        if ($_POST['user_cat'] == "user_app") {
            $statement = $connection->prepare("SELECT fcm_id AS fcm_token FROM user_drivers WHERE driver_id = :driver_id LIMIT 1");
            $statement->bindParam(':driver_id', $_POST['id_receveur']);
        } else {
            $statement = $connection->prepare("SELECT fcm_token FROM user_data WHERE user_id = :client_id LIMIT 1");
            $statement->bindParam(':client_id', $_POST['id_receveur']);
        }
        $statement->execute();
        $fcm_token = ($statement->fetch())['fcm_token'];

        if ($fcm_token != "") {
            $statement = $connection->prepare("SELECT message_id, message, request_id, sender_id, receiver_id, date_created
                    FROM messages WHERE message_id = :message_id LIMIT 1");
            $statement->bindParam(':message_id', $message_id);
            $statement->execute();

            $message = ["body" => $statement->fetch(), "title" => "New Message", "tag" => "new_message"];
            send_notification([$fcm_token], $message);
        }

        echo json_encode(['state' => 1]);
    }

