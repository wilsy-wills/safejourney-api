<?php
    function save_rating(pdo $connection) {
        try {
            $statement = $connection->prepare("SELECT rating_id, rating, course_id, message, date_created, date_modified 
                        FROM user_rating WHERE course_id = :request_id LIMIT 1");
            $statement->bindParam(':request_id', $_POST['request_id']);
            $statement->execute();
            $rating_data = $statement->fetch();

            if ($rating_data) {
                $statement = $connection->prepare("UPDATE user_rating SET rating = :rating, date_modified = :current_date,
                         message = :message WHERE rating_id = :rating_id");
                $statement->bindParam(':', $rating_data['rating_id']);
            } else {
                $statement = $connection->prepare("INSERT INTO user_rating (course_id, rating, message, date_created, date_modified, driver_id) 
                                    VALUES (:request_id, :rating, :message, :current_date, :current_date, :driver_id)");
                $statement->bindParam(':request_id', $_POST['request_id']);
                $statement->bindParam(':driver_id', $_POST['driver_id']);
            }

            $statement->bindParam(':rating', $_POST['rating']);
            $statement->bindParam(':current_date', $current_date);
            $statement->bindParam(':message', $_POST['message']);
            $statement->execute();

            $statement = $connection->prepare("SELECT sum(rating) AS ratings, count(rating_id) AS total 
                    FROM user_rating WHERE driver_id = :driver_id LIMIT 1");
            $statement->bindParam(':driver_id', $_POST['driver_id']);
            $statement->execute();
            $data = $statement->fetch();

            $average_rating = number_format(($data['ratings'] / $data['total']), 2);

            $statement = $connection->prepare("UPDATE user_drivers SET total_raters = :total_raters, total_rating = :total_rating 
                                WHERE driver_id = :driver_id");
            $statement->bindParam(':driver_id', $_POST['driver_id']);
            $statement->bindParam(':total_rating', $data['ratings']);
            $statement->bindParam(':total_raters',  $data['total']);
            $statement->execute();

            echo json_encode(['state' => 1, 'rating' => $average_rating]);

        } catch (Exception $error) {
            server_error($error);
        }
    }