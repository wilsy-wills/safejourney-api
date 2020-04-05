<?php
    function get_taxi(pdo $connection) {
        $statement = $connection->prepare("SELECT taxi.id, taxi.numero, taxi.immatriculation, taxi.statut,
                            driver.latitude, driver.longitude, taxi.creer, taxi.modifier, vehicle_type.type_name AS libtypevehicule
                        FROM vehicles taxi, vehicle_types vehicle_type, tj_affectation assignment, user_drivers driver
                        WHERE taxi.id_type_vehicule = vehicle_type.type_id AND assignment.id_taxi = taxi.id AND assignment.id_conducteur = driver.driver_id
                          AND taxi.statut = 'yes' AND driver.online = 'yes'");
        $statement->execute();
        $output = $statement->fetchAll();
        echo json_encode(['msg' => $output, 'state' => count($output) == 0 ? 0 : 1]);
    }