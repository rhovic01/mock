<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // 'admin' or 'officer'

    // Ensure contact number is exactly 9 digits
    if (!preg_match("/^[0-9]{10}$/", $contact_number)) {
        die("Invalid contact number. Enter 10 digits only (e.g., 9912345678).");
    }

    // Format contact number to include +63
    $formatted_contact = "+63" . $contact_number;

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (first_name, last_name, username, email, contact_number, password, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $formatted_contact, $hashed_password, $role);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // [Keep all your existing PHP validation and database logic exactly the same]
    // Only updating the success/error message output to match your design system

    if ($stmt->execute()) {
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Registration Successful | CICT Inventory</title>
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Google Fonts -->
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --dark-blue: #011f4b;
                    --medium-blue: #03396c;
                    --light-blue: #005b96;
                    --pale-blue: #6497b1;
                    --very-pale-blue: #b3cde0;
                    --off-white: #eeeeee;
                }
                
                body {
                    font-family: 'Montserrat', sans-serif;
                    background-color: var(--off-white);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0;
                }
                
                .success-card {
                    background-color: rgba(255, 255, 255, 0.9);
                    border-radius: 16px;
                    box-shadow: 0 8px 32px rgba(1, 31, 75, 0.1);
                    padding: 3rem;
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                    animation: fadeIn 0.5s ease-out;
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                .success-icon {
                    color: var(--light-blue);
                    font-size: 4rem;
                    margin-bottom: 1.5rem;
                }
                
                h2 {
                    color: var(--dark-blue);
                    font-weight: 700;
                    margin-bottom: 1rem;
                }
                
                p {
                    color: var(--medium-blue);
                    margin-bottom: 2rem;
                }
                
                .spinner {
                    width: 3rem;
                    height: 3rem;
                    border: 0.25rem solid var(--very-pale-blue);
                    border-top-color: var(--light-blue);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 2rem auto;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Registration Successful!</h2>
                <p>You're being redirected to the login page...</p>
                <div class="spinner"></div>
            </div>
            
            <!-- Font Awesome -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
            <script>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            </script>
        </body>
        </html>
HTML;
        exit();
    } else {
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Registration Error | CICT Inventory</title>
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Google Fonts -->
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --dark-blue: #011f4b;
                    --medium-blue: #03396c;
                    --light-blue: #005b96;
                    --pale-blue: #6497b1;
                    --very-pale-blue: #b3cde0;
                    --off-white: #eeeeee;
                }
                
                body {
                    font-family: 'Montserrat', sans-serif;
                    background-color: var(--off-white);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0;
                }
                
                .error-card {
                    background-color: rgba(255, 255, 255, 0.9);
                    border-radius: 16px;
                    box-shadow: 0 8px 32px rgba(1, 31, 75, 0.1);
                    padding: 3rem;
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                }
                
                .error-icon {
                    color: #dc3545;
                    font-size: 4rem;
                    margin-bottom: 1.5rem;
                }
                
                h2 {
                    color: var(--dark-blue);
                    font-weight: 700;
                    margin-bottom: 1rem;
                }
                
                .btn-primary {
                    background-color: var(--light-blue);
                    border: none;
                    padding: 0.75rem 1.5rem;
                    font-weight: 600;
                    border-radius: 8px;
                    margin-top: 1.5rem;
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h2>Registration Error</h2>
                <p>Could not complete registration. Please try again.</p>
                <button onclick="window.history.back()" class="btn btn-primary">Go Back</button>
            </div>
            
            <!-- Font Awesome -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
        </body>
        </html>
HTML;
    }

    $stmt->close();
    $conn->close();
}
}
