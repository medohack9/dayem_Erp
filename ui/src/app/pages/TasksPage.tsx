import { Plus, Calendar, Users, AlertCircle } from "lucide-react";

const tasks = [
  {
    id: 1,
    title: "تحديث موقع الشركة الإلكتروني",
    assignees: ["أحمد محمد", "محمود سعيد"],
    dueDate: "15/04/2026",
    priority: "عالية",
    status: "جاري التنفيذ",
  },
  {
    id: 2,
    title: "إعداد تقرير المبيعات الشهري",
    assignees: ["نورهان أحمد"],
    dueDate: "12/04/2026",
    priority: "عالية",
    status: "متأخرة",
  },
  {
    id: 3,
    title: "مراجعة عقود الموظفين الجدد",
    assignees: ["فاطمة حسن", "عمر خالد"],
    dueDate: "18/04/2026",
    priority: "متوسطة",
    status: "معلقة",
  },
  {
    id: 4,
    title: "تطوير نظام الحضور والانصراف",
    assignees: ["محمود سعيد"],
    dueDate: "25/04/2026",
    priority: "متوسطة",
    status: "جاري التنفيذ",
  },
  {
    id: 5,
    title: "حملة تسويقية على السوشيال ميديا",
    assignees: ["أحمد محمد", "كريم عادل"],
    dueDate: "08/04/2026",
    priority: "عالية",
    status: "مكتملة",
  },
  {
    id: 6,
    title: "تدريب الموظفين الجدد",
    assignees: ["فاطمة حسن", "منى سامي"],
    dueDate: "20/04/2026",
    priority: "منخفضة",
    status: "معلقة",
  },
];

export default function TasksPage() {
  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case "عالية":
        return "bg-red-100 text-red-800";
      case "متوسطة":
        return "bg-yellow-100 text-yellow-800";
      case "منخفضة":
        return "bg-blue-100 text-blue-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "مكتملة":
        return "bg-green-100 text-green-800";
      case "جاري التنفيذ":
        return "bg-blue-100 text-blue-800";
      case "معلقة":
        return "bg-gray-100 text-gray-800";
      case "متأخرة":
        return "bg-red-100 text-red-800";
      case "ملغية":
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
          <h1 className="text-3xl font-bold text-[#111111] mb-2">المهام</h1>
          <p className="text-[#666666]">تتبع وإدارة مهام الفريق</p>
        </div>
        <button className="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
          <Plus className="w-5 h-5" />
          إضافة مهمة جديدة
        </button>
      </div>

      {/* Filter Tabs */}
      <div className="flex gap-2 mb-6">
        <button className="px-4 py-2 bg-[#F4C400] text-[#111111] rounded-lg font-medium">
          الكل
        </button>
        <button className="px-4 py-2 bg-white text-[#666666] rounded-lg font-medium hover:bg-[#fafafa] border border-[#e5e5e5]">
          معلقة
        </button>
        <button className="px-4 py-2 bg-white text-[#666666] rounded-lg font-medium hover:bg-[#fafafa] border border-[#e5e5e5]">
          جاري التنفيذ
        </button>
        <button className="px-4 py-2 bg-white text-[#666666] rounded-lg font-medium hover:bg-[#fafafa] border border-[#e5e5e5]">
          مكتملة
        </button>
        <button className="px-4 py-2 bg-white text-[#666666] rounded-lg font-medium hover:bg-[#fafafa] border border-[#e5e5e5]">
          متأخرة
        </button>
      </div>

      {/* Tasks List */}
      <div className="space-y-4">
        {tasks.map((task) => (
          <div
            key={task.id}
            className="bg-white rounded-xl p-6 border border-[#e5e5e5] hover:shadow-md transition-shadow"
          >
            <div className="flex items-start justify-between mb-4">
              <div className="flex-1">
                <h3 className="text-lg font-bold text-[#111111] mb-2">
                  {task.title}
                </h3>
                <div className="flex items-center gap-4 text-sm text-[#666666]">
                  <div className="flex items-center gap-1">
                    <Calendar className="w-4 h-4" />
                    <span>{task.dueDate}</span>
                  </div>
                  <div className="flex items-center gap-1">
                    <Users className="w-4 h-4" />
                    <span>{task.assignees.join(", ")}</span>
                  </div>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <span
                  className={`px-3 py-1 rounded-full text-xs font-medium ${getPriorityColor(
                    task.priority
                  )}`}
                >
                  {task.priority}
                </span>
                <span
                  className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(
                    task.status
                  )}`}
                >
                  {task.status}
                </span>
              </div>
            </div>

            {task.status === "متأخرة" && (
              <div className="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                <AlertCircle className="w-4 h-4 text-red-600" />
                <span className="text-sm text-red-800">
                  هذه المهمة متأخرة عن الموعد المحدد
                </span>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
