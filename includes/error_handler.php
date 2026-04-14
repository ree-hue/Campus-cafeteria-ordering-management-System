<?php
// Global error handler for Campus Cafeteria System

// Set error reporting based on environment
if (getenv('APP_ENV') === 'production') {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Custom error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";

    // Log the error
    error_log($error_message);

    // Don't display errors in production
    if (getenv('APP_ENV') === 'production') {
        return true;
    }

    // Display user-friendly error for non-fatal errors
    if (!(error_reporting() & $errno)) {
        return false;
    }

    echo "<div style='background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin: 20px; border-left: 4px solid #c33;'>";
    echo "<h3><i class='fas fa-exclamation-triangle'></i> Oops! Something went wrong.</h3>";
    echo "<p>Please try refreshing the page or contact support if the problem persists.</p>";
    if (getenv('APP_ENV') !== 'production') {
        echo "<details style='margin-top: 10px;'>";
        echo "<summary>Technical Details (Development)</summary>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 4px; margin-top: 5px; font-size: 12px;'>$error_message</pre>";
        echo "</details>";
    }
    echo "</div>";

    return true;
}

// Set the custom error handler
set_error_handler("customErrorHandler");

// Custom exception handler
function customExceptionHandler($exception) {
    $error_message = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();

    // Log the exception
    error_log($error_message);

    echo "<div style='background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin: 20px; border-left: 4px solid #c33;'>";
    echo "<h3><i class='fas fa-exclamation-triangle'></i> Oops! Something went wrong.</h3>";
    echo "<p>Please try refreshing the page or contact support if the problem persists.</p>";
    if (getenv('APP_ENV') !== 'production') {
        echo "<details style='margin-top: 10px;'>";
        echo "<summary>Technical Details (Development)</summary>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 4px; margin-top: 5px; font-size: 12px;'>$error_message</pre>";
        echo "</details>";
    }
    echo "</div>";
}

// Set the custom exception handler
set_exception_handler("customExceptionHandler");

// Function to safely redirect with error message
function redirectWithError($url, $error_message) {
    $_SESSION['error_message'] = $error_message;
    header("Location: $url");
    exit();
}

// Function to display session error messages
function displaySessionErrors() {
    if (isset($_SESSION['error_message'])) {
        echo "<div style='background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33;'>";
        echo "<i class='fas fa-exclamation-triangle' style='margin-right: 8px;'></i>";
        echo htmlspecialchars($_SESSION['error_message']);
        echo "</div>";
        unset($_SESSION['error_message']);
    }
}

// Function to safely get POST data
function getPostData($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate phone number (Kenyan format)
function validatePhone($phone) {
    return preg_match('/^254[0-9]{9}$/', $phone);
}

// Function to sanitize output
function safeOutput($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>