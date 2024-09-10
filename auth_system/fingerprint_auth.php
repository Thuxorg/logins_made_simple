<?php
require 'force_https.php';
session_start();
require 'db_connect.php';
require 'vendor/autoload.php';

use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialDescriptor;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    
    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $rpEntity = new PublicKeyCredentialRpEntity('YourApp', 'example.com');
        $userEntity = new PublicKeyCredentialUserEntity($username, $user['id'], $username);
        $challenge = random_bytes(32);
        
        $publicKeyCredentialCreationOptions = new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            $challenge,
            [new PublicKeyCredentialParameters(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, -7)]
        );

        $_SESSION['challenge'] = base64_encode($challenge);
        $_SESSION['user_id'] = $user['id'];

        echo json_encode($publicKeyCredentialCreationOptions);
    } else {
        echo "User not found";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>