import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import GoogleSignInButton, { OrDivider } from '@/components/google-sign-in-button';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = Record<string, string | boolean> & {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    privacy_policy: boolean;
};

const fieldClass = 'h-11 rounded-xl border-line bg-white px-4 text-[15px] shadow-none focus-visible:border-cobalt focus-visible:ring-cobalt/20';

export default function Register({ privacyPolicyUrl }: { privacyPolicyUrl: string }) {
    const { googleEnabled } = usePage<{ googleEnabled?: boolean }>().props;
    const { data, setData, post, processing, errors, reset } = useForm<RegisterForm>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        privacy_policy: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Create your profile" description="Start your talent profile in a few minutes.">
            <Head title="Register" />

            {googleEnabled && (
                <div className="mb-6 grid gap-6">
                    <GoogleSignInButton label="Sign up with Google" tabIndex={0} privacy={data.privacy_policy} disabled={!data.privacy_policy} />
                    <OrDivider />
                </div>
            )}

            <form className="flex flex-col gap-5" onSubmit={submit}>
                <div className="grid gap-2">
                    <Label htmlFor="name" className="text-ink text-sm font-medium">
                        Full name
                    </Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autoFocus
                        tabIndex={1}
                        autoComplete="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        disabled={processing}
                        placeholder="Jane Doe"
                        className={fieldClass}
                    />
                    <InputError message={errors.name} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="email" className="text-ink text-sm font-medium">
                        Email address
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        tabIndex={2}
                        autoComplete="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        disabled={processing}
                        placeholder="you@example.com"
                        className={fieldClass}
                    />
                    <InputError message={errors.email} />
                    {errors.email?.includes('taken') && (
                        <p className="rounded-xl bg-blue-50 px-3 py-2 text-sm text-slate-600">
                            Already registered? <TextLink href={route('login')}>Log in</TextLink> with this email, or use Google.
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="password" className="text-ink text-sm font-medium">
                        Password
                    </Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabIndex={3}
                        autoComplete="new-password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        disabled={processing}
                        placeholder="At least 8 characters"
                        className={fieldClass}
                    />
                    <InputError message={errors.password} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="password_confirmation" className="text-ink text-sm font-medium">
                        Confirm password
                    </Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        tabIndex={4}
                        autoComplete="new-password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        disabled={processing}
                        placeholder="Repeat your password"
                        className={fieldClass}
                    />
                    <InputError message={errors.password_confirmation} />
                </div>

                <div className="grid gap-2">
                    <div className="flex items-start gap-2.5">
                        <Checkbox
                            id="privacy_policy"
                            tabIndex={5}
                            checked={data.privacy_policy}
                            onCheckedChange={(checked) => setData('privacy_policy', checked === true)}
                            disabled={processing}
                            className="mt-0.5 rounded-md"
                        />
                        <Label htmlFor="privacy_policy" className="text-sm leading-relaxed font-normal text-slate-600">
                            I have read and agree to the{' '}
                            <a
                                href={privacyPolicyUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-cobalt font-semibold hover:text-blue-700"
                            >
                                Privacy Policy
                            </a>
                            .
                        </Label>
                    </div>
                    <InputError message={errors.privacy_policy} />
                </div>

                <Button
                    type="submit"
                    className="bg-cobalt shadow-cobalt/20 mt-1 h-11 w-full rounded-xl text-[15px] font-semibold shadow-lg hover:bg-blue-700"
                    tabIndex={6}
                    disabled={processing || !data.privacy_policy}
                >
                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                    Create account
                </Button>

                <p className="text-center text-sm text-slate-500">
                    Already have an account?{' '}
                    <TextLink href={route('login')} className="text-cobalt font-semibold hover:text-blue-700" tabIndex={7}>
                        Log in
                    </TextLink>
                </p>
            </form>
        </AuthLayout>
    );
}
