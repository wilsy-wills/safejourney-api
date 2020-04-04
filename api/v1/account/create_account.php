<?php
    function is_email_in_use(pdo $connection, int $user_id, string $email_address) {
        try {
            $statement = $connection->prepare("SELECT user_id FROM user_data WHERE email_address = :email_address AND user_id <> :user_id");
            $statement->bindParam(':user_id', $user_id);
            $statement->bindParam(':email_address', $email_address);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (Exception $error) {
            server_error($error);
        }
        return true;
    }

    function is_contact_in_use(pdo $connection, int $user_id, string $contact) {
        try {
            $statement = $connection->prepare("SELECT user_id FROM user_data WHERE mobile_contact = :mobile_contact AND user_id <> :user_id");
            $statement->bindParam(':user_id', $user_id);
            $statement->bindParam(':mobile_contact', $contact);
            $statement->execute();

            return $statement->rowCount() > 0;

        } catch (Exception $error) {
            server_error($error);
        }
        return true;
    }

    function create_account(pdo $connection) {
        try {
            $connection->beginTransaction();

            if (is_contact_in_use($connection, 0, $_POST['contact'])) {
                echo json_encode(['code' => 2, 'message' => "Mobile contact is already in use"]);
            } else if (is_email_in_use($connection, 0, $_POST['email'])) {
                echo json_encode(['code' => 3, 'message' => "Email address is already in use"]);
            } else {
                $statement = $connection->prepare("INSERT INTO user_data (first_name, last_name, email_address, mobile_contact, user_avatar, 
                       fcm_token, login_password, password_salt, login_token, date_created) VALUES(:first_name, :last_name, :email_address, :mobile_contact, '', 
                       '', :login_password, :password_salt, :login_token, :date_created)");

                $password_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
                $new_password = hash('sha512', $_POST['password'] . $password_salt);
                $login_token = get_token($connection);
                $date_created = date('Y-m-d H:i:s', time());

                $statement->bindParam(':first_name', $_POST['first_name']);
                $statement->bindParam(':last_name', $_POST['last_name']);
                $statement->bindParam(':email_address', $_POST['email']);
                $statement->bindParam(':mobile_contact', $_POST['contact']);
                $statement->bindParam(':login_password', $new_password);
                $statement->bindParam(':password_salt', $password_salt);
                $statement->bindParam(':login_token', $login_token);
                $statement->bindParam(':date_created', $date_created);
                $statement->execute();
                $user_id = $connection->lastInsertId();

                $avatar = time() . $user_id . ".png";
                $path = "../files/avatars/$avatar";
                $status = file_put_contents($path, base64_decode($_POST['avatar']));

                if (!$status) {
                    echo json_encode(['code' => 4, 'msg' => "Error while uploading profile image"]);
                    die();
                }

                $statement = $connection->prepare("UPDATE user_data SET user_avatar = :avatar WHERE user_id = :user_id");
                $statement->bindParam(':avatar', $avatar);
                $statement->bindParam(':user_id', $user_id);
                $statement->execute();
                $connection->commit();

                $statement = $connection->prepare("SELECT first_name, last_name, email_address, mobile_contact, user_avatar, login_token 
                                            FROM user_data WHERE user_id = :user_id LIMIT 1");
                $statement->bindParam(':user_id', $user_id);
                $statement->execute();

                $data = $statement->fetch();
                $data['code'] = 1;
                $data['msg'] = "Account created successfully";
                echo json_encode($data);
            }
        } catch (Exception $error) {
            server_error($error);
        }
    }
