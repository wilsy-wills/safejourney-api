<?php

    function get_messages(pdo $connection) {
        try {
            $statement = $connection->prepare("SELECT message_id, message, request_id, sender_id, receiver_id, date_created 
                                FROM messages WHERE request_id = :request_id ORDER BY date_created");
            $statement->bindParam(':request_id', $_POST['id_requete']);
            $statement->execute();
            $data = $statement->fetchAll();
            echo json_encode(['state' => count($data) == 0 ? 0 : 1, 'msg' => $data]);
        } catch (Exception $error) {
            server_error($error);
        }
    }