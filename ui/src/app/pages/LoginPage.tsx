import { useState } from "react";
import { useNavigate } from "react-router";

export default function LoginPage() {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    navigate("/dashboard");
  };

  return (
    <div className="min-h-screen flex" dir="rtl">
      {/* Right Side - Form */}
      <div className="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div className="w-full max-w-md">
          {/* Logo */}
          <div className="mb-12">
            <div className="flex items-center gap-3 mb-2">
              <div className="w-12 h-12 bg-[#F4C400] rounded-lg flex items-center justify-center">
                <span className="text-[#111111] text-2xl font-bold">د</span>
              </div>
              <h1 className="text-3xl font-bold text-[#111111]">دايم | Dayem</h1>
            </div>
            <p className="text-[#666666] text-lg mr-[60px]">دايم... دايمًا حواليك</p>
          </div>

          {/* Welcome Message */}
          <div className="mb-8">
            <h2 className="text-2xl font-bold text-[#111111] mb-2">أهلاً بيك</h2>
            <p className="text-[#666666]">سجل دخول علشان تدخل على نظام الإدارة</p>
          </div>

          {/* Form */}
          <form onSubmit={handleLogin} className="space-y-6">
            <div>
              <label htmlFor="email" className="block text-[#111111] mb-2">
                البريد الإلكتروني أو رقم الموبايل
              </label>
              <input
                id="email"
                type="text"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                placeholder="ادخل البريد الإلكتروني أو رقم الموبايل"
                required
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-[#111111] mb-2">
                كلمة المرور
              </label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                placeholder="ادخل كلمة المرور"
                required
              />
            </div>

            <button
              type="submit"
              className="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors"
            >
              تسجيل الدخول
            </button>
          </form>

          <p className="text-center text-[#666666] text-sm mt-6">
            نسيت كلمة المرور؟{" "}
            <button className="text-[#111111] hover:underline font-medium">
              استرجع الآن
            </button>
          </p>
        </div>
      </div>

      {/* Left Side - Hero */}
      <div className="hidden lg:flex lg:w-1/2 bg-[#111111] items-center justify-center p-12 relative overflow-hidden">
        {/* Decorative Background */}
        <div className="absolute inset-0">
          <div className="absolute top-20 left-20 w-64 h-64 bg-[#F4C400] rounded-full opacity-20 blur-3xl"></div>
          <div className="absolute bottom-20 right-20 w-80 h-80 bg-[#F4C400] rounded-full opacity-10 blur-3xl"></div>
        </div>

        {/* Content */}
        <div className="relative text-center">
          <div className="mb-8">
            <div className="inline-flex items-center justify-center w-24 h-24 bg-[#F4C400] rounded-2xl mb-6">
              <span className="text-[#111111] text-5xl font-bold">د</span>
            </div>
            <h2 className="text-5xl font-bold text-white mb-4">دايم</h2>
            <p className="text-[#F4C400] text-2xl font-medium mb-8">دايمًا حواليك</p>
          </div>

          <div className="max-w-md mx-auto">
            <p className="text-white/80 text-lg leading-relaxed">
              نظام إدارة متكامل لإدارة الموظفين والمهام والمرتبات بكل سهولة وسرعة
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
