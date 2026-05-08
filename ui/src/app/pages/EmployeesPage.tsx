import { useNavigate } from "react-router";
import { Plus, Search, Edit, Trash2 } from "lucide-react";

const employees = [
  {
    id: 1,
    name: "أحمد محمد علي",
    code: "EMP-001",
    department: "التسويق",
    phone: "01012345678",
    salary: "15,000 جنيه",
    status: "نشط",
  },
  {
    id: 2,
    name: "فاطمة حسن",
    code: "EMP-002",
    department: "الموارد البشرية",
    phone: "01023456789",
    salary: "18,000 جنيه",
    status: "نشط",
  },
  {
    id: 3,
    name: "محمود سعيد",
    code: "EMP-003",
    department: "التكنولوجيا",
    phone: "01034567890",
    salary: "22,000 جنيه",
    status: "نشط",
  },
  {
    id: 4,
    name: "نورهان أحمد",
    code: "EMP-004",
    department: "المبيعات",
    phone: "01045678901",
    salary: "16,000 جنيه",
    status: "نشط",
  },
  {
    id: 5,
    name: "عمر خالد",
    code: "EMP-005",
    department: "المالية",
    phone: "01056789012",
    salary: "20,000 جنيه",
    status: "موقوف",
  },
  {
    id: 6,
    name: "ياسمين محمد",
    code: "EMP-006",
    department: "خدمة العملاء",
    phone: "01067890123",
    salary: "14,000 جنيه",
    status: "نشط",
  },
  {
    id: 7,
    name: "كريم عادل",
    code: "EMP-007",
    department: "التسويق",
    phone: "01078901234",
    salary: "17,000 جنيه",
    status: "منتهي الخدمة",
  },
  {
    id: 8,
    name: "منى سامي",
    code: "EMP-008",
    department: "الموارد البشرية",
    phone: "01089012345",
    salary: "19,000 جنيه",
    status: "نشط",
  },
];

export default function EmployeesPage() {
  const navigate = useNavigate();

  const getStatusColor = (status: string) => {
    switch (status) {
      case "نشط":
        return "bg-green-100 text-green-800";
      case "موقوف":
        return "bg-yellow-100 text-yellow-800";
      case "منتهي الخدمة":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] mb-2">الموظفين</h1>
          <p className="text-[#666666]">إدارة وعرض كل موظفين الشركة</p>
        </div>
        <button
          onClick={() => navigate("/dashboard/employees/add")}
          className="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors"
        >
          <Plus className="w-5 h-5" />
          إضافة موظف جديد
        </button>
      </div>

      {/* Search Bar */}
      <div className="bg-white rounded-xl p-4 mb-6 border border-[#e5e5e5]">
        <div className="relative">
          <Search className="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#666666]" />
          <input
            type="text"
            placeholder="ابحث عن موظف بالاسم أو الكود..."
            className="w-full pr-12 pl-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
          />
        </div>
      </div>

      {/* Employees Table */}
      <div className="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-[#fafafa] border-b border-[#e5e5e5]">
              <tr>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  اسم الموظف
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  كود الموظف
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  القسم
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  رقم الموبايل
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  المرتب
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  الحالة
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#e5e5e5]">
              {employees.map((employee) => (
                <tr
                  key={employee.id}
                  className="hover:bg-[#fafafa] transition-colors"
                >
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-[#F4C400] rounded-full flex items-center justify-center">
                        <span className="text-[#111111] font-bold">
                          {employee.name.charAt(0)}
                        </span>
                      </div>
                      <span className="font-medium text-[#111111]">
                        {employee.name}
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-[#666666]">{employee.code}</td>
                  <td className="px-6 py-4 text-[#666666]">
                    {employee.department}
                  </td>
                  <td className="px-6 py-4 text-[#666666]">{employee.phone}</td>
                  <td className="px-6 py-4 text-[#111111] font-medium">
                    {employee.salary}
                  </td>
                  <td className="px-6 py-4">
                    <span
                      className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(
                        employee.status
                      )}`}
                    >
                      {employee.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2">
                      <button className="p-2 hover:bg-[#fafafa] rounded-lg transition-colors">
                        <Edit className="w-4 h-4 text-[#666666]" />
                      </button>
                      <button className="p-2 hover:bg-[#fafafa] rounded-lg transition-colors">
                        <Trash2 className="w-4 h-4 text-red-500" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
