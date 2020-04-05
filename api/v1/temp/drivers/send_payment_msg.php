<?php
    function send_payment_message(pdo $connection) {
        try {
            $statement = $connection->prepare("SELECT customer_id, fcm_token 
                            FROM requests INNER JOIN user_data ON requests.customer_id = user_data.user_id 
                            WHERE request_id = :request_id LIMIT 1");
            $statement->bindParam(':request_id', $_POST['request_id']);
            $statement->execute();
            $user_data = $statement->fetch();

            $message = [
                "body"       => "You have arrived at your destination. We kindly ask you to pay the driver. Good continuation!",
                "title"      => "Payment request",
                "tag"        => "payment_request",
                "request_id" => $_POST['request_id']
            ];

            if ($user_data['fcm_token'] != "") {
                send_notification([$user_data['fcm_token']], $message);
            }
            echo json_encode(['msg' => 'Payment request sent successfully', 'state' => 1]);
        } catch (Exception $error) {
            server_error($error);
        }
    }