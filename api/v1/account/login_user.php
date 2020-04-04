<?php
    function login_user(pdo $connection) {
        $statement = $connection->prepare("SELECT first_name, last_name, email_address, mobile_contact, user_avatar, login_token 
                                            FROM user_data WHERE mobile_contact = :mobile_contact LIMIT 1");
        $statement->bindParam(':mobile_contact', $_POST['mobile_contact']);
        $statement->execute();

        $data = $statement->fetch();
        if ($data) {
            $data['code'] = 1;
            $data['msg'] = 'Login completed successfully';
            echo json_encode($data);
        } else {
            echo json_encode(['code' => 2, 'msg' => 'No user found']);
        }

    }