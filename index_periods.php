<?php
// index_periods.php - واجهة تسجيل الغياب بالحصة الدراسية
include 'db_connect.php';

// جلب قائمة الطلاب من جدولك الحالي
$students = $conn->query("SELECT id, name FROM students ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الغياب بالحصة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .attendance-card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="card attendance-card p-4">
        <h3 class="mb-4 fw-bold text-primary text-center">⏱️ منظومة تسجيل الغياب بالحصة الدراسية</h3>
        <form action="save_period_attendance.php" method="POST">
            <!-- اختيارات التوقيت والحصة -->
            <div class="row g-3 mb-4 bg-light p-3 rounded">
                <div class="col-md-6">
                    <label class="form-label fw-bold">📅 تاريخ اليوم:</label>
                    <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">⏰ اختر الحصة الدراسية:</label>
                    <select name="period_number" class="form-select fw-bold text-primary" required>
                        <option value="1">الحصة الأولى (08:00 - 08:45)</option>
                        <option value="2">الحصة الثانية (08:45 - 09:30)</option>
                        <option value="3">الحصة الثالثة (09:45 - 10:30)</option>
                        <option value="4">الحصة الرابعة (10:30 - 11:15)</option>
                        <option value="5">الحصة الخامسة (11:30 - 12:15)</option>
                    </select>
                </div>
            </div>

            <!-- جدول رصد الطلاب -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>👤 اسم الطالب</th>
                            <th width="30%">❌ الحالة (فعّل في حالة الغياب)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($students && $students->num_rows > 0): ?>
                            <?php while($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-start fw-bold ps-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="attendance[<?php echo $row['id']; ?>]" value="absent" id="switch_<?php echo $row['id']; ?>">
                                            <label class="form-check-label text-danger fw-bold" for="switch_<?php echo $row['id']; ?>">غائب ❌</label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-muted">لا يوجد طلاب مسجلين لعرضهم.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold">
                    💾 حفظ غياب الحصة وإرسال التنبيهات
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
