<?php
// save_period_attendance.php - الملف النهائي (ينشئ الجدول تلقائياً ويُرسل التنبيهات)
session_start();
include 'db_connect.php';

// ============================================================
// 1. إنشاء جدول الحصص تلقائياً (بدون الحاجة لـ phpMyAdmin)
// ============================================================
$create_table_sql = "CREATE TABLE IF NOT EXISTS `period_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `period_number` INT NOT NULL,
    `status` ENUM('present', 'absent') NOT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_period` (`student_id`, `date`, `period_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($create_table_sql) === FALSE) {
    die("❌ فشل إنشاء الجدول: " . $conn->error);
}

// ============================================================
// 2. معالجة بيانات النموذج وحفظ الغياب
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $period_number = intval($_POST['period_number']);
    $absent_records = $_POST['attendance'] ?? []; // المفاتيح هي student_id

    // جلب جميع الطلاب المسجلين
    $all_students = $conn->query("SELECT id, name, parent_phone FROM students");
    
    // مصفوفة لتخزين تنبيهات الحصة الأولى
    $alert_messages = [];

    while ($student = $all_students->fetch_assoc()) {
        $student_id = $student['id'];
        // إذا كان الطالب موجوداً في مصفوفة الغياب ← غائب، وإلا ← حاضر
        $status = isset($absent_records[$student_id]) ? 'absent' : 'present';

        // حفظ السجل في جدول الحصص الجديد
        $stmt = $conn->prepare("INSERT INTO period_attendance (student_id, date, period_number, status) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE status = ?");
        $stmt->bind_param("isiss", $student_id, $date, $period_number, $status, $status);
        $stmt->execute();

        // 🚨 تنبيه فوري للحصة الأولى (الأكثر طلباً للأمان)
        if ($status == 'absent' && $period_number == 1) {
            if (!empty($student['parent_phone'])) {
                $msg = "تنبيه غياب عاجل: نفيدكم بعدم حضور ابنكم/ابنتكم (" . $student['name'] . ") للحصة الأولى اليوم " . $date . ". يرجى مراجعة الأمر فوراً.";
                $clean_phone = preg_replace('/[^0-9]/', '', $student['parent_phone']);
                $whatsapp_url = "https://wa.me/" . $clean_phone . "?text=" . urlencode($msg);
                
                $alert_messages[] = [
                    'name' => $student['name'],
                    'url'  => $whatsapp_url
                ];
            }
        }
    }

    // ============================================================
    // 3. عرض شاشة النجاح وأزرار واتساب (إن وجدت)
    // ============================================================
    echo "<!DOCTYPE html>
    <html dir='rtl' lang='ar'>
    <head><meta charset='UTF-8'><title>تم الحفظ بنجاح</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>body{background:#f4f7fc;font-family:Tahoma;padding:30px;text-align:center;}</style>
    </head>
    <body>
    <div class='container'>
        <div class='card shadow p-5 mt-5'>
            <h2 class='text-success'>✅ تم حفظ غياب الحصة رقم ($period_number) بنجاح!</h2>";

    // إذا كانت الحصة الأولى وتوجد غيابات، نعرض أزرار المراسلة الفورية
    if ($period_number == 1 && !empty($alert_messages)) {
        echo "<hr><h4 class='text-danger'>🚨 تنبيهات غياب الحصة الأولى (اضغط لإرسال واتساب فوراً):</h4><div class='row justify-content-center mt-3'>";
        foreach ($alert_messages as $alert) {
            echo "<div class='col-md-6 mb-2'>
                    <a href='".$alert['url']."' target='_blank' 
                       class='btn btn-success w-100 py-2 fw-bold shadow-sm'>
                       <i class='fa-brands fa-whatsapp'></i> مراسلة ولي أمر: " . htmlspecialchars($alert['name']) . "
                    </a>
                  </div>";
        }
        echo "</div>";
    } elseif ($period_number == 1) {
        echo "<p class='text-muted'>🎉 لا يوجد غياب في الحصة الأولى اليوم.</p>";
    } else {
        echo "<p class='text-muted'>📌 تم الحفظ. لا توجد تنبيهات عاجلة لهذه الحصة.</p>";
    }

    echo "<hr><a href='index_periods.php' class='btn btn-primary px-5 py-2'>⬅️ العودة لتسجيل حصة أخرى</a>
          <a href='attendance_dashboard.php' class='btn btn-outline-dark px-5 py-2 me-2'>📊 عرض الإحصائيات</a>
        </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    </body></html>";
    exit();
}
?>
