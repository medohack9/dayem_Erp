import { Plus, Edit, Trash2 } from "lucide-react";

const departments = [
  {
    id: 1,
    name: "التسويق",
    manager: "أحمد محمد علي",
    defaultSalary: "15,000 جنيه",
    employeeCount: 12,
  },
  {
    id: 2,
    name: "الموارد البشرية",
    manager: "فاطمة حسن",
    defaultSalary: "18,000 جنيه",
    employeeCount: 8,
  },
  {
    id: 3,
    name: "التكنولوجيا",
    manager: "محمود سعيد",
    defaultSalary: "22,000 جنيه",
    employeeCount: 25,
  },
  {
    id: 4,
    name: "المبيعات",
    manager: "نورهان أحمد",
    defaultSalary: "16,000 جنيه",
    employeeCount: 35,
  },
  {
    id: 5,
    name: "المالية",
    manager: "عمر خالد",
    defaultSalary: "20,000 جنيه",
    employeeCount: 10,
  },
  {
    id: 6,
    name: "خدمة العملاء",
    manager: "ياسمين محمد",
    defaultSalary: "14,000 جنيه",
    employeeCount: 45,
  },
];

export default function DepartmentsPage() {
  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] mb-2">الأقسام</h1>
          <p className="text-[#666666]">إدارة أقسام الشركة والمديرين</p>
        </div>
        <button className="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
          <Plus className="w-5 h-5" />
          إضافة قسم جديد
        </button>
      </div>

      {/* Departments Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {departments.map((dept) => (
          <div
            key={dept.id}
            className="bg-white rounded-xl p-6 border border-[#e5e5e5] hover:shadow-lg transition-shadow"
          >
            <div className="flex items-start justify-between mb-4">
              <div>
                <h3 className="text-xl font-bold text-[#111111] mb-1">
                  {dept.name}
                </h3>
                <p className="text-[#666666] text-sm">
                  {dept.employeeCount} موظف
                </p>
              </div>
              <div className="flex items-center gap-1">
                <button className="p-2 hover:bg-[#fafafa] rounded-lg transition-colors">
                  <Edit className="w-4 h-4 text-[#666666]" />
                </button>
                <button className="p-2 hover:bg-[#fafafa] rounded-lg transition-colors">
                  <Trash2 className="w-4 h-4 text-red-500" />
                </button>
              </div>
            </div>

            <div className="space-y-3 pt-4 border-t border-[#e5e5e5]">
              <div>
                <p className="text-xs text-[#666666] mb-1">مدير القسم</p>
                <div className="flex items-center gap-2">
                  <div className="w-8 h-8 bg-[#F4C400] rounded-full flex items-center justify-center">
                    <span className="text-[#111111] text-xs font-bold">
                      {dept.manager.charAt(0)}
                    </span>
                  </div>
                  <p className="text-[#111111] font-medium">{dept.manager}</p>
                </div>
              </div>

              <div>
                <p className="text-xs text-[#666666] mb-1">المرتب الافتراضي</p>
                <p className="text-[#111111] font-bold">{dept.defaultSalary}</p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
