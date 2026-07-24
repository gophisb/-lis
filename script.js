/**
 * منصة المدرسة الرقمية الذكية - محرك التشغيل الديناميكي (script.js)
 * تم البناء خطوة بخطوة لدعم كافة وظائف الميثاق المعتمد.
 */

// 1. قاعدة البيانات المحلية الافتراضية للمحاكاة (Mock Database)
const schoolData = {
    students: [
        { id: "STU001", name: "محمد أمين علوي", class: "السنة الثالثة - أفواج 1", attendanceCount: 2, average: 14.50 },
        { id: "STU002", name: "ليندا بوحفص", class: "السنة الثالثة - أفواج 1", attendanceCount: 0, average: 16.75 },
        { id: "STU003", name: "أنيس بن مريم", class: "السنة الثالثة - أفواج 1", attendanceCount: 5, average: 09.20 }
    ],
    attendanceLogs: [
        { id: 1, studentId: "STU001", date: "2026-03-10", type: "غياب", status: "مقبول", excuse: "شهادة طبية مبررة" },
        { id: 2, studentId: "STU003", date: "2026-03-11", type: "تأخر", status: "معلق", excuse: "لم يرسل بعد" }
    ],
    grades: [
        { studentId: "STU001", math: 14, physics: 15, arabic: 16 },
        { studentId: "STU002", math: 18, physics: 17, arabic: 15 },
        { studentId: "STU003", math: 08, physics: 09, arabic: 11 }
    ]
};

// 2. تتبع الحالة الحالية للتطبيق (Application State)
let currentTab = 'attendance';

// 3. مستمع الأحداث عند تحميل الصفحة بالكامل
document.addEventListener("DOMContentLoaded", () => {
    switchTab(currentTab);
    const roleSelector = document.getElementById('user-role');
    if(roleSelector) {
        roleSelector.addEventListener('change', (e) => {
            alert(`تم تحويل النظام إلى محاكاة صلاحية: ${e.target.value === 'admin' ? 'الإدارة' : e.target.value === 'teacher' ? 'الأستاذ' : 'ولي الأمر'}`);
            switchTab(currentTab);
        });
    }
});

// 4. دالة التنقل الديناميكي بين الأقسام (Tab Switcher)
function switchTab(tabId) {
    currentTab = tabId;
    const contentDisplay = document.getElementById('main-content-display');

    const allButtons = document.querySelectorAll('button[id^="btn-"]');
    allButtons.forEach(btn => {
        if (btn.id === `btn-${tabId}`) {
            btn.className = "w-full text-right px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between transition bg-indigo-600 text-white shadow-sm";
        } else if (btn.id === "btn-ai-future") {
            btn.className = "w-full text-right px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between transition text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200";
        } else {
            btn.className = "w-full text-right px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between transition text-gray-600 hover:bg-gray-100";
        }
    });

    contentDisplay.innerHTML = generateTabContent(tabId);
}

// 5. مولد واجهات الاستخدام للأقسام (UI Engine)
function generateTabContent(tabId) {
    switch(tabId) {
        case 'attendance':
            return `
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-user-check text-indigo-600 ml-2"></i>دفتر الغياب والحضور الإلكتروني</h3>
                            <p class="text-xs text-gray-500 mt-0.5">تسجيل فوري وإرسال إشعارات تلقائية للأولياء وتدقيق التبريرات المرفوعة.</p>
                        </div>
                        <button onclick="simulateAction('تسجيل حضور يومي جديد')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-4 py-2 rounded-xl transition shadow-sm font-medium">+ تسجيل غياب جديد</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 border-b">
                                    <th class="p-3">اسم التلميذ</th>
                                    <th class="p-3">تاريخ الغياب</th>
                                    <th class="p-3">النوع</th>
                                    <th class="p-3">حالة التبرير الإلكتروني</th>
                                    <th class="p-3 text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                ${schoolData.attendanceLogs.map(log => {
                                    const student = schoolData.students.find(s => s.id === log.studentId);
                                    return `
                                        <tr>
                                            <td class="p-3 font-semibold text-gray-900">${student ? student.name : 'تلميذ غير معروف'}</td>
                                            <td class="p-3 font-mono text-gray-500">${log.date}</td>
                                            <td class="p-3"><span class="bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-bold">${log.type}</span></td>
                                            <td class="p-3">
                                                <span class="${log.status === 'مقبول' ? 'text-green-600 bg-green-50' : 'text-amber-600 bg-amber-50'} px-2 py-0.5 rounded-md font-medium">
                                                    ${log.status} (${log.excuse})
                                                </span>
                                            </td>
                                            <td class="p-3 text-center">
                                                <button onclick="simulateAction('مراجعة تبرير الطالب ${student ? student.name : ""}')" class="text-indigo-600 hover:text-indigo-900 font-bold ml-3">معاينة التبرير</button>
                                                <button onclick="simulateAction('إرسال إشعار فوري لولي الأمر')" class="text-emerald-600 hover:text-emerald-900 font-bold"><i class="fa-solid fa-paper-plane ml-1"></i>إشعار فوري</button>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

        case 'grades':
            return `
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-shield-halved text-indigo-600 ml-2"></i>كشوف النقاط والنتائج المؤمنة</h3>
                            <p class="text-xs text-gray-500 mt-0.5">حساب المعدلات الآلي، التشفير وحماية الوثائق ودمج رمز التحقق لمنع التزوير.</p>
                        </div>
                        <span class="bg-cyan-50 text-cyan-700 text-xs px-3 py-1.5 rounded-xl border border-cyan-200"><i class="fa-solid fa-qrcode ml-1.5"></i>تشفير QR مفعل</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${schoolData.students.map(student => {
                            const studentGrade = schoolData.grades.find(g => g.studentId === student.id) || {};
                            return `
                                <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50 hover:shadow-sm transition">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">${student.name}</h4>
                                            <p class="text-[11px] text-gray-400 font-mono">${student.id} | ${student.class}</p>
                                        </div>
                                        <div class="text-left">
                                            <span class="text-[10px] text-gray-400 block font-bold">المعدل الفصلي</span>
                                            <span class="text-sm font-bold font-mono ${student.average >= 10 ? 'text-green-600' : 'text-red-500'}">${student.average.toFixed(2)}</span>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 border border-gray-100 flex justify-between text-center text-xs mb-3">
                                        <div><span class="text-gray-400 block text-[10px]">رياضيات</span><span class="font-bold font-mono">${studentGrade.math || 0}</span></div>
                                        <div><span class="text-gray-400 block text-[10px]">فيزياء</span><span class="font-bold font-mono">${studentGrade.physics || 0}</span></div>
                                        <div><span class="text-gray-400 block text-[10px]">لغة عربية</span><span class="font-bold font-mono">${studentGrade.arabic || 0}</span></div>
                                    </div>
                                    <div class="flex justify-between items-center pt-1">
                                        <button onclick="simulateAction('تعديل نقاط ومعدل الطالب: ${student.name}')" class="text-indigo-600 hover:underline text-xs font-semibold"><i class="fa-regular fa-pen-to-square ml-1"></i>رصد النقاط</button>
                                        <button onclick="simulateAction('إصدار كشف مشفر مع رمز QR للطالب ${student.name}')" class="text-emerald-600 hover:underline text-xs font-semibold"><i class="fa-solid fa-lock ml-1"></i>إصدار كشف مشفر</button>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;

        case 'communication':
            return `
                <div class="space-y-6">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-bullhorn text-indigo-600 ml-2"></i>الإشعارات والتواصل المباشر والمنظم</h3>
                        <p class="text-xs text-gray-500 mt-0.5">مركز إرسال الإعلانات والتعميمات وبلاغات الامتحانات واجتماعات الإدارة والأسرة.</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl space-y-4 border border-gray-100">
                        <textarea id="notice-text" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="اكتب نص الإعلان أو التنبيه هنا..."></textarea>
                        <div class="flex flex-wrap items-center gap-4">
                            <select id="notice-target" class="border border-gray-300 rounded-lg text-xs px-3 py-2 bg-white">
                                <option value="all">كل أولياء المؤسسة</option>
                                <option value="absent">أولياء تلاميذ غائبين اليوم</option>
                                <option value="class">فوج محدد فقط</option>
                            </select>
                            <button onclick="simulateNotification()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-5 py-2 rounded-xl transition shadow-sm font-medium"><i class="fa-solid fa-broadcast ml-1"></i>بث الإشعار فوراً لـ هواتف الأولياء</button>
                        </div>
                    </div>
                </div>
            `;

        case 'requests':
            return `
                <div class="space-y-6">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-file-signature text-indigo-600 ml-2"></i>الطلبات الإدارية الإلكترونية المباشرة</h3>
                        <p class="text-xs text-gray-500 mt-0.5">تلقي ومعالجة طلبات الوثائق المدرسية ومواعيد المقابلات لتقليل الورق والازدحام.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border p-4 rounded-xl bg-white space-y-2">
                            <div class="flex justify-between items-center"><span class="bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded font-bold">طلب شهادة مدرسية</span><span class="text-gray-400 font-mono text-[10px]">#REQ-882</span></div>
                            <p class="text-xs font-bold text-gray-800">التلميذ: محمد أمين علوي</p>
                            <p class="text-[11px] text-gray-500">المرسل: الولي (الأب) • منذ 4 ساعات</p>
                            <div class="flex space-x-2 space-x-reverse pt-2"><button onclick="simulateAction('قبول وطباعة الشهادة المدرسية')" class="bg-green-600 text-white text-[11px] px-3 py-1 rounded-md font-semibold">تأكيد وإصدار</button><button onclick="simulateAction('رفض طلب الشهادة المدرسية')" class="text-red-500 text-[11px] px-2 py-1">رفض</button></div>
                        </div>
                        <div class="border p-4 rounded-xl bg-white space-y-2">
                            <div class="flex justify-between items-center"><span class="bg-purple-50 text-purple-700 text-[10px] px-2 py-0.5 rounded font-bold">طلب موعد مقابلة</span><span class="text-gray-400 font-mono text-[10px]">#REQ-879</span></div>
                            <p class="text-xs font-bold text-gray-800">الجهة: مستشار التربية والتوجيه</p>
                            <p class="text-[11px] text-gray-500">المرسل: ولي أمر التلميذ أنيس بن مريم • منذ يوم</p>
                            <div class="flex space-x-2 space-x-reverse pt-2"><button onclick="simulateAction('جدولة موعد لقاء الولي')" class="bg-indigo-600 text-white text-[11px] px-3 py-1 rounded-md font-semibold">تحديد موعد</button></div>
                        </div>
                    </div>
                </div>
            `;

        case 'profile':
            return `
                <div class="space-y-6">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-id-card text-indigo-600 ml-2"></i>ملف التلميذ الرقمي الموحد 360°</h3>
                        <p class="text-xs text-gray-500 mt-0.5">يحتوي السجل الأكاديمي، الحضور، الملاحظات السلوكية، وتاريخ المراسلات الإدارية كاملاً.</p>
                    </div>
                    <div class="bg-indigo-900 text-white p-4 rounded-xl flex items-center space-x-4 space-x-reverse">
                        <div class="bg-white/20 p-3 rounded-full"><i class="fa-solid fa-graduation-cap text-2xl text-yellow-400"></i></div>
                        <div>
                            <p class="text-xs text-indigo-200">البحث السريع في السجلات الطبية والتربوية للتلاميذ:</p>
                            <select onchange="simulateAction('استعراض الملف الشامل للتلميذ')" class="bg-indigo-800 border border-indigo-700 rounded-lg text-xs p-2 mt-1 focus:outline-none text-white w-56">
                                ${schoolData.students.map(s => `<option>${s.name} (${s.class})</option>`).join('')}
                            </select>
                        </div>
                    </div>
                </div>
            `;

        case 'appointments':
            return `
                <div class="space-y-6">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-calendar-days text-indigo-600 ml-2"></i>حجز وتنظيم المواعيد والاجتماعات</h3>
                        <p class="text-xs text-gray-500 mt-0.5">جدولة وتنظيم اللقاءات الدورية مع الإدارة، الأساتذة، ومستشار التوجيه المدرسي تلقائياً.</p>
                    </div>
                    <div class="bg-gray-50 border p-4 rounded-xl text-center text-xs text-gray-500 py-8">
                        <i class="fa-solid fa-calendar-check text-2xl text-gray-400 mb-2"></i>
                        <p class="font-semibold text-gray-700">لا توجد اجتماعات مجدولة لهذا اليوم.</p>
                        <button onclick="simulateAction('إنشاء جدول اجتماعات أولياء جديد')" class="mt-3 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-100 font-medium">فتح نافذة جدولة لقاء عام مع الأولياء</button>
                    </div>
                </div>
            `;

        case 'ai-future':
            return `
                <div class="space-y-6">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-bold text-purple-900"><i class="fa-solid fa-brain-circuit text-purple-600 ml-2"></i>مستشار الذكاء الاصطناعي الأكاديمي (الرؤية المستقبلية)</h3>
                        <p class="text-xs text-purple-600 mt-0.5">تحليل أوتوماتيكي ذكي لتراجع المستوى الدراسي، التنبؤ بالغيابات، ومساعدة المعلم في صياغة التمارين.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-100 rounded-2xl p-4 space-y-3">
                        <h4 class="text-xs font-bold text-purple-800 flex items-center"><i class="fa-solid fa-wand-magic-sparkles ml-1.5"></i>رصد ذكي للمؤشرات التلقائية المبكرة:</h4>
                        <div class="bg-white p-3 rounded-xl border border-purple-100 text-xs leading-relaxed text-gray-600">
                            🚨 <strong class="text-purple-900">تنبيه ذكي:</strong> نلاحظ انخفاضًا بنسبة <span class="font-bold text-red-500 font-mono">15%</span> في نتائج الطالب <span class="font-bold text-gray-900">أنيس بن مريم</span> في مادة الرياضيات مقارنة بالفصل السابق، متزامنًا مع تكرار تأخره الصباحي (رادار المتابعة الذكية). نوصي بجدولة موعد تلقائي مع ولي أمره ومستشار التوجيه.
                        </div>
                        <div class="flex space-x-2 space-x-reverse pt-1">
                            <button onclick="simulateAction('توليد خطة دعم دراسي بالذكاء الاصطناعي')" class="bg-purple-600 text-white text-[11px] px-3 py-1.5 rounded-lg font-semibold hover:bg-purple-700">توليد خطة دعم دراسي مقترحة</button>
                        </div>
                    </div>
                </div>
            `;

        default:
            return `<p class="text-center text-xs py-8 text-gray-400">القسم غير موجود.</p>`;
    }
}

// 6. دوال المحاكاة التجريبية للتفاعلات (Simulation Handlers)
function simulateAction(actionName) {
    alert(`⚡ [محاكاة تشغيلية]: تم تشغيل وظيفة (${actionName}) بنجاح.\n\nسيتم ربطها في الخطوة القادمة بـقاعدة البيانات الفعلية أو الخادم الخاص بك.`);
}

function simulateNotification() {
    const noticeText = document.getElementById('notice-text').value;
    const target = document.getElementById('notice-target').value;

    if(!noticeText.trim()) {
        alert("⚠️ يرجى كتابة نص الإشعار أولاً قبل الإرسال!");
        return;
    }

    alert(`🚀 [تم الإرسال الفوري بنجاح]:\n\nالهدف: ${target}\nمحتوى الرسالة المشفرة: "${noticeText}"\n\nتم إرسال إشعار فوري وتنبيه Push Notification لهواتف الأولياء الآن كفعل فوري.`);
    document.getElementById('notice-text').value = '';
}