<?php

$servername = "localhost";
$username = "root";
$password = "";
$db = "vesselsdb";
try {
    $conn = mysqli_connect($servername, $username, $password);
    //CREATE DATABASE vesselsdb
    $sql = "CREATE DATABASE vesselsdb";
    if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
    } else {
    echo "Error creating database: " . $conn->error;
    }
    mysqli_close($conn);

    // CREATE CONNECTION WITH vesselsdb
    $conn = mysqli_connect($servername, $username, $password, $db);
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected successfully";

    //CREATE DATABASE TABLE vessels IN vesselsdb
    $sql = "CREATE TABLE vessels (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        mmsi INT NOT NULL,
        status INT NOT NULL,
        stationId INT NOT NULL,
        speed INT NOT NULL,
        lon INT NOT NULL,
        lat INT NOT NULL,
        course INT NOT NULL,
        heading INT NOT NULL,
        rot INT NOT NULL,
        timestamp INT NOT NULL
        )";
    if ($conn->query($sql) === TRUE) {
        echo "Table vessels created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
    //CREATE DATABASE TABLE users IN vesselsdb
    $sql = "CREATE TABLE users (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        ip VARCHAR(30) NOT NULL,
        userRequests INT NOT NULL,
        firstTimestamp INT NOT NULL,
        lastTimestamp INT NOT NULL   
        )";
    if ($conn->query($sql) === TRUE) {
        echo "Table users created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    //IMPORT JSON FILE AND GENERATE QUERIES

    // MySQL table's name
    $tableName = 'vessels';
    // Get JSON file and decode contents into PHP arrays/values
    $jsonFile = 'ship_positions.json';
    $jsonData = json_decode(file_get_contents($jsonFile), true);

    // Iterate through JSON and build INSERT statements
    foreach ($jsonData as $id=>$row) {
        $insertPairs = array();
        foreach ($row as $key=>$val) {
            $insertPairs[addslashes($key)] = addslashes($val);
        }
        $insertKeys = '`' . implode('`,`', array_keys($insertPairs)) . '`';
        $insertVals = '"' . implode('","', array_values($insertPairs)) . '"';

        $sql = "INSERT INTO `{$tableName}` ({$insertKeys}) VALUES ({$insertVals});" . "\n";

        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
} catch (mysqli_sql_exception $ex) {
    throw new Exception("Can't connect to the database! \n" . $ex);
}


?>
