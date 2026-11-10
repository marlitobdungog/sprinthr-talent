import { Link } from '@inertiajs/react';
import { Check } from 'lucide-react';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    description?: string;
}

const highlights = [
    'Build one profile and share it with teams that are hiring',
    'You decide what is visible, and you can change it any time',
    'Sign in with email or Google, whichever you prefer',
];

const logoUrl = 'https://media.gleent.com/logos/Logo-SprintHR-white-text.png';

export default function AuthBrandLayout({ children, title, description }: AuthLayoutProps) {
    return (
        <div className="bg-canvas text-ink grid min-h-svh md:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]">
            <aside className="bg-ink relative hidden overflow-hidden p-12 text-white md:flex md:flex-col md:justify-between">
                <div className="bg-cobalt/30 pointer-events-none absolute -top-24 -right-24 size-80 rounded-full blur-3xl" />
                <div className="bg-lime/10 pointer-events-none absolute -bottom-32 -left-20 size-96 rounded-full blur-3xl" />

                <Link href={route('home')} className="relative flex items-center gap-3">
                    <img src={logoUrl} alt="SprintHR" className="h-8 w-auto" />
                    <span className="border-l border-white/20 pl-3 text-sm font-medium tracking-wide text-white/70 uppercase">Talent</span>
                </Link>

                <div className="relative max-w-md">
                    <p className="text-lime text-sm font-medium tracking-wide uppercase">Your career profile</p>
                    <h2 className="mt-4 text-4xl leading-tight font-semibold tracking-tight">A profile that moves with you.</h2>
                    <ul className="mt-8 space-y-4">
                        {highlights.map((item) => (
                            <li key={item} className="flex items-start gap-3 text-white/80">
                                <span className="bg-lime text-ink mt-0.5 grid size-5 shrink-0 place-items-center rounded-full">
                                    <Check size={13} strokeWidth={3} />
                                </span>
                                <span className="text-[15px] leading-relaxed">{item}</span>
                            </li>
                        ))}
                    </ul>
                </div>

                <p className="relative text-xs text-white/50">Your data stays private until you choose to share it.</p>
            </aside>

            <main className="flex flex-col items-center justify-center px-5 py-10 sm:px-8">
                <div className="w-full max-w-md">
                    <Link href={route('home')} className="bg-ink mb-8 inline-flex items-center gap-3 rounded-2xl px-4 py-3 md:hidden">
                        <img src={logoUrl} alt="SprintHR" className="h-7 w-auto" />
                        <span className="border-l border-white/20 pl-3 text-sm font-medium tracking-wide text-white/70 uppercase">Talent</span>
                    </Link>

                    <div className="border-line bg-card rounded-3xl border p-7 shadow-[0_1px_2px_rgba(20,35,60,0.04),0_12px_40px_-12px_rgba(20,35,60,0.12)] sm:p-9">
                        <div className="mb-7 space-y-1.5">
                            <h1 className="text-ink text-2xl font-semibold tracking-tight">{title}</h1>
                            {description && <p className="text-sm text-slate-500">{description}</p>}
                        </div>
                        {children}
                    </div>
                </div>
            </main>
        </div>
    );
}
