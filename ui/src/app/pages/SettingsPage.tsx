import { User, Bell, Shield, Globe } from "lucide-react";

export default function SettingsPage() {
  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-[#111111] mb-2">الإعدادات</h1>
        <p className="text-[#666666]">إدارة إعدادات النظام والحساب</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Sidebar Menu */}
        <div className="space-y-2">
          <button className="w-full flex items-center gap-3 px-4 py-3 bg-[#F4C400] text-[#111111] rounded-lg font-medium">
            <User className="w-5 h-5" />
            الحساب الشخصي
          </button>
          <button className="w-full flex items-center gap-3 px-4 py-3 bg-white border border-[#e5e5e5] text-[#666666] rounded-lg font-medium hover:bg-[#fafafa]">
            <Bell className="w-5 h-5" />
            الإشعارات
          </button>
          <button className="w-full flex items-center gap-3 px-4 py-3 bg-white border border-[#e5e5e5] text-[#666666] rounded-lg font-medium hover:bg-[#fafafa]">
            <Shield className="w-5 h-5" />
            الأمان والخصوصية
          </button>
          <button className="w-full flex items-center gap-3 px-4 py-3 bg-white border border-[#e5e5e5] text-[#666666] rounded-lg font-medium hover:bg-[#fafafa]">
            <Globe className="w-5 h-5" />
            اللغة والمنطقة
          </button>
        </div>

        {/* Settings Content */}
        <div className="lg:col-span-2 space-y-6">
          {/* Profile Settings */}
          <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
            <h3 className="text-lg font-bold text-[#111111] mb-6">
              معلومات الحساب
            </h3>
            <div className="space-y-4">
              <div className="flex items-center gap-4 mb-6">
                <div className="w-20 h-20 bg-[#F4C400] rounded-full flex items-center justify-center">
                  <span className="text-[#111111] text-3xl font-bold">أ</span>
                </div>
                <button className="px-4 py-2 bg-[#fafafa] border border-[#e5e5e5] rounded-lg font-medium hover:bg-white">
                  تغيير الصورة
                </button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-[#111111] mb-2">الاسم الكامل</label>
                  <input
                    type="text"
                    defaultValue="أحمد محمد"
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">
                    البريد الإلكتروني
                  </label>
                  <input
                    type="email"
                    defaultValue="ahmed@dayem.com"
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">رقم الموبايل</label>
                  <input
                    type="tel"
                    defaultValue="01012345678"
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">الوظيفة</label>
                  <input
                    type="text"
                    defaultValue="مدير النظام"
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Password Settings */}
          <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
            <h3 className="text-lg font-bold text-[#111111] mb-6">
              تغيير كلمة المرور
            </h3>
            <div className="space-y-4">
              <div>
                <label className="block text-[#111111] mb-2">
                  كلمة المرور الحالية
                </label>
                <input
                  type="password"
                  className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                />
              </div>
              <div>
                <label className="block text-[#111111] mb-2">
                  كلمة المرور الجديدة
                </label>
                <input
                  type="password"
                  className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                />
              </div>
              <div>
                <label className="block text-[#111111] mb-2">
                  تأكيد كلمة المرور الجديدة
                </label>
                <input
                  type="password"
                  className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]"
                />
              </div>
            </div>
          </div>

          {/* Save Button */}
          <div className="flex justify-end gap-3">
            <button className="px-6 py-3 bg-white border border-[#e5e5e5] text-[#111111] rounded-lg font-bold hover:bg-[#fafafa]">
              إلغاء
            </button>
            <button className="px-6 py-3 bg-[#F4C400] text-[#111111] rounded-lg font-bold hover:bg-[#e5b600]">
              حفظ التغييرات
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
