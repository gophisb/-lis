<?php
// attendance_dashboard.php - النسخة المطورة (PDF + WhatsApp + إحصائيات متقدمة)
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
    <title>لوحة الإحصائيات المتقدمة - التقارير الذكية</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- مكتبة html2pdf لتوليد الـ PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .card-stat { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            transition: transform 0.2s; 
        }
        .card-stat:hover { 
            transform: translateY(-5px); 
        }
        .progress { 
            height: 10px; 
            border-radius: 5px; 
        }
        .alert-zone { 
            background-color: #fff5f5; 
            border-right: 5px solid #dc3545; 
        }
        .border-top-success { border-top: 4px solid #198754 !important; }
        .border-top-danger { border-top: 4px solid #dc3545 !important; }
        .border-top-warning { border-top: 4px solid #ffc107 !important; }
        
        @media print {
            .no-print { display: none !important; }
        }
        
        .whatsapp-btn {
            background-color: #25d366;
            color: white;
            border: none;
            transition: all 0.3s;
        }
        .whatsapp-btn:hover {
            background-color: #1da851;
            color: white;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="container my-5" id="report-content">

    <!-- رأس الصفحة مع أزرار التحكم -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2 class="fw-bold text-dark m-0">
            <i class="fa-solid fa-chart-line text-primary me-2"></i> لوحة الإحصائيات والتقارير الذكية
        </h2>
        <div>
            <button onclick="generatePDF()" class="btn btn-danger px-4 py-2 fw-bold shadow-sm">
                <i class="fa-solid fa-file-pdf me-2"></i> تصدير التقرير العام PDF
            </button>
        </div>
    </div>

    <!-- إشعار النجاح عند الحفظ -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>
            <strong>تم الحفظ بنجاح!</strong> تم تحديث سجلات الغياب وفحص المنظومة تلقائياً.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ======== القسم الأول: كروت الأرقام السريعة ======== -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card card-stat bg-primary text-white p-4">
                <h5 class="card-title opacity-75">
                    <i class="fa-solid fa-users me-2"></i> إجمالي الطلاب المسجلين
                </h5>
                <h2 class="display-5 fw-bold"><?php echo $total_students; ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-stat bg-danger text-white p-4">
                <h5 class="card-title opacity-75">
                    <i class="fa-solid fa-user-slash me-2"></i> إجمالي حالات الغياب المسجلة
                </h5>
                <h2 class="display-5 fw-bold"><?php echo $total_absences; ?></h2>
            </div>
        </div>
    </div>

    <!-- ======== القسم الثاني: تنبيهات الخط الأحمر (تجاوز 15%) ======== -->
    <div class="card card-stat mb-5 border-0 shadow-sm" id="danger-table-zone">
        <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span>
                <i class="fa-solid fa-triangle-exclamation me-2"></i> الطلاب في منطقة الخطر (غياب ≥ 15%)
            </span>
            <button onclick="printDiv('danger-table-zone')" class="btn btn-sm btn-outline-light no-print">
                <i class="fa-solid fa-print me-1"></i> طباعة الجدول فقط
            </button>
        </div>
        <div class="card-body">
            <?php if ($alerts_result && $alerts_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>اسم الطالب</th>
                                <th>رقم هاتف الوالد</th>
                                <th>أيام الغياب</th>
                                <th>نسبة الغياب</th>
                                <th class="no-print">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $counter = 1;
                        while($row = $alerts_result->fetch_assoc()): 
                            $pct = $row['total_days'] > 0 ? ($row['absent_days'] / $row['total_days']) * 100 : 0;
                            // رسالة واتساب جاهزة
                            $whatsapp_msg = "السلام عليكم ورحمة الله،\nنفيدكم بأن ابنكم/ابنتكم (" . $row['name'] . ") قد تجاوز نسبة الغياب المسموحة في المدرسة.\n\n📊 تفاصيل الغياب:\n• عدد أيام الغياب: " . $row['absent_days'] . " يوم\n• نسبة الغياب الإجمالية: " . round($pct, 1) . "%\n\nيرجى مراجعة إدارة المدرسة فوراً لمتابعة الحالة.\n\nمع خالص التحية،\nإدارة المدرسة";
                            $whatsapp_url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $row['parent_phone']) . "?text=" . urlencode($whatsapp_msg);
                        ?>
                            <tr class="alert-zone">
                                <td><?php echo $counter++; ?></td>
                                <td class="fw-bold text-danger">
                                    <i class="fa-solid fa-user-graduate me-1"></i>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </td>
                                <td dir="ltr"><?php echo htmlspecialchars($row['parent_phone']); ?></td>
                                <td><?php echo $row['absent_days'] . ' من ' . $row['total_days']; ?> يوم</td>
                                <td class="fw-bold text-danger">
                                    <?php echo round($pct, 1); ?>%
                                    <div class="progress mt-1" style="width: 80px; height: 6px; display: inline-block;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo min($pct, 100); ?>%"></div>
                                    </div>
                                </td>
                                <td class="no-print">
                                    <!-- زر إرسال واتساب مباشر -->
                                    <a href="<?php echo $whatsapp_url; ?>" target="_blank" 
                                       class="btn whatsapp-btn btn-sm fw-bold shadow-sm" 
                                       title="إرسال رسالة واتساب لولي الأمر">
                                        <i class="fa-brands fa-whatsapp me-1"></i> مراسلة الوالد
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center my-4">
                    <i class="fa-solid fa-circle-check text-success fa-2x mb-2"></i>
                    <p class="text-muted fs-5">ممتاز! لا يوجد أي طالب تجاوز نسبة الغياب الحرجة حالياً.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ======== القسم الثالث: بطاقات المتابعة الفردية ======== -->
    <h4 class="mb-4 fw-bold text-secondary">
        <i class="fa-solid fa-id-card me-2"></i> بطاقات المتابعة الأكاديمية للطلاب
    </h4>
    <div class="row g-4" id="cards-zone">
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
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title fw-bold text-dark mb-3">
                                    <i class="fa-solid fa-user text-<?php echo $color_class; ?> me-1"></i>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </h5>
                                <span class="badge bg-<?php echo $color_class; ?> rounded-pill px-3 py-2">
                                    <?php echo round($pct, 1); ?>%
                                </span>
                            </div>
                            
                            <p class="card-text text-muted mb-2">
                                <i class="fa-regular fa-calendar-check text-success me-1"></i>
                                أيام الحضور الفعلي: <strong><?php echo $total - $absent; ?></strong> يوم
                            </p>
                            <p class="card-text text-muted mb-3">
                                <i class="fa-regular fa-calendar-xmark text-danger me-1"></i>
                                أيام الغياب الإجمالي: <strong><?php echo $absent; ?></strong> يوم
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-secondary">نسبة الغياب العامة</span>
                                <span class="fw-bold text-<?php echo $color_class; ?>"><?php echo round($pct, 1); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?php echo $color_class; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $pct; ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fa-regular fa-face-frown fa-3x mb-3"></i>
                    <p>لا توجد بيانات طلاب لعرضها. قم بتسجيل الغياب أولاً.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======== أزرار التنقل السفلية ======== -->
    <div class="text-center mt-5 no-print">
        <a href="index.php" class="btn btn-outline-dark px-4 py-2 me-2">
            <i class="fa-solid fa-arrow-right me-2"></i> صفحة الحضور والغياب
        </a>
        <button onclick="window.location.reload();" class="btn btn-outline-secondary px-4 py-2">
            <i class="fa-solid fa-rotate me-2"></i> تحديث البيانات
        </button>
    </div>

    <!-- تذييل التقرير (يظهر في PDF) -->
    <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.9rem;">
        <i class="fa-regular fa-clock me-1"></i> تم إنشاء التقرير بتاريخ: 
        <?php echo date('Y-m-d h:i A'); ?> 
        | نظام إدارة الغياب الذكي v2.0
    </div>

</div> <!-- نهاية report-content -->

<!-- ======== جافا سكريبت ======== -->
<script>
    // دالة توليد ملف PDF كامل
    function generatePDF() {
        const element = document.getElementById('report-content');
        const opt = {
            margin: 0.5,
            filename: 'تقرير_غياب_الطلاب_' + new Date().toISOString().slice(0,10) + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true,
                letterRendering: true
            },
            jsPDF: { 
                unit: 'in', 
                format: 'a4', 
                orientation: 'portrait' 
            }
        };
        // إظهار رسالة انتظار
        const btn = document.querySelector('.btn-danger');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> جاري التوليد...';
        btn.disabled = true;
        
        html2pdf().set(opt).from(element).save().then(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // دالة طباعة جزء معين من الصفحة
    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }

    // إغلاق التنبيهات تلقائياً بعد 5 ثواني
    document.addEventListener('DOMContentLoaded', function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                var closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }, 5000);
        });
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>