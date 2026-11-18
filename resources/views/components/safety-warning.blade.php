{{-- Safety warning (সতর্কীকরণ) shown wherever donors and requesters connect. --}}
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-amber-950']) }} role="note">
    <p class="flex items-center gap-2 font-bold"><x-lucide name="triangle-alert" class="size-5 text-amber-600" /> সতর্কীকরণ</p>
    <ul class="mt-2 list-disc space-y-1 pl-5">
        <li>পাবলিক প্লেস বা হাসপাতাল ছাড়া অন্য কোথাও কাউকে রক্ত দিতে না যাওয়ার জন্য অনুরোধ করা হলো।</li>
        <li>কাকে রক্ত দিচ্ছেন আর কার কাছ থেকে রক্ত নিচ্ছেন, তা নিজে যাচাই করে নেবেন। এখানে কেউ হয়রানির শিকার হলে কর্তৃপক্ষ দায়ী থাকবে না।</li>
    </ul>
</div>
