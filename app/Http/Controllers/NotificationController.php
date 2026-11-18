<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    /**
     * Mark one as read and follow its link. Only internal links that still match
     * a route are followed, so notifications for removed features (or anything
     * pointing off-site) fall back to the dashboard instead of an error page.
     */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url && $this->isLiveInternalUrl($request, $url)
            ? redirect()->to($url)
            : redirect()->route('dashboard')->with('info', 'এই নোটিফিকেশনের পেজটি আর নেই।');
    }

    private function isLiveInternalUrl(Request $request, string $url): bool
    {
        // Open-redirect guard: only links pointing at this application.
        $host = parse_url($url, PHP_URL_HOST);
        $internal = $host === null
            ? str_starts_with($url, '/') && ! str_starts_with($url, '//')
            : $host === $request->getHost() || $host === parse_url(config('app.url'), PHP_URL_HOST);

        if (! $internal) {
            return false;
        }

        try {
            Route::getRoutes()->match(Request::create($url, 'GET'));

            return true;
        } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
            return false;
        }
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'সব নোটিফিকেশন পড়া হয়েছে বলে চিহ্নিত করা হলো।');
    }
}
