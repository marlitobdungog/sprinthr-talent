import { Link, router, usePage } from '@inertiajs/react';
import { Bell, BriefcaseBusiness, ChevronDown, CircleUserRound, FileText, House, LockKeyhole, LogOut, Menu, UserRound, X } from 'lucide-react';
import { type PropsWithChildren, useState } from 'react';
import { type SharedData } from '@/types';

const navigation = [
    { label: 'Overview', href: '/dashboard', icon: House, match: ['/dashboard'] },
    { label: 'Profile', href: '/profile', icon: UserRound, match: ['/profile'] },
    { label: 'Experience', href: '/profile/experience', icon: BriefcaseBusiness, match: ['/profile/experience'] },
    { label: 'Resume', href: '/resume', icon: FileText, match: ['/resume'] },
    { label: 'Privacy', href: '/privacy', icon: LockKeyhole, match: ['/privacy'] },
    { label: 'Invitations', href: '/invitations', icon: Bell, match: ['/invitations'] },
];

export default function CandidateLayout({ children, title, eyebrow = 'YOUR TALENT PROFILE' }: PropsWithChildren<{ title: string; eyebrow?: string }>) {
    const page = usePage<SharedData>();
    const { auth, flash } = page.props;
    const [menuOpen, setMenuOpen] = useState(false);
    if (!auth.user) return null;
    const currentUrl = page.url.split('?')[0];
    const initials = auth.user.name.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase();

    const links = (mobile = false) => navigation.filter((item) => !mobile || ['/dashboard', '/profile', '/privacy', '/invitations'].includes(item.href)).map((item) => {
        const active = item.match.some((path) => currentUrl === path || currentUrl.startsWith(`${path}/`));
        const Icon = item.icon;

        return (
            <Link key={item.href} href={item.href} onClick={() => setMenuOpen(false)} className={`group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors ${active ? 'bg-white/12 text-white' : 'text-slate-300 hover:bg-white/7 hover:text-white'} ${mobile ? 'flex-col gap-1 px-2 py-1 text-[10px]' : ''}`}>
                <Icon size={18} strokeWidth={1.8} className={active ? 'text-lime' : 'text-slate-400 group-hover:text-slate-200'} />
                <span>{item.label}</span>
            </Link>
        );
    });

    return (
        <div className="min-h-screen bg-canvas text-ink">
            <aside className="fixed inset-y-0 left-0 z-30 hidden w-[248px] flex-col bg-ink px-4 py-5 text-white lg:flex">
                <Link href="/dashboard" className="mb-11 flex items-center gap-3 px-2">
                    <img src="https://media.gleent.com/logos/Logo-SprintHR-white-text.png" alt="SprintHR" className="h-8 w-auto" />
                    <span className="border-l border-white/20 pl-3 text-[11px] font-medium tracking-[0.16em] text-white/70 uppercase">Talent</span>
                </Link>
                <p className="mb-3 px-3 text-[10px] font-semibold tracking-[0.18em] text-slate-500">YOUR SPACE</p>
                <nav className="space-y-1">{links()}</nav>
                <div className="mt-auto rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="mb-2 flex items-center gap-2 text-lime"><LockKeyhole size={14} /><span className="text-[10px] font-semibold tracking-[0.14em]">YOUR CHOICE</span></div>
                    <p className="text-xs leading-5 text-slate-300">Your profile stays private until you opt in to discovery.</p>
                    <Link href="/privacy" className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-white hover:text-lime">Review privacy <span aria-hidden="true">→</span></Link>
                </div>
                <div className="mt-4 flex items-center gap-3 border-t border-white/10 px-2 pt-4">
                    <span className="grid size-9 place-items-center rounded-full bg-cobalt text-xs font-bold text-white">{initials}</span>
                    <span className="min-w-0 flex-1"><strong className="block truncate text-xs font-medium">{auth.user.name}</strong><span className="block truncate text-[11px] text-slate-400">{auth.user.email}</span></span>
                    <Link href="/settings/data" aria-label="Account and data settings" className="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white"><ChevronDown size={16} /></Link>
                </div>
                <button type="button" onClick={() => router.post('/logout')} className="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs text-slate-400 hover:bg-white/7 hover:text-white"><LogOut size={16} />Sign out</button>
            </aside>

            <div className="lg:pl-[248px]">
                <header className="sticky top-0 z-20 flex h-[70px] items-center justify-between border-b border-line bg-white/90 px-5 backdrop-blur-md sm:px-8 lg:px-10">
                    <div className="flex items-center gap-3">
                        <button type="button" aria-label={menuOpen ? 'Close navigation' : 'Open navigation'} onClick={() => setMenuOpen(!menuOpen)} className="rounded-lg p-2 text-ink hover:bg-slate-100 lg:hidden">{menuOpen ? <X size={20} /> : <Menu size={20} />}</button>
                        <div><p className="hidden text-[9px] font-semibold tracking-[0.2em] text-cobalt sm:block">{eyebrow}</p><p className="text-sm font-semibold tracking-tight text-ink">{title}</p></div>
                    </div>
                    <div className="flex items-center gap-2 sm:gap-4">
                        <Link href="/invitations" className="relative rounded-xl p-2.5 text-slate-500 transition hover:bg-slate-100 hover:text-ink" aria-label="Invitations"><Bell size={18} strokeWidth={1.8} /></Link>
                        <Link href="/settings/data" className="flex items-center gap-2 rounded-xl border border-line bg-white px-2.5 py-2 text-xs font-medium text-slate-600 hover:border-slate-300"><CircleUserRound size={16} /><span className="hidden sm:inline">Account</span></Link>
                    </div>
                </header>
                {menuOpen && <nav className="fixed inset-x-0 top-[70px] z-20 border-b border-line bg-ink px-4 py-3 text-white shadow-xl lg:hidden"><div className="grid grid-cols-4 gap-1">{links(true)}</div></nav>}
                {flash?.success && <div role="status" className="mx-auto mt-5 flex max-w-6xl items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><span className="grid size-5 place-items-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">✓</span>{flash.success}</div>}
                <main className="mx-auto max-w-6xl px-5 pb-28 pt-7 sm:px-8 sm:pt-9 lg:px-10 lg:pb-12">{children}</main>
            </div>
            <nav aria-label="Mobile navigation" className="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-line bg-white px-2 pb-[env(safe-area-inset-bottom)] pt-2 shadow-[0_-8px_26px_rgba(19,34,58,0.07)] lg:hidden">{links(true)}</nav>
        </div>
    );
}
