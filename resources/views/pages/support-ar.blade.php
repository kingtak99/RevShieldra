@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12">
    <h1 class="text-4xl font-bold mb-8 text-slate-900">الدعم</h1>
    
    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-slate-50 rounded-lg p-8 border border-slate-200">
            <h2 class="text-2xl font-bold mb-4 text-slate-900">احصل على المساعدة</h2>
            <p class="text-slate-600 mb-6">نحن هنا لمساعدتك على النجاح مع RevShieldra. إذا كان لديك أي أسئلة أو تحتاج إلى مساعدة، فلا تتردد في الاتصال بنا.</p>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">الدعم عبر البريد الإلكتروني</h3>
                    <p class="text-slate-600 mb-3">أرسل لنا بريدًا إلكترونيًا بسؤالك أو مخاوفك</p>
                    <a href="mailto:info.zaynix@gmail.com" class="inline-block bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition">اتصل بالدعم</a>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 rounded-lg p-8 border border-slate-200">
            <h2 class="text-2xl font-bold mb-4 text-slate-900">الأسئلة الشائعة</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">كيف أبدأ؟</h3>
                    <p class="text-slate-600 text-sm">أنشئ حسابًا وقم بإعداد ملف تعريف عملك وابدأ في جمع ملاحظات العملاء باستخدام روبوتنا الدردشة.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">طرق الدفع المقبولة؟</h3>
                    <p class="text-slate-600 text-sm">نقبل جميع بطاقات الائتمان الرئيسية من خلال معالج الدفع الآمن لدينا.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">هل يمكنني إلغاء اشتراكي؟</h3>
                    <p class="text-slate-600 text-sm">نعم، يمكنك إلغاء اشتراكك في أي وقت من إعدادات حسابك.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-12 bg-blue-50 rounded-lg p-8 border border-blue-200">
        <h2 class="text-2xl font-bold mb-4 text-slate-900">هل تحتاج إلى مزيد من المساعدة؟</h2>
        <p class="text-slate-600 mb-6">هل لديك سؤال لم يتم تناوله أعلاه؟ نود أن نسمع منك. تواصل مع فريق الدعم لدينا وسنعاود الاتصال بك في أقرب وقت ممكن.</p>
        <p class="text-slate-700">
            <strong>البريد الإلكتروني:</strong> <a href="mailto:info.zaynix@gmail.com" class="text-blue-600 hover:text-blue-800">info.zaynix@gmail.com</a>
        </p>
    </div>
</div>
@endsection
