<?php
    use v1\accounts\Accounts;

    require_once '../utils.php';
    new Entry();

    class Entry {
        private $connection;

        public function __construct() {
            $this->connection = connect_database();

            if (get_action() == 'is_contact_in_use') {
                echo json_encode(['in_use' => (new Accounts($this->connection))->is_contact_in_use(0, $_GET['contact'])]);
            } else if (get_action() == 'is_email_in_use') {
                echo json_encode(['in_use' => (new Accounts($this->connection))->is_email_in_use(0, $_GET['email_address'])]);
            } else if (post_action() == "create_account") {
                (new Accounts($this->connection))->create_account();
            } else if (post_action() == 'update_fcm_token') {
                (new Accounts($this->connection))->update_fcm_token();
            } else if (post_action() == 'login_user') {
                (new Accounts($this->connection))->login_user();
            }
        }

        public function __destruct() {
            $this->connection = null;
        }
    }

