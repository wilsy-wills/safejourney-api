<?php
    function set_driver_position(pdo $connection) {
        $statement = $connection->prepare("UPDATE driver_info SET latitude = :latitude, longitude = :longitude, 
                       updated = :updated WHERE driver_id = :driver_id");
        $statement->bindParam(':latitude', $_POST['latitude']);
        $statement->bindParam(':longitude', $_POST['longitude']);
        $statement->bindParam(':updated', $current_date);
        $statement->bindParam(':driver_id', $_POST['user_id']);
        $statement->execute();

        echo json_encode(['state' => 1]);
    }
