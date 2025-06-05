<?php
require_once 'Model/Database.php';
require_once 'Model/Doctor.php';

header('Content-Type: application/json');

$specialty = isset($_GET['specialty']) ? $_GET['specialty'] : null;

$db = new Database();
$doctor = new Doctor($db->link);
$doctors = $doctor->fetchDoctorsList($specialty);

echo json_encode($doctors);
?>
