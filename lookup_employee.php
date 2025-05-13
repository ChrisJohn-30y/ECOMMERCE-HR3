<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conn.php';

if (isset($_POST['generated_code'])) {
    $code = $_POST['generated_code'];
    file_put_contents('debug.txt', "Code received: " . $code . "\n", FILE_APPEND);

    $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE generatedcode = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->bind_result($employee_id);

    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'employee_id' => $employee_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No code received']);
}
?>
