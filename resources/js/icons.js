/**
 * Lucide icons, tree-shaken: only the icons used in Blade templates are bundled.
 * Usage in Blade: <x-icon name="map-pin" class="size-4" />
 */
import {
    createIcons,
    Activity, ArrowRight, BadgeCheck, Ban, Bell, BellRing, Calendar, CalendarClock, Camera, Check,
    ChevronDown, ChevronLeft, ChevronRight, CircleCheck, CircleUser, CircleX, Clock, Droplet, Droplets,
    ExternalLink, Eye, EyeOff, FileText, Flag, Globe, HeartHandshake, HeartPulse, History, Hospital, House,
    Inbox, Info, KeyRound, LayoutDashboard, LoaderCircle, Lock, LockOpen, LogIn, LogOut, Mail, Map as MapIcon,
    MapPin, Menu, MessageSquare, Navigation, Phone, Plus, Power, RefreshCw, Scale, Search, Send, Settings,
    Shield, ShieldCheck, Siren, SlidersHorizontal, Sparkles, Trash2, TriangleAlert, Upload, User, UserPlus,
    Users, X, ListChecks, Hand, Stethoscope, Target,
} from 'lucide';

const icons = {
    Activity, ArrowRight, BadgeCheck, Ban, Bell, BellRing, Calendar, CalendarClock, Camera, Check,
    ChevronDown, ChevronLeft, ChevronRight, CircleCheck, CircleUser, CircleX, Clock, Droplet, Droplets,
    ExternalLink, Eye, EyeOff, FileText, Flag, Globe, HeartHandshake, HeartPulse, History, Hospital, House,
    Inbox, Info, KeyRound, LayoutDashboard, LoaderCircle, Lock, LockOpen, LogIn, LogOut, Mail, Map: MapIcon,
    MapPin, Menu, MessageSquare, Navigation, Phone, Plus, Power, RefreshCw, Scale, Search, Send, Settings,
    Shield, ShieldCheck, Siren, SlidersHorizontal, Sparkles, Trash2, TriangleAlert, Upload, User, UserPlus,
    Users, X, ListChecks, Hand, Stethoscope, Target,
};

export function renderIcons() {
    createIcons({ icons, attrs: { 'stroke-width': 2, 'aria-hidden': 'true' } });
}

/** Re-render icons inserted later by Alpine (x-if / x-for). */
export function watchIcons() {
    let queued = false;
    new MutationObserver(() => {
        if (queued) return;
        queued = true;
        requestAnimationFrame(() => {
            queued = false;
            if (document.querySelector('i[data-lucide]')) renderIcons();
        });
    }).observe(document.body, { childList: true, subtree: true });
}
