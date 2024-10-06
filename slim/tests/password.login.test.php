<?php
// Password
$plainPassword = 'Password123!'; // Your plain text password

// Hash con md5 y forzar un length de 16
$hashedPassword = substr(md5($plainPassword), 0, 16); // MD5 hash trimmed to 16 chars

// Simular el input del user
$userInputPassword = 'Password123!'; // Password input for testing

// Function
function isPasswordValid($inputPassword, $storedHash) {
    // Comparamos
    return substr(md5($inputPassword), 0, 16) === $storedHash;
}

// Chequeamos
if (isPasswordValid($userInputPassword, $hashedPassword)) {
    echo "Password is valid!";
} else {
    echo "Invalid password.";
}


echo "\nNew hashed password: " . $hashedPassword;
