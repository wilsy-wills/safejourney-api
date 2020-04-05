<?php

    namespace v1\requests;

    use PDO;

    class Requests {

        private $connection;

        public function __construct(pdo $connection) {
            $this->connection = $connection;
        }

        public function __destruct() {
            $this->connection = null;
        }

        function make_request() {

        }
    }