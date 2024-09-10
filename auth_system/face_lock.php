<?php
require 'force_https.php';
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputDescriptor = json_decode($_POST['faceDescriptor'], true);

    // Retrieve user's face descriptors from the database
    $stmt = $conn->prepare("SELECT id, face_descriptors FROM users");
    $stmt->execute();
    $result = $stmt->get_result();

    $matchedUserId = null;

    while ($row = $result->fetch_assoc()) {
        $storedDescriptors = json_decode($row['face_descriptors'], true);

        // Compare the input descriptor with stored descriptors
        foreach ($storedDescriptors as $storedDescriptor) {
            $distance = calculateEuclideanDistance($inputDescriptor, $storedDescriptor);
            if ($distance < 0.6) { // Adjust threshold as needed
                $matchedUserId = $row['id'];
                break 2; // Break out of both loops
            }
        }
    }

    if ($matchedUserId) {
        // Successful login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $matchedUserId;
        echo "Face lock login successful! Welcome, user ID: " . htmlspecialchars($matchedUserId) . ".";
    } else {
        echo "Face lock login failed. No matching face found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}

// Function to calculate Euclidean distance between two descriptors
function calculateEuclideanDistance($descriptor1, $descriptor2) {
    $distance = 0.0;
    for ($i = 0; $i < count($descriptor1); $i++) {
        $distance += pow($descriptor1[$i] - $descriptor2[$i], 2);
    }
    return sqrt($distance);
}
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $face_descriptor = json_decode($_POST['faceDescriptor'], true);

        // Update user's face descriptors in the database
        $stmt = $conn->prepare("UPDATE users SET face_descriptors = ? WHERE id = ?");
        
        //this code was giving an error so i made it as comment so as i test the system and see if it works
        //$stmt->bind_param("si", json_encode($face_descriptor), $user_id);


        if ($stmt->execute()) {
            echo "Face lock updated successfully!";
        } else {
            echo "Error updating face lock: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "User session not found. Please log in.";
    }

?>