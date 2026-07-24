// دالة محاكاة تسجيل الغياب وإرسال التنبيهات
function toggleAbsence(student, parent) {
    const consoleLog = document.getElementById('logConsole');
    const time = new Date().toLocaleTimeString('ar-EG');
    consoleLog.innerHTML += `<div class="text-yellow-400">[${time}] ⚠️ تم تسجيل غياب التلميذ(ة): ${student}</div>`;
    consoleLog.innerHTML += `<div class="text-blue-300">[${time}] 📱 جاري إرسال رسالة SMS فورية إلى هاتف الولي: ${parent}...</div>`;
    consoleLog.innerHTML += `<div class="text-green-400">[${time}] ✅ تم التسليم بنجاح.</div>`;
    consoleLog.scrollTop = consoleLog.scrollHeight;
}

// دالة محاكاة تشفير كشوف المواد
function encryptSubject(subject) {
    const consoleLog = document.getElementById('logConsole');
    const time = new Date().toLocaleTimeString('ar-EG');
    consoleLog.innerHTML += `<div class="text-white">[${time}] 🔑 جاري جلب مفتاح التشفير الخاص بمادة ${subject}...</div>`;
    consoleLog.innerHTML += `<div class="text-green-400">[${time}] 🔒 تم تشفير كشف مادة (${subject}) بالمفتاح المرفق بنجاح.</div>`;
    consoleLog.scrollTop = consoleLog.scrollHeight;
}
