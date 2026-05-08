import {
  BarChart,
  Bar,
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from "recharts";
import { Users, CheckSquare, Ticket, DollarSign } from "lucide-react";

const taskData = [
  { month: "يناير", completed: 45, pending: 12 },
  { month: "فبراير", completed: 52, pending: 8 },
  { month: "مارس", completed: 61, pending: 15 },
  { month: "أبريل", completed: 58, pending: 10 },
  { month: "مايو", completed: 70, pending: 6 },
  { month: "يونيو", completed: 65, pending: 11 },
];

const ticketData = [
  { day: "السبت", open: 12, closed: 8 },
  { day: "الأحد", open: 15, closed: 10 },
  { day: "الاثنين", open: 8, closed: 14 },
  { day: "الثلاثاء", open: 10, closed: 12 },
  { day: "الأربعاء", open: 14, closed: 9 },
  { day: "الخميس", open: 11, closed: 13 },
  { day: "الجمعة", open: 6, closed: 15 },
];

const stats = [
  {
    icon: Users,
    label: "إجمالي الموظفين",
    value: "247",
    change: "+12 هذا الشهر",
    color: "bg-blue-500",
  },
  {
    icon: CheckSquare,
    label: "المهام المفتوحة",
    value: "34",
    change: "من أصل 98 مهمة",
    color: "bg-[#F4C400]",
  },
  {
    icon: Ticket,
    label: "التذاكر المفتوحة",
    value: "18",
    change: "تحتاج مراجعة",
    color: "bg-orange-500",
  },
  {
    icon: DollarSign,
    label: "إجمالي المرتبات الشهرية",
    value: "2,450,000 جنيه",
    change: "لشهر أبريل 2026",
    color: "bg-green-500",
  },
];

export default function MainDashboard() {
  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-[#111111] mb-2">
          أهلاً بيك في لوحة تحكم دايم
        </h1>
        <p className="text-[#666666]">إدارة شاملة لكل عمليات الشركة</p>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {stats.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <div
              key={index}
              className="bg-white rounded-xl p-6 border border-[#e5e5e5] hover:shadow-lg transition-shadow"
            >
              <div className="flex items-start justify-between mb-4">
                <div
                  className={`w-12 h-12 ${stat.color} rounded-lg flex items-center justify-center`}
                >
                  <Icon className="w-6 h-6 text-white" />
                </div>
              </div>
              <p className="text-[#666666] text-sm mb-2">{stat.label}</p>
              <p className="text-2xl font-bold text-[#111111] mb-1">
                {stat.value}
              </p>
              <p className="text-[#666666] text-xs">{stat.change}</p>
            </div>
          );
        })}
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Tasks Chart */}
        <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
          <h3 className="text-lg font-bold text-[#111111] mb-6">
            رسم بياني لإنجاز المهام
          </h3>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={taskData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e5e5" />
              <XAxis dataKey="month" stroke="#666666" />
              <YAxis stroke="#666666" />
              <Tooltip
                contentStyle={{
                  backgroundColor: "#fff",
                  border: "1px solid #e5e5e5",
                  borderRadius: "8px",
                }}
              />
              <Bar dataKey="completed" fill="#F4C400" name="مكتملة" />
              <Bar dataKey="pending" fill="#111111" name="معلقة" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Tickets Chart */}
        <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
          <h3 className="text-lg font-bold text-[#111111] mb-6">
            رسم بياني لحركة التذاكر
          </h3>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={ticketData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e5e5" />
              <XAxis dataKey="day" stroke="#666666" />
              <YAxis stroke="#666666" />
              <Tooltip
                contentStyle={{
                  backgroundColor: "#fff",
                  border: "1px solid #e5e5e5",
                  borderRadius: "8px",
                }}
              />
              <Line
                type="monotone"
                dataKey="open"
                stroke="#f97316"
                strokeWidth={3}
                name="مفتوحة"
              />
              <Line
                type="monotone"
                dataKey="closed"
                stroke="#22c55e"
                strokeWidth={3}
                name="مغلقة"
              />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="mt-6 bg-white rounded-xl p-6 border border-[#e5e5e5]">
        <h3 className="text-lg font-bold text-[#111111] mb-4">
          آخر التحديثات
        </h3>
        <div className="space-y-4">
          <div className="flex items-start gap-4 pb-4 border-b border-[#e5e5e5]">
            <div className="w-2 h-2 bg-[#F4C400] rounded-full mt-2"></div>
            <div className="flex-1">
              <p className="text-[#111111] font-medium">
                تم إضافة موظف جديد: محمد أحمد
              </p>
              <p className="text-[#666666] text-sm">قبل ساعتين</p>
            </div>
          </div>
          <div className="flex items-start gap-4 pb-4 border-b border-[#e5e5e5]">
            <div className="w-2 h-2 bg-[#F4C400] rounded-full mt-2"></div>
            <div className="flex-1">
              <p className="text-[#111111] font-medium">
                تم إغلاق تذكرة الدعم #1234
              </p>
              <p className="text-[#666666] text-sm">قبل 4 ساعات</p>
            </div>
          </div>
          <div className="flex items-start gap-4">
            <div className="w-2 h-2 bg-[#F4C400] rounded-full mt-2"></div>
            <div className="flex-1">
              <p className="text-[#111111] font-medium">
                تم صرف مرتبات شهر مارس
              </p>
              <p className="text-[#666666] text-sm">قبل يوم واحد</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
