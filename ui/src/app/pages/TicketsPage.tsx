import { useState } from "react";
import { Send, Paperclip } from "lucide-react";

const tickets = [
  {
    id: 1,
    title: "مشكلة في تسجيل الدخول",
    employee: "أحمد محمد",
    status: "مفتوحة",
    time: "منذ 10 دقائق",
    unread: 2,
  },
  {
    id: 2,
    title: "طلب إجازة مرضية",
    employee: "فاطمة حسن",
    status: "تحت المراجعة",
    time: "منذ ساعة",
    unread: 0,
  },
  {
    id: 3,
    title: "استفسار عن المرتب",
    employee: "محمود سعيد",
    status: "تم الرد",
    time: "منذ 3 ساعات",
    unread: 1,
  },
  {
    id: 4,
    title: "تحديث بيانات الحساب",
    employee: "نورهان أحمد",
    status: "مغلقة",
    time: "منذ يوم",
    unread: 0,
  },
];

const messages = [
  {
    id: 1,
    sender: "أحمد محمد",
    message: "السلام عليكم، عندي مشكلة في تسجيل الدخول للنظام",
    time: "10:30 ص",
    isEmployee: true,
  },
  {
    id: 2,
    sender: "الدعم الفني",
    message: "وعليكم السلام، ممكن توضح إيه المشكلة بالظبط؟",
    time: "10:32 ص",
    isEmployee: false,
  },
  {
    id: 3,
    sender: "أحمد محمد",
    message: "بيظهرلي رسالة خطأ لما بدخل كلمة المرور",
    time: "10:35 ص",
    isEmployee: true,
  },
  {
    id: 4,
    sender: "الدعم الفني",
    message: "تمام، جرب تعمل إعادة تعيين لكلمة المرور من خلال الرابط اللي هبعتهولك",
    time: "10:37 ص",
    isEmployee: false,
  },
];

export default function TicketsPage() {
  const [selectedTicket, setSelectedTicket] = useState(tickets[0]);
  const [messageInput, setMessageInput] = useState("");

  const getStatusColor = (status: string) => {
    switch (status) {
      case "مفتوحة":
        return "bg-orange-100 text-orange-800";
      case "تحت المراجعة":
        return "bg-blue-100 text-blue-800";
      case "تم الرد":
        return "bg-green-100 text-green-800";
      case "مغلقة":
        return "bg-gray-100 text-gray-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const handleSendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (messageInput.trim()) {
      setMessageInput("");
    }
  };

  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-[#111111] mb-2">التذاكر</h1>
        <p className="text-[#666666]">نظام الدعم والتواصل مع الموظفين</p>
      </div>

      {/* Tickets Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-280px)]">
        {/* Tickets List */}
        <div className="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
          <div className="p-4 border-b border-[#e5e5e5]">
            <h3 className="font-bold text-[#111111]">كل التذاكر</h3>
          </div>
          <div className="overflow-y-auto h-[calc(100%-60px)]">
            {tickets.map((ticket) => (
              <div
                key={ticket.id}
                onClick={() => setSelectedTicket(ticket)}
                className={`p-4 border-b border-[#e5e5e5] cursor-pointer transition-colors ${
                  selectedTicket.id === ticket.id
                    ? "bg-[#F4C400]/10 border-r-4 border-r-[#F4C400]"
                    : "hover:bg-[#fafafa]"
                }`}
              >
                <div className="flex items-start justify-between mb-2">
                  <h4 className="font-bold text-[#111111] text-sm">
                    {ticket.title}
                  </h4>
                  {ticket.unread > 0 && (
                    <span className="w-5 h-5 bg-[#F4C400] text-[#111111] rounded-full text-xs flex items-center justify-center font-bold">
                      {ticket.unread}
                    </span>
                  )}
                </div>
                <p className="text-[#666666] text-sm mb-2">{ticket.employee}</p>
                <div className="flex items-center justify-between">
                  <span
                    className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                      ticket.status
                    )}`}
                  >
                    {ticket.status}
                  </span>
                  <span className="text-xs text-[#666666]">{ticket.time}</span>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Chat Thread */}
        <div className="lg:col-span-2 bg-white rounded-xl border border-[#e5e5e5] flex flex-col">
          {/* Chat Header */}
          <div className="p-4 border-b border-[#e5e5e5]">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-bold text-[#111111]">
                  {selectedTicket.title}
                </h3>
                <p className="text-sm text-[#666666]">
                  {selectedTicket.employee}
                </p>
              </div>
              <span
                className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(
                  selectedTicket.status
                )}`}
              >
                {selectedTicket.status}
              </span>
            </div>
          </div>

          {/* Messages */}
          <div className="flex-1 overflow-y-auto p-4 space-y-4">
            {messages.map((msg) => (
              <div
                key={msg.id}
                className={`flex ${
                  msg.isEmployee ? "justify-start" : "justify-end"
                }`}
              >
                <div
                  className={`max-w-[70%] ${
                    msg.isEmployee ? "order-2" : "order-1"
                  }`}
                >
                  <div
                    className={`rounded-lg p-4 ${
                      msg.isEmployee
                        ? "bg-[#fafafa] border border-[#e5e5e5]"
                        : "bg-[#F4C400] text-[#111111]"
                    }`}
                  >
                    <p className="text-sm font-medium mb-1">{msg.sender}</p>
                    <p className="text-sm">{msg.message}</p>
                  </div>
                  <p className="text-xs text-[#666666] mt-1 px-2">{msg.time}</p>
                </div>
              </div>
            ))}
          </div>

          {/* Message Input */}
          <form
            onSubmit={handleSendMessage}
            className="p-4 border-t border-[#e5e5e5]"
          >
            <div className="flex items-end gap-2">
              <button
                type="button"
                className="p-3 hover:bg-[#fafafa] rounded-lg transition-colors"
              >
                <Paperclip className="w-5 h-5 text-[#666666]" />
              </button>
              <input
                type="text"
                value={messageInput}
                onChange={(e) => setMessageInput(e.target.value)}
                placeholder="اكتب رسالتك هنا..."
                className="flex-1 px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
              />
              <button
                type="submit"
                className="p-3 bg-[#F4C400] text-[#111111] rounded-lg hover:bg-[#e5b600] transition-colors"
              >
                <Send className="w-5 h-5" />
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
