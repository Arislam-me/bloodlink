@props(['facebook' => null, 'whatsapp' => null])
{{-- Facebook (required) and WhatsApp (optional) for donor profiles. --}}
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="facebook_url" class="label">ফেসবুক প্রোফাইলের লিংক <span class="text-brand-700">*</span></label>
        <input id="facebook_url" name="facebook_url" type="text" inputmode="url" value="{{ old('facebook_url', $facebook) }}" required maxlength="255" autocomplete="url"
               @class(['input', 'input-error' => $errors->has('facebook_url')]) placeholder="facebook.com/your.name">
        <p class="hint">ফেসবুক অ্যাপে নিজের প্রোফাইল খুলে "Copy link" দিয়ে লিংকটি কপি করুন।</p>
        <x-input-error for="facebook_url" />
    </div>
    <div>
        <label for="whatsapp_number" class="label">হোয়াটসঅ্যাপ নম্বর <span class="font-normal text-slate-500">(ইচ্ছা হলে)</span></label>
        <input id="whatsapp_number" name="whatsapp_number" type="tel" inputmode="numeric" value="{{ old('whatsapp_number', $whatsapp) }}" maxlength="20"
               @class(['input', 'input-error' => $errors->has('whatsapp_number')]) placeholder="01XXXXXXXXX">
        <p class="hint">মোবাইল নম্বরের সাথে আলাদা হলে দিন।</p>
        <x-input-error for="whatsapp_number" />
    </div>
</div>
