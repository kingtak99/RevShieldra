@extends('layouts.app') {{-- تأكد من استخدام اسم الـ layout الرئيسي لمشروعك هنا --}}

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 border-b border-gray-100 pb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">سجل التعلم الذاتي للبوت</h1>
                <p class="text-sm text-gray-500 mt-1">تتبع وإدارة جميع الكلمات الدلالية والصياغات التي تعلمها البوت تلقائياً
                    عبر الذكاء الاصطناعي</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ url('/run-chatbot-learning') }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.213 15M16 8l4-4m0 0l4 4m-4-4v12"></path>
                    </svg>
                    تشغيل دورة تعلم فورية
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl border border-gray-150 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">إجمالي الكلمات المتعلمة</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $learnedKeywords->total() }}</h3>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-150 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">من أخطاء واستفسارات الناس</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $learnedKeywords->where('source', 'gemini')->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-150 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">من الابتكار والتعلم الذاتي</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $learnedKeywords->where('source', 'gemini_self_learning')->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl border border-gray-150 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-right" dir="rtl">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">الكلمة /
                                العبارة الدلالية</th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">التدفق
                                المستهدف (Flow)</th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">الفرع
                                (Branch)</th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">نسبة الثقة
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">مصدر التعلم
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">اللغة</th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">تاريخ الحفظ
                            </th>
                            <th scope="col"
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($learnedKeywords as $keyword)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="text-sm font-medium text-gray-900 bg-gray-100 px-2.5 py-1 rounded-md">{{ $keyword->keyword }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 font-mono">{{ $keyword->target_flow }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="text-sm text-gray-500 font-mono">{{ $keyword->target_branch ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($keyword->confidence)
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $keyword->confidence >= 0.85 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                {{ number_format($keyword->confidence * 100, 0) }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($keyword->source === 'gemini_self_learning')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            🚀 تعلم ذاتي استباقي
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            💬 معالجة خطأ مستخدم
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 uppercase">
                                    {{ $keyword->language }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $keyword->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('settings.chatbot-learning.destroy', $keyword->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذه العبارة التعليمية؟ البوت لن يستجيب لها بعد الآن.')"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-900 font-medium transition">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                            </path>
                                        </svg>
                                        <p class="text-base font-semibold text-gray-700">لم يتعلم البوت أي صياغات بعد</p>
                                        <p class="text-xs text-gray-400 mt-1">قم بتشغيل دورة التعلم أو انتظر استعلامات
                                            المستخدمين لتظهر السجلات هنا</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($learnedKeywords->hasPages())
                <div class="bg-white px-4 py-4 border-t border-gray-150 sm:px-6">
                    {{ $learnedKeywords->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
```
eof

---

### 🛠️ طريقة تفعيل هذه الصفحة برمجياً في تطبيقك:

لكي تفتح هذه الواجهة بنجاح، تحتاج فقط لتعريف مسار (Route) ودالة متحكم (Controller) تجلب البيانات المذكورة:

#### 1. أضف الـ Routes في ملف `routes/web.php`:
افتح ملف الـ Routes وأضف السطرين التاليين (مثلاً تحت مظلة الـ `auth` middleware لكي لا يراها الزوار العاديون):

```php
// مسار لعرض الصفحة
Route::get('settings/chatbot-learning', [SettingsController::class,
'chatbotLearningDashboard'])->name('settings.chatbot-learning');

// مسار لحذف الكلمة في حال لم تعجبك
Route::delete('settings/chatbot-learning/{keyword}', [SettingsController::class,
'destroyLearnedKeyword'])->name('settings.chatbot-learning.destroy');
```

#### 2. أضف الدوال البرمجية في الـ `SettingsController.php`:
افتح ملف `app/Http/Controllers/SettingsController.php` وأضف الدالتين التاليتين داخله لجلب البيانات وحذفها:

```php
use App\Models\ChatbotLearnedKeyword;

// دالة جلب وعرض البيانات في الجدول
public function chatbotLearningDashboard()
{
$learnedKeywords = ChatbotLearnedKeyword::query()
->orderByDesc('created_at')
->paginate(15);

return view('settings.chatbot_learning', compact('learnedKeywords'));
}

// دالة حذف العبارة إذا رغبت في إلغاء تفعيلها
public function destroyLearnedKeyword($id)
{
$keyword = ChatbotLearnedKeyword::findOrFail($id);
$keyword->delete();

return redirect()->back()->with('success', 'تم حذف الكلمة الدلالية بنجاح.');
}
