
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establishing a connection with the MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database"; // Replace with your database name

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch weather data
if (isset($_GET['city'])) {
    // Get the searched city from the search bar
    $searchedCity = $_GET['city'];

    // Your API key
    $apiKey = 'da98a262e080e996ea17f7ddef9d765e'; // Replace with your OpenWeatherMap API key
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=$searchedCity&appid=$apiKey&units=metric";

    $curl = curl_init($apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $apiData = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($apiData, true);

    if ($data && $data['cod'] === 200) {
        $date = date('Y-m-d');
        $temperature = $data['main']['temp'];
        $humidity = $data['main']['humidity'];
        $windSpeed = $data['wind']['speed'];
        $pressure = $data['main']['pressure'];

        // Insert the data into the database
        $sql = "INSERT INTO weatherapp (date, temperature, humidity, wind, pressure, city) VALUES ('$date', '$temperature', '$humidity', '$windSpeed', '$pressure', '$searchedCity')";

        if (mysqli_query($conn, $sql)) {
            // Retrieves the inserted data from the database and return it as JSON
            $sql = "SELECT * FROM weatherapp WHERE city='$searchedCity'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                // Prepare the weather data as an associative array
                $weatherData = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $weatherData[] = array(
                        'date' => $row['date'],
                        'city' => $row['city'],
                        'temperature' => $row['temperature'],
                        'humidity' => $row['humidity'],
                        'windSpeed' => $row['wind'],
                        'pressure' => $row['pressure']
                    );
                }

                // Return the weather data as JSON
                header('Content-Type: application/json');
                echo json_encode($weatherData);
            }
        } else {
            $response = array('error' => "Error inserting data: " . mysqli_error($conn));
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    } else {
        $response = array('error' => "Failed to fetch weather data for the city: $searchedCity");
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} else {
    // Retrieves the weather data from the database for the searched city
    if (isset($_GET['city'])) {
        $searchedCity = $_GET['city'];
    } else {
        $searchedCity = "Mesa";
    }

    $sql = "SELECT * FROM weatherapp WHERE city='$searchedCity'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Prepare the weather data as an associative array
        $weatherData = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $weatherData[] = array(
                'date' => $row['date'],
                'city' => $row['city'],
                'temperature' => $row['temperature'],
                'humidity' => $row['humidity'],
                'windSpeed' => $row['wind'],
                'pressure' => $row['pressure']
            );
        }

        // Return the weather data as JSON
        header('Content-Type: application/json');
        echo json_encode($weatherData);
    } else {
        $response = array('error' => "No weather data available for the city: $searchedCity");
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

mysqli_close($conn);
?>
