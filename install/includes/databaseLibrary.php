<?php

class Database {

    function create_database($data) {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], '');
        if (mysqli_connect_errno()) {
            return FALSE;
        } else {
            $mysqli->query("CREATE DATABASE IF NOT EXISTS " . $data['database']);
            $mysqli->close();
            return TRUE;
        }
    }

    function create_tables($data) {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], $data['database']);
        if (mysqli_connect_errno()) {
            return FALSE;
        } else {
            $query = file_get_contents('assets/quiz.php');
            $mysqli->multi_query($query);
            $mysqli->close();
            return TRUE;
        }
    }

}
