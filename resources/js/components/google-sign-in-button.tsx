import { Button } from '@/components/ui/button';

export default function GoogleSignInButton({
    label,
    tabIndex,
    privacy = false,
    disabled = false,
}: {
    label: string;
    tabIndex?: number;
    privacy?: boolean;
    disabled?: boolean;
}) {
    const className = 'h-11 w-full rounded-xl border-line bg-white text-ink shadow-sm hover:bg-slate-50';
    const icon = (
        <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24">
            <path
                fill="#4285F4"
                d="M23.5 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.46a5.52 5.52 0 0 1-2.4 3.62v3h3.88c2.27-2.09 3.56-5.17 3.56-8.81z"
            />
            <path
                fill="#34A853"
                d="M12 24c3.24 0 5.96-1.07 7.94-2.91l-3.88-3c-1.07.72-2.45 1.15-4.06 1.15-3.12 0-5.77-2.11-6.71-4.95H1.28v3.09A12 12 0 0 0 12 24z"
            />
            <path fill="#FBBC05" d="M5.29 14.29a7.2 7.2 0 0 1 0-4.58V6.62H1.28a12 12 0 0 0 0 10.76l4.01-3.09z" />
            <path
                fill="#EA4335"
                d="M12 4.77c1.76 0 3.34.61 4.59 1.8l3.44-3.44A11.5 11.5 0 0 0 12 0 12 12 0 0 0 1.28 6.62l4.01 3.09C6.23 6.88 8.88 4.77 12 4.77z"
            />
        </svg>
    );

    if (disabled) {
        return (
            <Button type="button" variant="outline" className={className} disabled tabIndex={tabIndex}>
                {icon}
                {label}
            </Button>
        );
    }

    return (
        <Button asChild variant="outline" className={className} tabIndex={tabIndex}>
            <a href={route('auth.google.redirect', privacy ? { privacy: 1 } : {})}>
                {icon}
                {label}
            </a>
        </Button>
    );
}

export function OrDivider() {
    return (
        <div className="text-muted-foreground flex items-center gap-3 text-xs uppercase">
            <span className="bg-border h-px flex-1" />
            or
            <span className="bg-border h-px flex-1" />
        </div>
    );
}
