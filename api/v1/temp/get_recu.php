<?php
    function get_recu(pdo $connection) {
        $statement = $connection->prepare("SELECT * FROM tj_recu WHERE id_course= :request_id AND id_user_app = :client_id");
        $statement->bindParam(':request_id', $_POST['request_id']);
        $statement->bindParam(':client_id', $_POST['client_id']);
        $statement->execute();
        $data = $statement->fetchAll();

        echo json_encode(['msg' => $data, 'state' => (count($data) == 0) ? 0 : 1]);
    }