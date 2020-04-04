<?php

    function update_fcm_token(pdo $connection) {
        try {
            get_user_id($connection);

            $statement = $connection->prepare("UPDATE user_data SET fcm_token = :fcm_token WHERE user_id = :user_id");
            $statement->bindParam(':user_id', $user_id);
            $statement->bindParam(':fcm_token', $_POST['fcm_token']);
            $statement->execute();

            echo json_encode(['code' => 1, 'message' => 'FCM token has been updated successfully']);
        } catch (Exception $error) {
            server_error($error);
        }
    }
