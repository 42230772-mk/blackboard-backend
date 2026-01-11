<?php
header("Content-Type: application/json");

// Full path to python script
$pythonPath = "python"; // should work if python is in PATH
$scriptPath = __DIR__ . "/../python_scripts/test_python.py";

// Run python script
$output = shell_exec("$pythonPath $scriptPath 2>&1");

echo $output;
?>
