<?php

    require_once '../utils.php';
    new Entry();

    class Entry {
        private $connection;

        public function __construct() {
            $this->connection = connect_database();

            if (get_action() == 'is_contact_in_use') {
                require_once 'account/create_account.php';
                echo json_encode(['in_use' => is_contact_in_use($this->connection, 0, $_GET['contact'])]);
            } else if (get_action() == 'is_email_in_use') {
                require_once 'account/create_account.php';
                echo json_encode(['in_use' => is_email_in_use($this->connection, 0, $_GET['email_address'])]);
            } else if (post_action() == "create_account") {
                require_once 'account/create_account.php';
                create_account($this->connection);
            } else if (post_action() == 'update_fcm_token') {
                require_once 'account/update_fcm_token.php';
                update_fcm_token($this->connection);
            } else if (post_action() == 'login_user') {
                require_once 'account/login_user.php';
                login_user($this->connection);
            }
        }

        public function __destruct() {
            $this->connection = null;
        }
    }
