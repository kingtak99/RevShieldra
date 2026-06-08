@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12">
    <h1 class="text-4xl font-bold mb-8 text-slate-900">Support</h1>
    
    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-slate-50 rounded-lg p-8 border border-slate-200">
            <h2 class="text-2xl font-bold mb-4 text-slate-900">Get Help</h2>
            <p class="text-slate-600 mb-6">We're here to help you succeed with RevShieldra. If you have any questions or need assistance, don't hesitate to reach out.</p>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">Email Support</h3>
                    <p class="text-slate-600 mb-3">Send us an email with your question or concern</p>
                    <a href="mailto:info.zaynix@gmail.com" class="inline-block bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition">Contact Support</a>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 rounded-lg p-8 border border-slate-200">
            <h2 class="text-2xl font-bold mb-4 text-slate-900">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">How do I get started?</h3>
                    <p class="text-slate-600 text-sm">Create an account, set up your business profile, and start collecting customer feedback with our chatbot.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">What payment methods do you accept?</h3>
                    <p class="text-slate-600 text-sm">We accept all major credit cards through our secure payment processor.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 mb-2">Can I cancel my subscription?</h3>
                    <p class="text-slate-600 text-sm">Yes, you can cancel your subscription anytime from your account settings.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-12 bg-blue-50 rounded-lg p-8 border border-blue-200">
        <h2 class="text-2xl font-bold mb-4 text-slate-900">Still Need Help?</h2>
        <p class="text-slate-600 mb-6">Have a question that's not covered above? We'd love to hear from you. Reach out to our support team and we'll get back to you as soon as possible.</p>
        <p class="text-slate-700">
            <strong>Email:</strong> <a href="mailto:info.zaynix@gmail.com" class="text-blue-600 hover:text-blue-800">info.zaynix@gmail.com</a>
        </p>
    </div>
</div>
@endsection
