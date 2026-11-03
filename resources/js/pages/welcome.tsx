import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowDownRight, ArrowRight, Check, LockKeyhole, Sparkles } from 'lucide-react';
import { type SharedData } from '@/types';

export default function Welcome() {
    const { auth, localAuthEnabled = false } = usePage<SharedData>().props;
    const signedIn = Boolean(auth.user);

    return (
        <>
            <Head title="SprintHR Talent — A profile that moves with you" />
            <div className="min-h-screen overflow-hidden bg-canvas text-ink">
                <header className="mx-auto flex h-[78px] max-w-7xl items-center justify-between px-5 sm:px-8">
                    <Link href="/" className="flex items-center gap-2.5"><span className="grid size-9 place-items-center rounded-xl bg-ink text-lime"><Sparkles size={18} /></span><span className="font-semibold tracking-tight">SprintHR <span className="font-normal text-slate-400">Talent</span></span></Link>
                    <nav className="flex items-center gap-3 text-sm">
                        <a href="#how-it-works" className="hidden text-slate-500 hover:text-ink sm:block">How it works</a>
                        {signedIn ? <Link href="/dashboard" className="rounded-xl bg-ink px-4 py-2.5 font-medium text-white hover:bg-slate-800">Go to your profile <ArrowRight className="ml-1 inline" size={15} /></Link> : localAuthEnabled ? <><Link href="/login" className="px-3 py-2 font-medium text-slate-600 hover:text-ink">Sign in</Link><Link href="/register" className="rounded-xl bg-ink px-4 py-2.5 font-medium text-white hover:bg-slate-800">Create profile <ArrowRight className="ml-1 inline" size={15} /></Link></> : <span className="text-xs font-medium text-slate-500">Secure sign-in is being configured</span>}
                    </nav>
                </header>

                <main>
                    <section className="relative mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-12 sm:px-8 sm:pb-24 sm:pt-16 lg:grid-cols-[1.04fr_0.96fr] lg:gap-16 lg:pb-28 lg:pt-20">
                        <div className="absolute -right-32 top-10 hidden size-[520px] rounded-full border border-cobalt/8 lg:block" />
                        <div className="absolute -right-16 top-26 hidden size-[400px] rounded-full border border-cobalt/8 lg:block" />
                        <div className="relative z-10">
                            <div className="mb-7 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-600 shadow-sm"><span className="size-1.5 rounded-full bg-lime-500" />A profile you control</div>
                            <h1 className="max-w-[680px] font-display text-[46px] font-semibold leading-[1.04] tracking-[-0.055em] text-ink sm:text-[64px] lg:text-[72px]">Your next chapter starts with <span className="relative inline-block">your story<span className="absolute -bottom-1 left-0 h-2 w-full -rotate-1 bg-lime/80" /></span></h1>
                            <p className="mt-7 max-w-lg text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Build one clear profile for the work you want. Choose when employers can discover it, and decide for yourself when to share more.</p>
                            <div className="mt-8 flex flex-wrap items-center gap-3">{signedIn ? <Link href="/dashboard" className="inline-flex h-12 items-center gap-2 rounded-xl bg-cobalt px-5 text-sm font-semibold text-white shadow-lg shadow-cobalt/20 transition hover:-translate-y-0.5 hover:bg-blue-700">Open your profile<ArrowRight size={16} /></Link> : localAuthEnabled ? <Link href="/register" className="inline-flex h-12 items-center gap-2 rounded-xl bg-cobalt px-5 text-sm font-semibold text-white shadow-lg shadow-cobalt/20 transition hover:-translate-y-0.5 hover:bg-blue-700">Create your free profile<ArrowRight size={16} /></Link> : <span className="inline-flex h-12 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-medium text-slate-500">Sign-in will open once identity is connected</span>}<a href="#privacy" className="inline-flex h-12 items-center gap-2 px-3 text-sm font-semibold text-slate-600 hover:text-ink">How privacy works<ArrowDownRight size={15} /></a></div>
                            <div className="mt-8 flex items-center gap-3 text-xs text-slate-500"><LockKeyhole size={15} className="text-cobalt" /><span>Your profile starts private. Discovery is always your choice.</span></div>
                        </div>

                        <div className="relative mx-auto w-full max-w-[520px] lg:mr-0">
                            <div className="absolute -left-5 top-16 hidden h-[370px] w-3 flex-col items-center justify-between sm:flex"><span className="size-2 rounded-full bg-cobalt" /><span className="w-px flex-1 border-l border-dashed border-slate-300" /><span className="size-2 rounded-full bg-lime-500" /></div>
                            <div className="relative rounded-[28px] border border-slate-200 bg-white p-4 shadow-[0_35px_100px_-35px_rgba(19,34,58,0.28)] sm:p-6">
                                <div className="flex items-start justify-between border-b border-slate-100 pb-5"><div className="flex items-center gap-3"><div className="grid size-12 place-items-center rounded-2xl bg-[#E8EDFF] text-sm font-bold text-cobalt">AM</div><div><p className="text-sm font-semibold">Alex Morgan</p><p className="mt-0.5 text-xs text-slate-500">Product designer · Cebu City</p></div></div><span className="rounded-full bg-[#EFF8D9] px-2.5 py-1 text-[10px] font-semibold text-[#4D6812]">PROFILE PREVIEW</span></div>
                                <div className="grid grid-cols-[26px_1fr] gap-x-4 py-5"><div className="flex flex-col items-center"><span className="mt-1 size-2 rounded-full bg-cobalt ring-4 ring-cobalt/10" /><span className="my-1 h-14 border-l border-dashed border-slate-300" /><span className="size-2 rounded-full bg-lime-500 ring-4 ring-lime/20" /></div><div className="space-y-5"><div><p className="text-[10px] font-semibold tracking-[0.16em] text-cobalt">NOW</p><p className="mt-1 text-sm font-semibold">Senior Product Designer</p><p className="mt-1 text-xs text-slate-500">Building useful tools for growing teams</p></div><div><p className="text-[10px] font-semibold tracking-[0.16em] text-slate-400">EXPERIENCE</p><p className="mt-1 text-sm font-semibold">Design systems · Research · Prototyping</p><p className="mt-1 text-xs text-slate-500">A career path told in your own words</p></div></div></div>
                                <div className="rounded-2xl bg-[#F4F6FA] p-4"><div className="mb-3 flex items-center justify-between"><p className="text-xs font-semibold">Skills you choose to share</p><span className="text-[10px] font-medium text-slate-400">5 skills</span></div><div className="flex flex-wrap gap-2">{['Product design', 'Figma', 'Research', 'Prototyping', 'Systems'].map((skill) => <span key={skill} className="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[10px] font-medium text-slate-600">{skill}</span>)}</div></div>
                                <div className="mt-4 flex items-center gap-3 rounded-2xl bg-ink px-4 py-3.5 text-white"><span className="grid size-8 place-items-center rounded-xl bg-lime text-ink"><LockKeyhole size={15} /></span><p className="flex-1 text-xs leading-5 text-slate-200">Hidden from employer search<br /><strong className="font-semibold text-white">You decide when to be found.</strong></p><Check size={17} className="text-lime" /></div>
                            </div>
                            <div className="absolute -bottom-5 -right-3 hidden rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-lg sm:block"><div className="flex items-center gap-2"><span className="grid size-7 place-items-center rounded-lg bg-[#E8EDFF] text-cobalt"><Sparkles size={15} /></span><span className="text-[11px] font-semibold">One profile. Your rules.</span></div></div>
                        </div>
                    </section>

                    <section id="how-it-works" className="border-y border-slate-200 bg-white py-16 sm:py-20">
                        <div className="mx-auto max-w-7xl px-5 sm:px-8"><div className="mb-10 max-w-xl"><p className="text-[10px] font-bold tracking-[0.2em] text-cobalt">A SIMPLE START</p><h2 className="mt-3 font-display text-3xl font-semibold tracking-[-0.04em] sm:text-4xl">Make your experience easier to see.</h2></div><div className="grid gap-8 md:grid-cols-3">{[{ n: '01', title: 'Build your profile', desc: 'Add the work, skills and preferences that make your next role a good fit.' }, { n: '02', title: 'Choose your visibility', desc: 'Keep your profile private or opt in to relevant employer discovery.' }, { n: '03', title: 'Respond on your terms', desc: 'Review each invitation before you decide whether to share more.' }].map((item) => <article key={item.n} className="border-t border-slate-200 pt-4"><p className="font-mono text-xs font-semibold tracking-widest text-cobalt">{item.n}</p><h3 className="mt-5 text-base font-semibold">{item.title}</h3><p className="mt-2 max-w-xs text-sm leading-6 text-slate-500">{item.desc}</p></article>)}</div></div>
                    </section>

                    <section id="privacy" className="mx-auto grid max-w-7xl gap-8 px-5 py-16 sm:px-8 sm:py-20 md:grid-cols-[0.8fr_1.2fr] md:items-center"><div><p className="text-[10px] font-bold tracking-[0.2em] text-cobalt">PRIVACY, BY DEFAULT</p><h2 className="mt-3 font-display text-3xl font-semibold tracking-[-0.04em] sm:text-4xl">Your information is yours to share.</h2></div><div className="grid gap-3 sm:grid-cols-2">{['Discovery starts off', 'Your contact details stay hidden', 'Invitations show who is reaching out', 'You can change your mind anytime'].map((item) => <div key={item} className="flex gap-3 rounded-2xl border border-slate-200 bg-white p-4"><span className="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-[#EFF8D9] text-[#4D6812]"><Check size={13} /></span><p className="text-sm font-medium leading-5 text-slate-700">{item}</p></div>)}</div></section>
                </main>
                <footer className="border-t border-slate-200 bg-white px-5 py-6 text-center text-xs text-slate-400">SprintHR Talent · A professional profile you control</footer>
            </div>
        </>
    );
}
