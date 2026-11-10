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

type LoginForm = Record<string, string | boolean> & {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    privacyPolicyUrl: string;
}

const fieldClass = 'h-11 rounded-xl border-line bg-white px-4 text-[15px] shadow-none focus-visible:border-cobalt focus-visible:ring-cobalt/20';

export default function Login({ status, canResetPassword, privacyPolicyUrl }: LoginProps) {
    const { googleEnabled, errors: pageErrors } = usePage<{ googleEnabled?: boolean; errors: Record<string, string> }>().props;
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Welcome back" description="Log in to manage your talent profile.">
            <Head title="Log in" />

            {status && <div className="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">{status}</div>}
            {pageErrors?.google && <div className="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{pageErrors.google}</div>}

            {googleEnabled && (
                <div className="mb-6 grid gap-6">
                    <GoogleSignInButton label="Continue with Google" tabIndex={0} privacy />
                    <p className="text-center text-xs text-slate-500">
                        By continuing with Google you agree to the{' '}
                        <a
                            href={privacyPolicyUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-cobalt font-semibold hover:text-blue-700"
                        >
                            Privacy Policy
                        </a>
                        .
                    </p>
                    <OrDivider />
                </div>
            )}

            <form className="flex flex-col gap-5" onSubmit={submit}>
                <div className="grid gap-2">
                    <Label htmlFor="email" className="text-ink text-sm font-medium">
                        Email address
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autoFocus
                        tabIndex={1}
                        autoComplete="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="you@example.com"
                        className={fieldClass}
                    />
                    <InputError message={errors.email} />
                </div>

                <div className="grid gap-2">
                    <div className="flex items-center justify-between">
                        <Label htmlFor="password" className="text-ink text-sm font-medium">
                            Password
                        </Label>
                        {canResetPassword && (
                            <TextLink href={route('password.request')} className="text-cobalt text-sm hover:text-blue-700" tabIndex={5}>
                                Forgot password?
                            </TextLink>
                        )}
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabIndex={2}
                        autoComplete="current-password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        placeholder="Your password"
                        className={fieldClass}
                    />
                    <InputError message={errors.password} />
                </div>

                <div className="flex items-center gap-2.5">
                    <Checkbox id="remember" name="remember" tabIndex={3} className="rounded-md" />
                    <Label htmlFor="remember" className="text-sm font-normal text-slate-600">
                        Keep me signed in
                    </Label>
                </div>

                <Button
                    type="submit"
                    className="bg-cobalt shadow-cobalt/20 mt-1 h-11 w-full rounded-xl text-[15px] font-semibold shadow-lg hover:bg-blue-700"
                    tabIndex={4}
                    disabled={processing}
                >
                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                    Log in
                </Button>

                <p className="text-center text-sm text-slate-500">
                    New here?{' '}
                    <TextLink href={route('register')} className="text-cobalt font-semibold hover:text-blue-700" tabIndex={5}>
                        Create your profile
                    </TextLink>
                </p>
            </form>
        </AuthLayout>
    );
}
