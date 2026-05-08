import { useState } from "react";
import { useNavigate } from "react-router";
import { ArrowRight, Upload } from "lucide-react";

export default function AddEmployeePage() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    fullName: "",
    email: "",
    phone: "",
    nationalId: "",
    birthDate: "",
    salary: "",
    department: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/dashboard/employees");
  };

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
  ) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center gap-4 mb-8">
        <button
          onClick={() => navigate("/dashboard/employees")}
          className="p-2 hover:bg-white rounded-lg transition-colors"
        >
          <ArrowRight className="w-5 h-5 text-[#111111]" />
        </button>
        <div>
          <h1 className="text-3xl font-bold text-[#111111] mb-2">
            إضافة موظف جديد
          </h1>
          <p className="text-[#666666]">املأ البيانات لإضافة موظف جديد للنظام</p>
        </div>
      </div>

      {/* Form */}
      <form onSubmit={handleSubmit}>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Info */}
          <div className="lg:col-span-2 space-y-6">
            {/* Personal Information */}
            <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
              <h3 className="text-lg font-bold text-[#111111] mb-6">
                البيانات الشخصية
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-[#111111] mb-2">
                    الاسم بالكامل
                  </label>
                  <input
                    type="text"
                    name="fullName"
                    value={formData.fullName}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    placeholder="ادخل الاسم الكامل"
                    required
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">
                    البريد الإلكتروني
                  </label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    placeholder="example@dayem.com"
                    required
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">
                    رقم الموبايل
                  </label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    placeholder="01012345678"
                    required
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">
                    الرقم القومي
                  </label>
                  <input
                    type="text"
                    name="nationalId"
                    value={formData.nationalId}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    placeholder="29901011234567"
                    required
                  />
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">
                    تاريخ الميلاد
                  </label>
                  <input
                    type="date"
                    name="birthDate"
                    value={formData.birthDate}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    required
                  />
                </div>
              </div>
            </div>

            {/* Job Information */}
            <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
              <h3 className="text-lg font-bold text-[#111111] mb-6">
                بيانات العمل
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-[#111111] mb-2">
                    المرتب الشهري
                  </label>
                  <input
                    type="number"
                    name="salary"
                    value={formData.salary}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    placeholder="15000"
                    required
                  />
                  <p className="text-sm text-[#666666] mt-1">جنيه مصري</p>
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">القسم</label>
                  <select
                    name="department"
                    value={formData.department}
                    onChange={handleChange}
                    className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                    required
                  >
                    <option value="">اختر القسم</option>
                    <option value="marketing">التسويق</option>
                    <option value="hr">الموارد البشرية</option>
                    <option value="tech">التكنولوجيا</option>
                    <option value="sales">المبيعات</option>
                    <option value="finance">المالية</option>
                    <option value="support">خدمة العملاء</option>
                  </select>
                </div>
              </div>
            </div>

            {/* Documents */}
            <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
              <h3 className="text-lg font-bold text-[#111111] mb-6">
                المستندات
              </h3>
              <div className="space-y-4">
                <div>
                  <label className="block text-[#111111] mb-2">
                    عقد العمل
                  </label>
                  <div className="border-2 border-dashed border-[#e5e5e5] rounded-lg p-6 text-center hover:border-[#F4C400] transition-colors cursor-pointer">
                    <Upload className="w-8 h-8 text-[#666666] mx-auto mb-2" />
                    <p className="text-[#666666]">
                      اضغط لرفع عقد العمل أو اسحبه هنا
                    </p>
                    <p className="text-sm text-[#666666] mt-1">PDF, DOC, DOCX</p>
                  </div>
                </div>
                <div>
                  <label className="block text-[#111111] mb-2">
                    مستندات إضافية
                  </label>
                  <div className="border-2 border-dashed border-[#e5e5e5] rounded-lg p-6 text-center hover:border-[#F4C400] transition-colors cursor-pointer">
                    <Upload className="w-8 h-8 text-[#666666] mx-auto mb-2" />
                    <p className="text-[#666666]">
                      اضغط لرفع المستندات أو اسحبها هنا
                    </p>
                    <p className="text-sm text-[#666666] mt-1">
                      يمكنك رفع ملفات متعددة
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Profile Picture */}
            <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
              <h3 className="text-lg font-bold text-[#111111] mb-4">
                صورة شخصية
              </h3>
              <div className="border-2 border-dashed border-[#e5e5e5] rounded-lg p-6 text-center hover:border-[#F4C400] transition-colors cursor-pointer">
                <div className="w-24 h-24 bg-[#fafafa] rounded-full mx-auto mb-3 flex items-center justify-center">
                  <Upload className="w-8 h-8 text-[#666666]" />
                </div>
                <p className="text-[#666666] text-sm">اضغط لرفع الصورة</p>
                <p className="text-xs text-[#666666] mt-1">JPG, PNG</p>
              </div>
            </div>

            {/* Actions */}
            <div className="bg-white rounded-xl p-6 border border-[#e5e5e5]">
              <button
                type="submit"
                className="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors mb-3"
              >
                إضافة الموظف
              </button>
              <button
                type="button"
                onClick={() => navigate("/dashboard/employees")}
                className="w-full bg-white text-[#111111] py-3 px-6 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#fafafa] transition-colors"
              >
                إلغاء
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}
