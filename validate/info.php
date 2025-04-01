<?php
session_start();
if (!isset($_SESSION['student_number'])) {
    header("Location: ../validate/graduate.php");
    exit();
}

require '../includes/config.php';

// Function to fetch name based on ID
function getNameFromId($pdo, $table, $id_column, $id_value) {
    $stmt = $pdo->prepare("SELECT name FROM $table WHERE $id_column = ?");
    $stmt->execute([$id_value]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['name'] ?? '';  // Return the name or empty string if not found
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $permanent_address = trim($_POST['address'] ?? '');
    $mobile_number = trim($_POST['mobile'] ?? '');
    $civil_status = trim($_POST['civilstatus'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $region_id = trim($_POST['region_id'] ?? '');
    $province_id = trim($_POST['province_id'] ?? '');
    $municipality_id = trim($_POST['city_id'] ?? '');
    $barangay_id = trim($_POST['barangay_id'] ?? '');
    $student_number = $_SESSION['student_number'];

    $region_name = getNameFromId($pdo, 'regions', 'reg_code', $region_id);
    $province_name = getNameFromId($pdo, 'provinces', 'prv_code', $province_id);
    $municipality_name = getNameFromId($pdo, 'municipalities', 'mun_code', $municipality_id);
    $barangay_name = getNameFromId($pdo, 'barangays', 'bgy_code', $barangay_id);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE graduates SET 
            email = ?, 
            permanent_address = ?, 
            mobile_number = ?, 
            civil_status = ?, 
            gender = ?, 
            regions = ?, 
            provinces = ?, 
            municipalities = ?, 
            barangays = ? 
            WHERE student_number = ?");

        $stmt->execute([
            $email,
            $permanent_address,
            $mobile_number,
            $civil_status,
            $gender,
            $region_name,
            $province_name,
            $municipality_name,
            $barangay_name,
            $student_number
        ]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Data updated successfully!']);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}