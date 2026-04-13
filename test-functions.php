<?php
require_once 'includes/functions.php';

echo "<h1>Testing Functions</h1>";

if (function_exists('sanitize')) {
    echo "✓ sanitize() function exists<br>";
    $test = sanitize("<script>alert('test')</script>");
    echo "Sanitize test: " . $test . "<br>";
} else {
    echo "✗ sanitize() function NOT found<br>";
}

if (function_exists('uploadFile')) {
    echo "✓ uploadFile() function exists<br>";
} else {
    echo "✗ uploadFile() function NOT found<br>";
}

if (function_exists('createEvent')) {
    echo "✓ createEvent() function exists<br>";
} else {
    echo "✗ createEvent() function NOT found<br>";
}

echo "<br><a href='register.php'>Go to Register Page</a>";
?>