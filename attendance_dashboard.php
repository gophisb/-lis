<?php
// attendance_dashboard.php
include 'db_connect.php';

// 1. جلب إحصائيات عامة
$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0;
$total_absences = $conn->query("SELECT COUNT(*) FROM attendance WHERE status='absent'")->fetch_row()[0] ?? 0;

// 2. جلب الطلاب الذين تجاوزوا نسبة غياب 15%
$alert_query = "SELECT s.id, s.name, s.parent_phone, COUNT(a.id) as total_days, SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days 
                FROM students s 
                LEFT JOIN attendance a ON s.id = a.student_id 
                GROUP BY s.id 
                HAVING (absent_days / total_days) * 100 >= 15";
$alerts_result = $conn->query($alert_query);

// 3. جلب قائمة الطلاب لعرض بطاقات المتابعة الفردية
$students_query = "SELECT s.id, s.name, COUNT(a.id) as total_days, SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days 
                   FROM students s 
                   LEFT JOIN attendance a ON s.id = a.student_id 
                   GROUP BY s.id";
$students_result = $conn->query($students_query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة إحصائيات الغياب المتقدمة</title>
    <!-- روابط Bootstrap الصحيحة -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-stat { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-5px); }
        .progress { height: 10px; border-radius: 5px; }
        .alert-zone { background-color: #fff5f5; border-right: 5px solid #dc3545; }
        .border-top-success { border-top: 4px solid #198754 !important; }
        .border-top-danger { border-top: 4px solid #dc3545 !important; }
        .border-top-warning { border-top: 4px solid #ffc107 !important; }
    </style>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4 text-center fw-bold text-dark">📊 لوحة إحصائيات ومتابعة الغياب</h2>

    <!-- إشعار النجاح عند الحفظ -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>تم الحفظ بنجاح!</strong> تم تحديث سجلات الغياب وفحص المنظومة تلقائياً.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- القسم الأول: كروت الأرقام السريعة -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card card-stat bg-primary text-white p-4">
                <h5 class="card-title opacity-75">إجمالي الطلاب المسجلين</h5>
                <h2 class="display-5 fw-bold"><?php echo $total_students; ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-stat bg-danger text-white p-4">
                <h5 class="card-title opacity-75">إجمالي حالات الغياب المسجلة</h5>
                <h2 class="display-5 fw-bold"><?php echo $total_absences; ?></h2>
            </div>
        </div>
    </div>

    <!-- القسم الثاني: تنبيهات الخط الأحمر (تجاوز 15%) -->
    <div class="card card-stat mb-5 border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-bold py-3">
            ⚠️ الطلاب في منطقة الخطر (غياب ≥ 15% - تم إرسال SMS)
        </div>
        <div class="card-body">
            <?php if ($alerts_result && $alerts_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>اسم الطالب</th>
                                <th>رقم هاتف الوالد</th>
                                <th>أيام الغياب</th>
                                <th>نسبة الغياب</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = $alerts_result->fetch_assoc()): 
                            $pct = $row['total_days'] > 0 ? ($row['absent_days'] / $row['total_days']) * 100 : 0; ?>
                            <tr class="alert-zone">
                                <td class="fw-bold text-danger"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['parent_phone']); ?></td>
                                <td><?php echo $row['absent_days'] . ' من ' . $row['total_days']; ?> يوم</td>
                                <td class="fw-bold text-danger"><?php echo round($pct, 1); ?>%</td>
                                <td><span class="badge bg-danger">إنذار نشط</span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center my-3">ممتاز! لا يوجد أي طالب تجاوز نسبة الغياب الحرجة حالياً.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- القسم الثالث: بطاقات المتابعة الفردية لكل الطلاب -->
    <h4 class="mb-4 fw-bold text-secondary">📇 بطاقات المتابعة الأكاديمية للطلاب</h4>
    <div class="row g-4">
        <?php if ($students_result && $students_result->num_rows > 0): ?>
            <?php while($student = $students_result->fetch_assoc()): 
                $total = $student['total_days'];
                $absent = $student['absent_days'] ?? 0;
                $pct = $total > 0 ? ($absent / $total) * 100 : 0;
                // تحديد لون الكارت حسب النسبة
                $color_class = "success";
                if ($pct >= 15) $color_class = "danger";
                elseif ($pct >= 10) $color_class = "warning";
            ?>
                <div class="col-md-4">
                    <div class="card card-stat h-100 border-top-<?php echo $color_class; ?> shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($student['name']); ?></h5>
                            <p class="card-text text-muted mb-2">
                                🗓️ أيام الحضور الفعلي: <strong><?php echo $total - $absent; ?></strong> يوم
                            </p>
                            <p class="card-text text-muted mb-3">
                                ❌ أيام الغياب الإجمالي: <strong><?php echo $absent; ?></strong> يوم
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">نسبة الغياب العامة</span>
                                <span class="fw-bold text-<?php echo $color_class; ?>"><?php echo round($pct, 1); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?php echo $color_class; ?>" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-muted">لا توجد بيانات طلاب لعرضها.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- زر العودة للشاشة الرئيسية -->
    <div class="text-center mt-5">
        <a href="index.php" class="btn btn-outline-dark px-4 py-2">⬅️ العودة لصفحة تسجيل الحضور والغياب</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
