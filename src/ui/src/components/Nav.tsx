import { useStore } from 'glassx';
import { twMerge } from 'tailwind-merge';

const screens = [{ name: 'Home' }, { name: 'Create' }, { name: 'Apps' }];

const Nav = () => {
    const [screen, setScreen] = useStore('screen');

    return (
        <nav className="w-full flex justify-between items-center px-5 lg:px-10 py-5 border-b dark:border-blue-200/10 border-gray-200">
            <div className="main-nav flex gap-10">
                {screens.map(({ name }) => (
                    <div
                        key={name}
                        className={twMerge(
                            'flex cursor-pointer gap-2',
                            screen === name ? 'text-[#3eaf7c]' : 'text-[#aaa]'
                        )}
                        onClick={() => setScreen(name)}
                    >
                        <p className="m-0">{name}</p>
                    </div>
                ))}
            </div>

            <img
                src="https://leafphp.dev/logo-circle.png"
                className="w-12 h-12"
                alt="logo"
            />
        </nav>
    );
};

export default Nav;
