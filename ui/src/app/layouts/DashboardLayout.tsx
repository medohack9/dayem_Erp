import { Outlet, Link, useLocation } from "react-router";
import {
  Home,
  Users,
  Building2,
  CheckSquare,
  Ticket,
  DollarSign,
  FolderOpen,
  FileText,
  Settings,
} from "lucide-react";

const menuItems = [
  { path: "/dashboard", icon: Home, label: "الرئيسية" },
  { path: "/dashboard/employees", icon: Users, label: "الموظفين" },
  { path: "/dashboard/departments", icon: Building2, label: "الأقسام" },
  { path: "/dashboard/tasks", icon: CheckSquare, label: "المهام" },
  { path: "/dashboard/tickets", icon: Ticket, label: "التذاكر" },
  { path: "/dashboard/salaries", icon: DollarSign, label: "المرتبات" },
  { path: "/dashboard/files", icon: FolderOpen, label: "الملفات" },
  { path: "/dashboard/logs", icon: FileText, label: "السجلات" },
  { path: "/dashboard/settings", icon: Settings, label: "الإعدادات" },
];

export default function DashboardLayout() {
  const location = useLocation();

  const isActive = (path: string) => {
    if (path === "/dashboard") {
      return location.pathname === path;
    }
    return location.pathname.startsWith(path);
  };

  return (
    <div className="min-h-screen bg-[#fafafa]" dir="rtl">
      {/* Sidebar */}
      <aside className="fixed right-0 top-0 h-screen w-64 bg-[#111111] border-l border-[#1f1f1f] flex flex-col">
        {/* Logo */}
        <div className="p-6 border-b border-[#1f1f1f]">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-[#F4C400] rounded-lg flex items-center justify-center">
              <span className="text-[#111111] text-xl font-bold">د</span>
            </div>
            <div>
              <h1 className="text-white font-bold text-lg">دايم</h1>
              <p className="text-white/60 text-xs">نظام الإدارة</p>
            </div>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 p-4 space-y-1">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const active = isActive(item.path);

            return (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-all ${
                  active
                    ? "bg-[#F4C400] text-[#111111]"
                    : "text-white hover:bg-[#1f1f1f]"
                }`}
              >
                <Icon className="w-5 h-5" />
                <span className="font-medium">{item.label}</span>
              </Link>
            );
          })}
        </nav>

        {/* User Info */}
        <div className="p-4 border-t border-[#1f1f1f]">
          <div className="flex items-center gap-3 px-4 py-3">
            <div className="w-10 h-10 bg-[#F4C400] rounded-full flex items-center justify-center">
              <span className="text-[#111111] font-bold">أ</span>
            </div>
            <div className="flex-1">
              <p className="text-white text-sm font-medium">أحمد محمد</p>
              <p className="text-white/60 text-xs">مدير النظام</p>
            </div>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="mr-64">
        <Outlet />
      </main>
    </div>
  );
}
