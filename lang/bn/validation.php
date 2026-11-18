<?php

/*
|--------------------------------------------------------------------------
| Validation messages (Bangla)
|--------------------------------------------------------------------------
| Plain, easy Bangla. Only the rules this application uses are listed here;
| anything missing falls back to lang/en/validation.php.
*/

return [
    'accepted' => ':attribute-এ সম্মতি দিন।',
    'after' => ':attribute অবশ্যই :date-এর পরের হতে হবে।',
    'before' => ':attribute অবশ্যই :date-এর আগের হতে হবে।',
    'before_or_equal' => ':attribute আজকের পরের তারিখ হতে পারবে না।',
    'between' => [
        'numeric' => ':attribute :min থেকে :max-এর মধ্যে হতে হবে।',
        'string' => ':attribute :min থেকে :max অক্ষরের মধ্যে হতে হবে।',
    ],
    'boolean' => ':attribute সঠিক নয়।',
    'confirmed' => 'দুইবার লেখা :attribute মিলছে না।',
    'current_password' => 'পাসওয়ার্ডটি সঠিক নয়।',
    'date' => ':attribute একটি সঠিক তারিখ নয়।',
    'different' => ':attribute আর :other আলাদা হতে হবে।',
    'digits' => ':attribute অবশ্যই :digits সংখ্যার হতে হবে।',
    'dimensions' => 'ছবির মাপ ঠিক নেই (কমপক্ষে ১০০×১০০ পিক্সেল)।',
    'email' => 'একটি সঠিক ইমেইল ঠিকানা দিন।',
    'exists' => 'বেছে নেওয়া :attribute সঠিক নয়।',
    'image' => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'in' => 'বেছে নেওয়া :attribute সঠিক নয়।',
    'integer' => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'max' => [
        'file' => ':attribute :max কিলোবাইটের বেশি হতে পারবে না।',
        'numeric' => ':attribute :max-এর বেশি হতে পারবে না।',
        'string' => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
    ],
    'mimes' => ':attribute অবশ্যই :values ফাইল হতে হবে।',
    'min' => [
        'numeric' => ':attribute কমপক্ষে :min হতে হবে।',
        'string' => ':attribute কমপক্ষে :min অক্ষরের হতে হবে।',
    ],
    'numeric' => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'password' => [
        'letters' => ':attribute-এ অন্তত একটি অক্ষর থাকতে হবে।',
        'mixed' => ':attribute-এ অন্তত একটি বড় হাতের ও একটি ছোট হাতের অক্ষর থাকতে হবে।',
        'numbers' => ':attribute-এ অন্তত একটি সংখ্যা থাকতে হবে।',
        'symbols' => ':attribute-এ অন্তত একটি চিহ্ন থাকতে হবে।',
        'uncompromised' => 'এই :attribute আগে ফাঁস হয়েছে। অন্য একটি পাসওয়ার্ড দিন।',
    ],
    'prohibited' => ':attribute দেওয়া যাবে না।',
    'regex' => ':attribute-এর ধরন সঠিক নয়।',
    'required' => ':attribute দিতে হবে।',
    'required_if' => ':attribute দিতে হবে।',
    'required_with' => ':attribute দিতে হবে।',
    'size' => [
        'string' => ':attribute অবশ্যই :size অক্ষরের হতে হবে।',
    ],
    'string' => ':attribute অবশ্যই লেখা হতে হবে।',
    'unique' => 'এই :attribute দিয়ে আগেই অ্যাকাউন্ট খোলা আছে।',
    'uploaded' => ':attribute আপলোড করা যায়নি।',

    'custom' => [],

    /*
    | Field names shown inside messages, e.g. "মোবাইল নম্বর দিতে হবে।"
    */
    'attributes' => [
        'name' => 'নাম',
        'email' => 'ইমেইল',
        'phone' => 'মোবাইল নম্বর',
        'login' => 'ইমেইল বা মোবাইল নম্বর',
        'password' => 'পাসওয়ার্ড',
        'current_password' => 'বর্তমান পাসওয়ার্ড',
        'blood_group_id' => 'রক্তের গ্রুপ',
        'blood_group' => 'রক্তের গ্রুপ',
        'division_id' => 'বিভাগ',
        'district_id' => 'জেলা',
        'upazila_id' => 'উপজেলা / থানা',
        'division' => 'বিভাগ',
        'district' => 'জেলা',
        'upazila' => 'উপজেলা / থানা',
        'area' => 'এলাকা',
        'area_name' => 'এলাকা / মহল্লা',
        'last_donation_date' => 'শেষ রক্তদানের তারিখ',
        'photo' => 'ছবি',
        'terms' => 'শর্তাবলি',
        'bio' => 'ছোট নোট',
        'code' => 'কোড',
        'donated_on' => 'রক্তদানের তারিখ',
        'location' => 'জায়গা',
        'notes' => 'নোট',
        'reason' => 'কারণ',
        'details' => 'বিস্তারিত',
        'hospital_name' => 'হাসপাতালের নাম',
        'hospital_address' => 'হাসপাতালের ঠিকানা',
        'patient_condition' => 'রোগীর অবস্থা',
        'units_needed' => 'ব্যাগের সংখ্যা',
        'needed_at' => 'রক্ত লাগার সময়',
        'contact_phone' => 'যোগাযোগের নম্বর',
        'latitude' => 'ম্যাপের জায়গা',
        'longitude' => 'ম্যাপের জায়গা',
        'message' => 'বার্তা',
        'type' => 'ধরন',
        'page' => 'পাতা',
        'sort' => 'সাজানো',
        'availability' => 'অবস্থা',
        'date_of_birth' => 'জন্মতারিখ',
        'weight_kg' => 'ওজন',
        'facebook_url' => 'ফেসবুক লিংক',
        'whatsapp_number' => 'হোয়াটসঅ্যাপ নম্বর',
    ],
];
