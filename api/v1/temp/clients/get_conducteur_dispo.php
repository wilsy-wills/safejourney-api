<?php

    function get_available_drivers(pdo $connection) {
        function cmp($a, $b) {
            return strcmp($a["distance"], $b["distance"]);
        }

        $statement = $connection->prepare("SELECT driver_taxi.id, driver_taxi.statut, latitude, longitude, driver_id, first_name,
                        last_name, driver_taxi.immatriculation, driver_taxi.numero
                    FROM vehicles driver_taxi, vehicle_types vehicle_type, tj_affectation assignment, user_drivers, user_data
                    WHERE driver_taxi.id_type_vehicule = vehicle_type.type_id AND assignment.id_taxi = driver_taxi.id 
                      AND assignment.id_conducteur = driver_id AND driver_taxi.statut = 'yes' AND online != 'no'");
        $statement->execute();
        $result = $statement->fetchAll();

        foreach ($result as $row) {
            $row['distance'] = distance($row['latitude'], $row['longitude'], $_POST['lat1'], $_POST['lng1'], 'K');
            $output[] = $row;
        }

        usort($output, "cmp");

        echo json_encode(['msg' => $output, 'state' => count($output) == 0 ? 0 : 1]);
    }