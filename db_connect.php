<?php
// db_connect.php

$host = "localhost";      // اسم السيرفر (غالباً لوكال هوسط)
$user = "root";           // اسم مستخدم قاعدة البيانات
$pass = "";               // كلمة المرور (اتركها فارغة إن كنت تستخدم XAMPP)
$dbname = "school_db";    // اكتب هنا اسم قاعدة البيانات الخاصة بمنصتك

// إنشاء الاتصال
$conn = new mysqli($host, $user, $pass, $dbname);

// التحقق من نجاح الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// ضبط الترميز للغة العربية
$conn->set_charset("utf8mb4");
?>
