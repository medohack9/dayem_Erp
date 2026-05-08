import { Upload, Search, Download, FileText, Image, File } from "lucide-react";

const files = [
  {
    id: 1,
    name: "عقد عمل - أحمد محمد.pdf",
    type: "PDF",
    size: "2.5 MB",
    uploadDate: "05/04/2026",
    uploadedBy: "فاطمة حسن",
    priority: "عالية",
  },
  {
    id: 2,
    name: "تقرير المبيعات - مارس 2026.xlsx",
    type: "Excel",
    size: "1.8 MB",
    uploadDate: "01/04/2026",
    uploadedBy: "نورهان أحمد",
    priority: "عالية",
  },
  {
    id: 3,
    name: "صورة البطاقة - محمود سعيد.jpg",
    type: "صورة",
    size: "850 KB",
    uploadDate: "28/03/2026",
    uploadedBy: "فاطمة حسن",
    priority: "متوسطة",
  },
  {
    id: 4,
    name: "سياسة الشركة 2026.docx",
    type: "Word",
    size: "450 KB",
    uploadDate: "15/03/2026",
    uploadedBy: "عمر خالد",
    priority: "متوسطة",
  },
  {
    id: 5,
    name: "لوجو الشركة الجديد.png",
    type: "صورة",
    size: "1.2 MB",
    uploadDate: "10/03/2026",
    uploadedBy: "أحمد محمد",
    priority: "منخفضة",
  },
  {
    id: 6,
    name: "كشف الحضور - فبراير.pdf",
    type: "PDF",
    size: "3.1 MB",
    uploadDate: "01/03/2026",
    uploadedBy: "منى سامي",
    priority: "متوسطة",
  },
];

export default function FilesPage() {
  const getFileIcon = (type: string) => {
    if (type === "صورة") return Image;
    if (type === "PDF") return FileText;
    return File;
  };

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

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-[#111111] mb-2">الملفات</h1>
          <p className="text-[#666666]">إدارة ومشاركة ملفات الشركة</p>
        </div>
        <button className="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
          <Upload className="w-5 h-5" />
          رفع ملف جديد
        </button>
      </div>

      {/* Upload Area */}
      <div className="bg-white rounded-xl p-8 mb-6 border-2 border-dashed border-[#e5e5e5] hover:border-[#F4C400] transition-colors text-center cursor-pointer">
        <Upload className="w-12 h-12 text-[#666666] mx-auto mb-3" />
        <h3 className="text-lg font-bold text-[#111111] mb-2">
          اسحب وأفلت الملفات هنا
        </h3>
        <p className="text-[#666666]">أو اضغط لاختيار الملفات من جهازك</p>
        <p className="text-sm text-[#666666] mt-2">
          يدعم: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (حتى 10 MB)
        </p>
      </div>

      {/* Search and Filter */}
      <div className="bg-white rounded-xl p-4 mb-6 border border-[#e5e5e5]">
        <div className="flex items-center gap-4">
          <div className="flex-1 relative">
            <Search className="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#666666]" />
            <input
              type="text"
              placeholder="ابحث باسم الملف..."
              className="w-full pr-12 pl-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
            />
          </div>
          <select className="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400]">
            <option>ترتيب حسب الأولوية</option>
            <option>ترتيب حسب التاريخ</option>
            <option>ترتيب حسب الحجم</option>
          </select>
        </div>
      </div>

      {/* Files List */}
      <div className="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-[#fafafa] border-b border-[#e5e5e5]">
              <tr>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  اسم الملف
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  النوع
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  الحجم
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  تاريخ الرفع
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  رفع بواسطة
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  الأولوية
                </th>
                <th className="px-6 py-4 text-right text-sm font-bold text-[#111111]">
                  الإجراءات
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#e5e5e5]">
              {files.map((file) => {
                const FileIcon = getFileIcon(file.type);
                return (
                  <tr
                    key={file.id}
                    className="hover:bg-[#fafafa] transition-colors"
                  >
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-[#fafafa] border border-[#e5e5e5] rounded-lg flex items-center justify-center">
                          <FileIcon className="w-5 h-5 text-[#666666]" />
                        </div>
                        <span className="font-medium text-[#111111]">
                          {file.name}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-[#666666]">{file.type}</td>
                    <td className="px-6 py-4 text-[#666666]">{file.size}</td>
                    <td className="px-6 py-4 text-[#666666]">
                      {file.uploadDate}
                    </td>
                    <td className="px-6 py-4 text-[#666666]">
                      {file.uploadedBy}
                    </td>
                    <td className="px-6 py-4">
                      <span
                        className={`px-3 py-1 rounded-full text-xs font-medium ${getPriorityColor(
                          file.priority
                        )}`}
                      >
                        {file.priority}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <button className="p-2 hover:bg-[#fafafa] rounded-lg transition-colors">
                        <Download className="w-4 h-4 text-[#666666]" />
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
