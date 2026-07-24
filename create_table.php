<?php
// create_table.php - نسخة كاشف الأخطاء

// تفعيل إظهار جميع الأخطاء المخفية في السيرفر
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>جاري فحص الاتصال وقاعدة البيانات...</h3>";

// استدعاء ملف الاتصال
if (!file_exists('db_connect.php')) {
    die("<span style='color:red;'>❌ خطأ: ملف db_connect.php غير موجود في هذا المجلد!</span>");
}

include 'db_connect.php';

if (!isset($conn) || !$conn) {
    die("<span style='color:red;'>❌ خطأ: متغير الاتصال conn$ غير معرف أو فشل الاتصال.</span>");
}

// نص أمر إنشاء الجدول
$sql = "CREATE TABLE IF NOT EXISTS `period_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `period_number` INT NOT NULL,
    `status` ENUM('present', 'absent') NOT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_period` (`student_id`, `date`, `period_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "<h2 style='color:green;'>✅ ممتاز! تم إنشاء الجدول بنجاح الآن.</h2>";
} else {
    echo "<h2 style='color:red;'>❌ فشل إنشاء الجدول. خطأ قاعدة البيانات:</h2>" . $conn->error;
}
?>