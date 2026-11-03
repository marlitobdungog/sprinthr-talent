import CandidateLayout from '@/layouts/candidate-layout';
import { EmptyState, FieldError, FieldLabel, PageHeading, Panel, fieldClass, primaryButton } from '@/components/portal';
import { Head, router, useForm } from '@inertiajs/react';
import { ArrowRight, Code2, Plus, Sparkles, Trash2 } from 'lucide-react';
import { type FormEvent } from 'react';

type Skill = { id: number; name: string; experience_years: number | null };

export default function Skills({ skills }: { skills: Skill[] }) {
    const form = useForm({ name: '', experience_years: '' });
    const submit = (event: FormEvent) => { event.preventDefault(); form.post('/profile/skills', { preserveScroll: true, onSuccess: () => form.reset() }); };
    const remove = (skill: Skill) => { if (window.confirm(`Remove ${skill.name} from your profile?`)) router.delete(`/profile/skills/${skill.id}`, { preserveScroll: true }); };

    return <CandidateLayout title="Skills"><Head title="Your skills" />
        <PageHeading eyebrow="WHAT YOU BRING" title="Skills" description="Add the strengths and tools you want to be known for. You can edit or remove them anytime." action={<a href="/profile" className="inline-flex items-center gap-1.5 text-xs font-semibold text-cobalt">Profile basics <ArrowRight size={14} /></a>} />
        <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
            <Panel><div className="mb-5 flex items-center justify-between"><div><p className="text-[10px] font-bold tracking-[0.16em] text-slate-400">YOUR SKILLS</p><h2 className="mt-1.5 text-base font-semibold">A useful snapshot</h2></div><span className="text-xs text-slate-400">{skills.length} added</span></div>
                {skills.length ? <ul className="grid gap-2 sm:grid-cols-2">{skills.map((skill) => <li key={skill.id} className="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3"><span className="grid size-9 place-items-center rounded-xl bg-white text-cobalt shadow-sm"><Code2 size={17} /></span><div className="min-w-0 flex-1"><p className="truncate text-sm font-semibold">{skill.name}</p><p className="mt-0.5 text-[10px] text-slate-500">{skill.experience_years == null ? 'Experience not specified' : `${skill.experience_years} ${skill.experience_years === 1 ? 'year' : 'years'} experience`}</p></div><button type="button" aria-label={`Remove ${skill.name}`} onClick={() => remove(skill)} className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600"><Trash2 size={15} /></button></li>)}</ul> : <EmptyState icon={<Sparkles size={19} />} title="Start with what you know" description="Add a few skills that reflect the work you do well or would like to do." />}
            </Panel>
            <Panel><div className="mb-5"><p className="text-[10px] font-bold tracking-[0.16em] text-cobalt">ADD A SKILL</p><h2 className="mt-1.5 text-base font-semibold">Make your strengths visible</h2></div><form onSubmit={submit} className="space-y-4"><div><FieldLabel htmlFor="skill-name">Skill</FieldLabel><input id="skill-name" autoComplete="off" maxLength={80} placeholder="e.g. Project management" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} className={fieldClass} /><FieldError message={form.errors.name} /></div><div><FieldLabel htmlFor="skill-years">Years of experience <span className="font-normal text-slate-400">(optional)</span></FieldLabel><input id="skill-years" type="number" min="0" max="60" placeholder="e.g. 4" value={form.data.experience_years} onChange={(event) => form.setData('experience_years', event.target.value)} className={fieldClass} /><FieldError message={form.errors.experience_years} /></div><button disabled={form.processing} className={`${primaryButton} w-full`}>{form.processing ? 'Adding…' : 'Add skill'} <Plus size={15} /></button></form><div className="mt-5 rounded-xl bg-[#F0F4FF] p-3.5"><p className="text-[11px] leading-5 text-slate-600">You decide which profile details are visible. Skills are only included in employer discovery after you opt in.</p></div></Panel>
        </div>
    </CandidateLayout>;
}
