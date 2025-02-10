import { Link, Head } from '@inertiajs/react';

const Hello = ({ auth }) => {
    return (
        <>
            <Head title="Hello" />
            <div>
                <h1>Hello World from React</h1>
                <p>Current user: {auth?.user?.name ?? 'No auth is active'}</p>
                <Link href="/login">Go to Login</Link>
            </div>
        </>
    );
};

export default Hello;
