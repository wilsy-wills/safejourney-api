<?php
    function get_taxi(pdo $connection) {
        $statement = $connection->prepare("SELECT taxi.id, taxi.numero, taxi.immatriculation, taxi.statut,
                            driver.latitude, driver.longitude, taxi.creer, taxi.modifier, vehicle_type.libelle AS libtypevehicule
                        FROM tj_taxi taxi, tj_type_vehicule vehicle_type, tj_affectation assignment, driver_info driver
                        WHERE taxi.id_type_vehicule = vehicle_type.id AND assignment.id_taxi = taxi.id AND assignment.id_conducteur = driver.driver_id
                          AND taxi.statut = 'yes' AND driver.online = 'yes'");
        $statement->execute();
        $output = $statement->fetchAll();
        echo json_encode(['msg' => $output, 'state' => count($output) == 0 ? 0 : 1]);
    }