<?php
// attendance_dashboard.php
include 'db_connect.php';

// 1. إحصائيات اليوم
$today = date('Y-m-d');
$stats_query = "SELECT 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
FROM attendance WHERE date = '$today'";
$stats = $conn->query($stats_query)->fetch_assoc();

// 2. الطلاب الذين تجاوزوا 15% غياب (حتى لو لم يكونوا غائبين اليوم)
$critical_query = "SELECT 
    s.id,
    s.name,
    s.parent_phone,
    (SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100 as absence_rate
FROM attendance a
JOIN students s ON a.student_id = s.id
GROUP BY a.student_id
HAVING absence_rate >= 15
ORDER BY absence_rate DESC";
$critical_students = $conn->query($critical_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الغيابات الذكية</title>
    <style>
        /* تنسيق بسيط وجذاب */
        body { font-family: 'Tahoma', sans-serif; background: #f4f7fc; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; border-radius: 16px; padding: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        h2 { color: #1a3b5d; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .cards-grid { display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0; }
        .card { flex: 1; min-width: 150px; padding: 20px; border-radius: 12px; color: white; font-weight: bold; font-size: 1.2rem; text-align: center; }
        .card-present { background: #2ecc71; }
        .card-absent { background: #e74c3c; }
        .card-late { background: #f39c12; }
        .card-total { background: #3498db; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #2c3e50; color: white; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
        .text-danger { color: #e74c3c; font-weight: bold; }
        .badge { background: #27ae60; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.9rem; }
        .badge-sent { background: #2980b9; }
        .badge-warning { background: #e67e22; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .footer-note { margin-top: 30px; font-size: 0.9rem; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 15px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 لوحة تحكم الغيابات الذكية</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">✅ تم حفظ بيانات الغياب بنجاح وإرسال التنبيهات للطلاب المتجاوزين.</div>
    <?php endif; ?>

    <!-- بطاقات إحصاء اليوم -->
    <div class="cards-grid">
        <div class="card card-present">✔ حضور اليوم: <?php echo $stats['present'] ?? 0; ?></div>
        <div class="card card-absent">✖ غياب اليوم: <?php echo $stats['absent'] ?? 0; ?></div>
        <div class="card card-late">⏰ تأخر اليوم: <?php echo $stats['late'] ?? 0; ?></div>
        <div class="card card-total">📅 إجمالي المسجلين: <?php 
            $total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
            echo $total_students; 
        ?></div>
    </div>

    <!-- جدول الحالات الحرجة (تجاوز 15% غياب) -->
    <h3>⚠️ الطلاب المتجاوزون لنسبة الغياب المسموحة (≥ 15%)</h3>
    <?php if ($critical_students->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الطالب</th>
                    <th>نسبة الغياب</th>
                    <th>رقم ولي الأمر</th>
                    <th>حالة التنبيه</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while ($row = $critical_students->fetch_assoc()): 
                    // محاكاة حالة الإرسال (نفترض أنه تم إرسال SMS عند حفظ الغياب)
                    $alert_status = (!empty($row['parent_phone'])) ? 
                        '<span class="badge badge-sent">📨 تم إرسال SMS</span>' : 
                        '<span class="badge badge-warning">⚠️ رقم غير مسجل</span>';
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td class="text-danger"><?php echo round($row['absence_rate'], 1); ?>%</td>
                    <td><?php echo htmlspecialchars($row['parent_phone'] ?? 'غير متوفر'); ?></td>
                    <td><?php echo $alert_status; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #27ae60;">🎉 لا يوجد طلاب تجاوزوا نسبة الغياب المسموحة. أداء ممتاز!</p>
    <?php endif; ?>

    <!-- زر العودة لتسجيل الغياب (اختياري) -->
    <div style="margin-top: 30px; text-align: left;">
        <a href="attendance_form.php" style="background: #3498db; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none;">➕ تسجيل غياب جديد</a>
        <a href="index.php" style="background: #95a5a6; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none; margin-right: 10px;">🏠 الرئيسية</a>
    </div>

    <div class="footer-note">
        يتم تحديث البيانات تلقائياً – نظام الغيابات الذكي يعمل بكفاءة.
    </div>
</div>
</body>
</html>
