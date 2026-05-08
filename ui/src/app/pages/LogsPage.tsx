import { Download, Activity, User, Settings, FileText } from "lucide-react";

const logs = [
  {
    id: 1,
    action: "إضافة موظف جديد",
    user: "فاطمة حسن",
    details: "تم إضافة الموظف: محمد أحمد إلى قسم التسويق",
    timestamp: "10/04/2026 - 02:30 م",
    type: "employee",
  },
  {
    id: 2,
    action: "تعديل المرتب",
    user: "عمر خالد",
    details: "تم تعديل مرتب الموظف أحمد محمد من 14,000 إلى 15,000 جنيه",
    timestamp: "10/04/2026 - 01:15 م",
    type: "salary",
  },
  {
    id: 3,
    action: "إغلاق تذكرة",
    user: "أحمد محمد",
    details: "تم إغلاق تذكرة الدعم #1234: مشكلة في تسجيل الدخول",
    timestamp: "10/04/2026 - 11:45 ص",
    type: "ticket",
  },
  {
    id: 4,
    action: "رفع ملف",
    user: "نورهان أحمد",
    details: 'تم رفع الملف: "تقرير المبيعات - مارس 2026.xlsx"',
    timestamp: "10/04/2026 - 10:20 ص",
    type: "file",
  },
  {
    id: 5,
    action: "تعديل الصلاحيات",
    user: "أحمد محمد",
    details: "تم تعديل صلاحيات المستخدم: محمود سعيد - إضافة صلاحية الإدارة",
    timestamp: "09/04/2026 - 05:30 م",
    type: "settings",
  },
  {
    id: 6,
    action: "إنشاء مهمة جديدة",
    user: "فاطمة حسن",
    details: "تم إنشاء مهمة: تحديث موقع الشركة الإلكتروني",
    timestamp: "09/04/2026 - 03:15 م",
    type: "task",
  },
  {
    id: 7,
    action: "حذف موظف",
    user: "عمر خالد",
    details: "تم حذف الموظف: كريم عادل من قسم التسويق (منتهي الخدمة)",
    timestamp: "09/04/2026 - 01:00 م",
    type: "employee",
  },
  {
    id: 8,
    action: "تسجيل دخول",
    user: "أحمد محمد",
    details: "تسجيل دخول ناجح من IP: 197.43.123.45",
    timestamp: "09/04/2026 - 09:00 ص",
    type: "auth",
  },
];

export default function LogsPage() {
  const getActionIcon = (type: string) => {
    switch (type) {
      case "employee":
        return User;
      case "settings":
        return Settings;
      case "file":
        return FileText;
      default:
        return Activity;
    }
  };

  const getActionColor = (type: string) => {
    switch (type) {
      case "employee":
        return "bg-blue-100 text-blue-800";
      case "salary":
        return "bg-green-100 text-green-800";
      case "ticket":
        return "bg-orange-100 text-orange-800";
      case "file":
        return "bg-purple-100 text-purple-800";
      case "settings":
        return "bg-red-100 text-red-800";
      case "task":
        return "bg-yellow-100 text-yellow-800";
      case "auth":
        return "bg-gray-100 text-gray-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] mb-2">السجلات</h1>
          <p className="text-[#666666]">سجل نشاط النظام الكامل</p>
        </div>
        <button className="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
          <Download className="w-5 h-5" />
          تحميل التقرير CSV
        </button>
      </div>

      {/* Filter Options */}
      <div className="bg-white rounded-xl p-4 mb-6 border border-[#e5e5e5]">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select className="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]">
            <option>كل الأنشطة</option>
            <option>الموظفين</option>
            <option>المرتبات</option>
            <option>التذاكر</option>
            <option>الملفات</option>
            <option>الإعدادات</option>
          </select>
          <select className="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]">
            <option>كل المستخدمين</option>
            <option>أحمد محمد</option>
            <option>فاطمة حسن</option>
            <option>عمر خالد</option>
          </select>
          <input
            type="date"
            className="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
          />
          <input
            type="date"
            className="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
          />
        </div>
      </div>

      {/* Activity Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-xl p-4 border border-[#e5e5e5]">
          <p className="text-[#666666] text-sm mb-1">إجمالي النشاطات</p>
          <p className="text-2xl font-bold text-[#111111]">{logs.length}</p>
        </div>
        <div className="bg-white rounded-xl p-4 border border-[#e5e5e5]">
          <p className="text-[#666666] text-sm mb-1">اليوم</p>
          <p className="text-2xl font-bold text-[#111111]">5</p>
        </div>
        <div className="bg-white rounded-xl p-4 border border-[#e5e5e5]">
          <p className="text-[#666666] text-sm mb-1">هذا الأسبوع</p>
          <p className="text-2xl font-bold text-[#111111]">23</p>
        </div>
        <div className="bg-white rounded-xl p-4 border border-[#e5e5e5]">
          <p className="text-[#666666] text-sm mb-1">هذا الشهر</p>
          <p className="text-2xl font-bold text-[#111111]">87</p>
        </div>
      </div>

      {/* Logs Timeline */}
      <div className="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
        <div className="p-6 border-b border-[#e5e5e5]">
          <h3 className="font-bold text-[#111111]">سجل النشاطات</h3>
        </div>
        <div className="divide-y divide-[#e5e5e5]">
          {logs.map((log) => {
            const ActionIcon = getActionIcon(log.type);
            return (
              <div
                key={log.id}
                className="p-6 hover:bg-[#fafafa] transition-colors"
              >
                <div className="flex items-start gap-4">
                  <div
                    className={`w-10 h-10 rounded-lg flex items-center justify-center ${getActionColor(
                      log.type
                    )}`}
                  >
                    <ActionIcon className="w-5 h-5" />
                  </div>
                  <div className="flex-1">
                    <div className="flex items-start justify-between mb-2">
                      <div>
                        <h4 className="font-bold text-[#111111] mb-1">
                          {log.action}
                        </h4>
                        <p className="text-[#666666] text-sm">{log.details}</p>
                      </div>
                      <span className="text-sm text-[#666666] whitespace-nowrap mr-4">
                        {log.timestamp}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      <User className="w-4 h-4 text-[#666666]" />
                      <span className="text-[#666666]">{log.user}</span>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
