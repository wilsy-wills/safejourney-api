<?php

    define("username", "safejourny_taxi");//safejourny_taxi
    define("password", "mugwanya0754665613");//mugwanya0754665613
    define("host", "localhost");
    define("database", "safejourny_database");

    $current_date = date('Y-m-d H:i:s', time());

    function connect_database() {
        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        try {
            $connection = new PDO("mysql:host=" . host . ";dbname=" . database . ";charset=utf8", username,
                password, $options);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $connection;
        } catch (PDOException $error) {
            server_error($error->getMessage());
            return null;
        }
    }

    function server_error(string $message, Exception $exception = null) {
        header("HTTP/1.1 500");
        echo json_encode(['message' => $message, 'code' => 500]);
        die();
    }

    function success_message(array $array) {
        header("HTTP/1.1 200");
        echo json_encode($array);
        die();
    }

    function post_action() {
        if (isset($_POST, $_POST['operation'])) {
            return $_POST['operation'];
        }
        return '';
    }

    function get_action() {
        if (isset($_GET, $_GET['operation'])) {
            return $_GET['operation'];
        }
        return '';
    }

    function get_token(pdo $connection) {
        $login_token = "";
        $pool = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        for ($index = 0; $index < 200; $index++) {
            $login_token .= $pool[mt_rand(0, count($pool) - 1)];
        }
        $statement = $connection->prepare("SELECT login_token FROM user_data WHERE login_token = :login_token");
        $statement->bindParam(':login_token', $login_token);
        $statement->execute();
        if ($statement->rowCount() == 0) {
            return $login_token;
        }
        return get_token($connection);
    }

    function get_user_id(pdo $connection) {
        $token = "";
        if (isset($_POST, $_POST['user_token'])) {
            $token = $_POST['user_token'];
        }
        if (isset($_GET, $_GET['user_token'])) {
            $token = $_GET['user_token'];
        }
        $token = preg_replace('/\s+/', '', $token);

        $statement = $connection->prepare("SELECT user_id FROM user_data WHERE login_token = :login_token LIMIT 1");
        $statement->bindParam(':login_token', $token);
        $statement->execute();
        $data = $statement->fetch();
        if ($data) {
            $_POST['user_id'] = $data['user_id'];
            $_GET['user_id'] = $data['user_id'];
        } else {
            success_message(['code' => -100, 'message' => "No user with provided token exists"]);
            die();
        }
    }

    function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else {
                if ($unit == "N") {
                    return ($miles * 0.8684);
                } else {
                    return $miles;
                }
            }
        }
    }

    function send_notification($tokens, $message) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = [
            'registration_ids'  => $tokens,
            'body'              => $message,
            'content_available' => true,
            'priority'          => 'high',
        ];

        $fcm_key = "AIzaSyDDdqDb8FA1iImrCVPCNbMC92HTTVWhw4o";

        $headers = ['Authorization:key=' . $fcm_key, 'Content-Type:application/json'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === false) {
            die('Curl Failed:' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }
