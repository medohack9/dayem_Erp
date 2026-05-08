import { DollarSign, CheckCircle, XCircle } from "lucide-react";

const salaries = [
  {
    id: 1,
    name: "أحمد محمد علي",
    monthlySalary: "15,000 جنيه",
    monthsPaid: 3,
    lastPayment: "01/03/2026",
    status: "مدفوع",
  },
  {
    id: 2,
    name: "فاطمة حسن",
    monthlySalary: "18,000 جنيه",
    monthsPaid: 3,
    lastPayment: "01/03/2026",
    status: "مدفوع",
  },
  {
    id: 3,
    name: "محمود سعيد",
    monthlySalary: "22,000 جنيه",
    monthsPaid: 2,
    lastPayment: "01/02/2026",
    status: "معلق",
  },
  {
    id: 4,
    name: "نورهان أحمد",
    monthlySalary: "16,000 جنيه",
    monthsPaid: 3,
    lastPayment: "01/03/2026",
    status: "مدفوع",
  },
  {
    id: 5,
    name: "عمر خالد",
    monthlySalary: "20,000 جنيه",
    monthsPaid: 3,
    lastPayment: "01/03/2026",
    status: "مدفوع",
  },
  {
    id: 6,
    name: "ياسمين محمد",
    monthlySalary: "14,000 جنيه",
    monthsPaid: 2,
    lastPayment: "01/02/2026",
    status: "معلق",
  },
  {
    id: 7,
    name: "كريم عادل",
    monthlySalary: "17,000 جنيه",
    monthsPaid: 3,
    lastPayment: "01/03/2026",
    status: "مدفوع",
  },
  {
    id: 8,
    name: "منى سامي",
    monthlySalary: "19,000 جنيه",
    monthsPaid: 3,
    lastPayment: "01/03/2026",
    status: "مدفوع",
  },
];

export default function SalariesPage() {
  const totalMonthly = salaries.reduce((sum, emp) => {
    const salary = parseInt(emp.monthlySalary.replace(/[^0-9]/g, ""));
    return sum + salary;
  }, 0);

  const pendingCount = salaries.filter((s) => s.status === "معلق").length;

  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-[#111111] mb-2">المرتبات</h1>
        <p className="text-[#666666]">إدارة ومتابعة مرتبات الموظفين</p>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
              <DollarSign className="w-6 h-6 text-white" />
            </div>
            <div>
              <p className="text-[#666666] text-sm">إجمالي المرتبات الشهرية</p>
              <p className="text-2xl font-bold text-[#111111]">
                {totalMonthly.toLocaleString()} جنيه
              </p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-12 h-12 bg-[#F4C400] rounded-lg flex items-center justify-center">
              <CheckCircle className="w-6 h-6 text-[#111111]" />
            </div>
            <div>
              <p className="text-[#666666] text-sm">المرتبات المدفوعة</p>
              <p className="text-2xl font-bold text-[#111111]">
                {salaries.length - pendingCount}
              </p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
              <XCircle className="w-6 h-6 text-white" />
            </div>
            <div>
              <p className="text-[#666666] text-sm">المرتبات المعلقة</p>
              <p className="text-2xl font-bold text-[#111111]">{pendingCount}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Salaries Table */}
      <div className="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-[#fafafa] border-b border-[#e5e5e5]">
              <tr>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  اسم الموظف
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  المرتب الشهري
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  عدد الشهور المدفوعة
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  تاريخ آخر دفع
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  حالة الدفع
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#e5e5e5]">
              {salaries.map((salary) => (
                <tr
                  key={salary.id}
                  className="hover:bg-[#fafafa] transition-colors"
                >
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-[#F4C400] rounded-full flex items-center justify-center">
                        <span className="text-[#111111] font-bold">
                          {salary.name.charAt(0)}
                        </span>
                      </div>
                      <span className="font-medium text-[#111111]">
                        {salary.name}
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-[#111111] font-bold">
                    {salary.monthlySalary}
                  </td>
                  <td className="px-6 py-4 text-[#666666]">
                    {salary.monthsPaid} شهور
                  </td>
                  <td className="px-6 py-4 text-[#666666]">
                    {salary.lastPayment}
                  </td>
                  <td className="px-6 py-4">
                    <span
                      className={`px-3 py-1 rounded-full text-xs font-medium ${
                        salary.status === "مدفوع"
                          ? "bg-green-100 text-green-800"
                          : "bg-orange-100 text-orange-800"
                      }`}
                    >
                      {salary.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    {salary.status === "معلق" && (
                      <button className="px-4 py-2 bg-[#F4C400] text-[#111111] rounded-lg font-medium hover:bg-[#e5b600] transition-colors">
                        تأكيد صرف المرتب
                      </button>
                    )}
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
