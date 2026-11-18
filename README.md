# BloodLink: Blood Donor Finder for Bangladesh

BloodLink helps people who urgently need blood find voluntary donors in their own upazila or district, without exposing donors' private information.

**Stack:** Laravel 13 · PHP 8.4 · MySQL 8.4 · Blade · Tailwind CSS 4 · Alpine.js 3 · Filament 5 (admin) · Lucide icons · Leaflet + OpenStreetMap

---

## Features

| Area | What's implemented |
|---|---|
| **Roles** | Guest (search only), Registered user/donor, Administrator (Filament panel at `/admin`). |
| **Auth** | Registration with donor profile, sign-in by email **or** mobile number, **mobile OTP** verification only (hashed, expiring, attempt-limited codes; no email verification), password reset by email, password change (signs out other devices), self-deactivation and reactivation by signing in again, admin suspension (sessions are killed). |
| **Locations** | All 8 divisions, 64 districts and 495 upazilas (open *bangladesh-geocode* dataset), plus 66 Dhaka and Chattogram city thanas. Areas can be picked from a list or typed in; typed areas wait for admin approval or merge. |
| **Donor search** | Filter by blood group, division, district, upazila, area, availability and verified status. Sort by relevance, longest since donation, or newest. Card layout and pagination capped at 50 pages. Results show only a masked name ("Rahim U."), the general location, the donation month and the status. |
| **Monthly view limit** | Each account can unlock **10 unique donor contact profiles per calendar month** (Asia/Dhaka time). The limit is enforced on the server with a row lock and a unique `(viewer, donor, month)` index. Re-opening a profile is free. Refreshing, a new session or another device cannot reset it. It resets on the 1st. Unlocking requires a verified email and mobile number. |
| **Contact on unlock** | Phone numbers are never shown in search. A phone-verified member who unlocks a donor profile (max 10 unique donors per month) sees the full name, area and phone number. Donors consent to this in the terms at registration; consents are logged in `user_consents`. |
| **Donor self-service** | Availability is automatic (resting for 4 months after a donation). Location and free-text area/neighborhood, photo, and donation history (keeps `last_donation_date` in sync). Donors cannot delete history; they request removal and an admin approves it. |
| **Emergency requests** | Blood group, hospital, district/upazila, OpenStreetMap pin (search, click, or "use my location"), units, needed-by time, contact method, details. Requests from admin-verified users go live at once; others wait in **Pending** for moderation. On activation, compatible (ABO/Rh), available, phone-verified donors in the same district who are past the advisory 90-day gap are notified (database + email), same upazila first. Statuses: Pending, Active, Fulfilled, Cancelled, Expired. Auto-expiry runs on the scheduler. Donors can respond "I can donate". Requests can be reported. |
| **Trust & safety** | Abuse reports on donors, users and requests. Rate limits on search, unlocks, OTP, auth, contact requests, reports and emergency posts. Honeypots on public forms, non-guessable ULID URLs, HTML stripped from free text, Blade auto-escaping, CSRF everywhere, policies on every action, security headers, `no-store` caching on private pages, and account/personal-data deletion requests. |
| **Admin (Filament)** | Dashboard with KPIs, a registrations chart, donors by blood group, and the top viewers against the monthly limit. Users (verify/suspend/reactivate/restore), Donors (filter by group and location, verify), Emergency requests (approve and notify, fulfil, reject), Donation history (approve or reject removal requests), Contact requests (read-only history), Abuse reports (resolve, suspend or cancel the target), Profile view log, Data deletion requests (process or reject), and Divisions/Districts/Upazilas/Areas (approve or merge) plus Blood groups. |

### Pages
Home · About · Find Donors · Donor Details · Register · Login · Forgot/Reset password · Verify mobile · Dashboard · Edit Profile · Donation History · Emergency Requests (newest first) · Create Request · Request details · Notifications · Privacy Policy · Terms · Admin panel (English). All public pages are in Bangla (`APP_LOCALE=bn`, `lang/bn`).

---

## Architecture

```
app/
  Enums/                 UserRole, UserStatus, EmergencyStatus, ContactRequestStatus, ReportReason/Status, ContactMethod
  Models/                User, DonorProfile, BloodGroup, Division, District, Upazila, Area, DonationHistory,
                         DonorProfileView, ContactRequest, EmergencyRequest(+Response), Report, UserConsent,
                         PhoneVerificationCode, DataDeletionRequest
  Services/              ProfileViewLimitService   (monthly quota: the core privacy rule)
                         EmergencyRequestService   (create / moderate / expire)
                         DonorProfileService       (registration, location, photo, donation history)
                         PhoneVerificationService  (OTP) · DataDeletionService · Sms/* (log | http drivers)
  Jobs/NotifyNearbyDonors.php
  Notifications/         Emergency alert, donor responded, available again after rest
  Policies/              DonorProfile, EmergencyRequest, DonationHistory
  Http/Middleware/       EnsureAccountIsActive, EnsurePhoneIsVerified, SecurityHeaders, UseEnglishLocale (admin)
  Http/Requests/         Form requests (all validation server-side)
  Filament/              Admin resources and widgets
config/bloodlink.php     All business rules (limits, expiry, OTP, SMS)
database/data/*.json     Bangladesh geocode dataset used by LocationSeeder
resources/js/            Alpine components: locationPicker, mapPicker, mapView; tree-shaken Lucide icons
tests/Feature/           53 feature tests against MySQL
```

### Database (MySQL, InnoDB, utf8mb4)
`users` · `donor_profiles` · `blood_groups` · `divisions` · `districts` · `upazilas` · `areas` · `donation_histories` · `donor_profile_views` · `contact_requests` · `emergency_requests` · `emergency_request_responses` · `notifications` · `reports` · `user_consents` · `phone_verification_codes` · `data_deletion_requests`, plus Laravel's `sessions`, `cache`, `jobs` and `password_reset_tokens`. Foreign keys use cascade, restrict or null-on-delete as appropriate. Composite search index: `(blood_group_id, district_id, upazila_id, is_available)`.

---

## Local installation

**Requirements:** PHP ≥ 8.3 with `pdo_mysql, mbstring, intl, gd, zip, fileinfo, openssl, curl`, Composer 2, Node ≥ 20, MySQL 8 (or MariaDB 10.6+).

```bash
git clone <repo> bloodlink && cd bloodlink
cp .env.example .env              # then set DB_* (and ADMIN_EMAIL / ADMIN_PASSWORD)
# create the database:  CREATE DATABASE bloodlink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
composer setup                    # install, key, migrate+seed, storage:link, livewire assets, npm build
php artisan serve                 # http://127.0.0.1:8000
```

Optional extras:

```bash
SEED_DEMO_DATA=true php artisan db:seed --class=DemoDataSeeder   # synthetic donors (local only, password: "password")
php artisan bloodlink:create-admin                                # interactive admin creation / promotion
npm run dev                                                       # Vite hot reload while editing views
```

**Where to find dev "messages":** with `MAIL_MAILER=log` and `SMS_DRIVER=log`, verification emails and OTP codes are written to `storage/logs/laravel.log`.

### This workstation (already set up)
* PHP 8.4 (winget), Composer at `%LOCALAPPDATA%\devbin`, MySQL 8.4 running from `%LOCALAPPDATA%\mysql-data` on 127.0.0.1:3306.
* Start MySQL again after a reboot:
  `"C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe" --defaults-file="%LOCALAPPDATA%\mysql-dl\my.ini" --console`
* Databases: `bloodlink` (app) and `bloodlink_test` (tests). Credentials are in `.env`.
* Admin login: `/admin` with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`.

## Tests

```bash
php artisan test
```

The suite runs against the `bloodlink_test` MySQL database (see `phpunit.xml`), so foreign keys, unique indexes and row locks behave as in production. It covers registration and validation, the OTP flow and lockout, login by email or phone, rate limiting, suspension and deactivation, search privacy and filters, the monthly limit (uniqueness, the 10-profile cap, monthly reset, cross-device), the consent flow, emergency moderation and donor matching, expiry, admin access and actions, reports, data deletion and security headers.

---

## Production deployment checklist

1. **Server:** Nginx + PHP-FPM 8.3/8.4, MySQL 8, HTTPS (Let's Encrypt). Document root is `public/`.
2. **Environment:** `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_URL` (https), `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `TRUSTED_PROXIES` if behind a proxy, and `ADMIN_*` left empty (use `bloodlink:create-admin`).
3. **Mail:** configure SMTP/SES/Postmark (`MAIL_*`).
4. **SMS:** set `SMS_DRIVER=http`, `SMS_API_URL`, `SMS_API_KEY`, `SMS_SENDER_ID`, and adapt the payload keys in `app/Services/Sms/HttpSmsSender.php` to your gateway (BulkSMSBD, SSL Wireless, Alpha SMS, …).
5. **Queue:** `QUEUE_CONNECTION=database` (or redis), run a supervised worker:
   `php artisan queue:work --tries=3 --max-time=3600`
6. **Scheduler (cron):** `* * * * * cd /var/www/bloodlink && php artisan schedule:run >> /dev/null 2>&1`
   (expires requests every 15 min, prunes 12-month-old view logs and failed jobs daily).
7. **Deploy commands:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan db:seed --force          # reference data only; demo data never runs in production
   php artisan storage:link
   php artisan optimize                 # config/route/view/event cache
   php artisan filament:optimize
   ```
8. **Backups:** daily MySQL dumps, plus `storage/app/public` (profile photos).
9. **Legal:** have the Privacy Policy and Terms reviewed by Bangladeshi counsel before launch.

---

## Known limitations

* **SMS gateway** must be configured for real OTP delivery (it is log-only in development).
* **Distance search** works on the administrative hierarchy (upazila, then district), not GPS radius. This is intentional, because donor coordinates are never collected.
* **Nominatim** (hospital search on the request form) is a free, rate-limited OSM service. For heavy traffic, self-host it or use a commercial geocoder.
* The Windows PHP built-in server truncates large streamed files, so Livewire's JS is published to `public/vendor/livewire` (handled by `composer setup` / `post-update-cmd`). This does not affect Nginx or Apache.
