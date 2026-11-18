<x-layouts.app title="আমাদের সম্পর্কে">
    <x-page-header title="BloodLink সম্পর্কে" subtitle="বাংলাদেশে দ্রুত, নিরাপদে ও গোপনীয়তা রক্ষা করে স্বেচ্ছায় রক্তদাতা খুঁজে পাওয়ার একটি কমিউনিটি প্ল্যাটফর্ম।" icon="heart-pulse" />

    <div class="container-page grid gap-8 py-10 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card card-body">
                <h2 class="text-xl font-bold">আমাদের লক্ষ্য</h2>
                <p class="mt-3 leading-7 text-slate-700">
                    প্রতিদিন দেশের অনেক পরিবার রক্তের জন্য ফেসবুক পোস্ট আর ফোনে ফোনে খোঁজাখুঁজি করে। BloodLink সেই কাজকে সহজ করে —
                    এলাকা অনুযায়ী সাজানো স্বেচ্ছায় রক্তদাতাদের একটি তালিকা, যেখানে ডোনারের তথ্যের নিরাপত্তাও রক্ষা করা হয়।
                </p>
            </section>

            <section class="card card-body">
                <h2 class="text-xl font-bold">আমরা কীভাবে আপনার তথ্য রক্ষা করি</h2>
                <ul class="mt-4 space-y-4">
                    @foreach ([
                        ['lock', 'নম্বর সবার জন্য খোলা নয়', 'খোঁজার তালিকায় কারও মোবাইল নম্বর দেখা যায় না। শুধু মোবাইল যাচাই করা সদস্যরা ডোনারের প্রোফাইল খুলে নম্বর দেখতে পারেন।'],
                        ['map-pin', 'শুধু সাধারণ এলাকা', 'আমরা শুধু উপজেলা ও জেলা দেখাই — কখনো বাসা, রাস্তা বা সঠিক লোকেশন নয়।'],
                        ['shield', 'নজরদারি', 'কে কতগুলো নম্বর দেখছেন, তা আমাদের টিম নজরে রাখে। অস্বাভাবিক বেশি নম্বর দেখলে অ্যাডমিনকে সতর্ক করা হয়।'],
                        ['flag', 'কমিউনিটি নজরদারি', 'ভুয়া অনুরোধ বা হয়রানি দেখলে যে কেউ রিপোর্ট করতে পারেন। আমাদের টিম দেখে ব্যবস্থা নেয়।'],
                    ] as [$icon, $title, $text])
                        <li class="flex gap-4">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-lucide :name="$icon" class="size-5" /></span>
                            <div><h3 class="font-semibold">{{ $title }}</h3><p class="text-sm text-slate-600">{{ $text }}</p></div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="card card-body">
                <h2 class="text-xl font-bold">স্বাস্থ্য বিষয়ে জরুরি কথা</h2>
                <p class="mt-3 leading-7 text-slate-700">
                    BloodLink শুধু যোগাযোগ করিয়ে দেয়, এটি কোনো চিকিৎসা সেবা নয়। ডোনাররা নিজেরাই তাঁদের তথ্য দেন।
                    তালিকায় থাকা মানেই কেউ এখন রক্ত দিতে পারবেন, এমন নয় — হাসপাতাল বা ব্লাড ব্যাংক প্রতিটি রক্তদানের আগে পরীক্ষা করবে।
                    রক্তের জন্য কখনো টাকা দেবেন না বা নেবেন না; কেউ চাইলে রিপোর্ট করুন।
                </p>
            </section>
        </div>

        <aside class="space-y-6">
            <div class="card card-body bg-brand-700 text-white">
                <x-lucide name="siren" class="size-8" />
                <h2 class="mt-3 text-lg font-bold">এখনই রক্ত দরকার?</h2>
                <p class="mt-1 text-sm text-brand-100">জরুরি অনুরোধ দিন। একই জেলার মিল থাকা, প্রস্তুত ডোনারদের জানানো হবে।</p>
                <a href="{{ route('emergency.create') }}" class="btn mt-5 w-full bg-white text-brand-800 hover:bg-brand-50">রক্তের অনুরোধ দিন</a>
            </div>
            <div class="card card-body">
                <h2 class="font-bold">যোগাযোগ</h2>
                <p class="mt-2 text-sm text-slate-600">প্রশ্ন, সহযোগিতা বা তথ্য-সংক্রান্ত অনুরোধ:</p>
                <a href="mailto:{{ config('bloodlink.support_email') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-brand-700"><x-lucide name="mail" /> {{ config('bloodlink.support_email') }}</a>
            </div>
        </aside>
    </div>
</x-layouts.app>
