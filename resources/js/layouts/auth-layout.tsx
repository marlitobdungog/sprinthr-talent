import AuthBrandLayout from '@/layouts/auth/auth-brand-layout';

export default function AuthLayout({ children, title, description, ...props }: { children: React.ReactNode; title: string; description: string }) {
    return (
        <AuthBrandLayout title={title} description={description} {...props}>
            {children}
        </AuthBrandLayout>
    );
}
