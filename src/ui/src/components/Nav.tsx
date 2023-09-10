import { useStore } from 'glassx';
import { Home, Terminal, Layers } from 'react-feather';
import { twMerge } from 'tailwind-merge';

const screens = [
    {
        name: 'Home',
        icon: Home,
    },
    {
        name: 'Create',
        icon: Terminal,
    },
    {
        name: 'Apps',
        icon: Layers,
    },
];

const Nav = () => {
    const [screen, setScreen] = useStore('screen');

    return (
        <nav className="h-full w-[100px] xl:w-[300px] border-r dark:border-blue-200/10 border-gray-200 flex flex-col justify-start items-center xl:items-start xl:px-8 fixed">
            <img
                src="https://leafphp.dev/logo-circle.png"
                className="w-12 h-12 mt-5"
                alt="logo"
            />
            <hr className="border-b dark:border-blue-200/10 border-gray-200 w-1/2 mt-6 mb-8 xl:hidden" />
            <div className="main-nav flex flex-col gap-10 xl:mt-10 xl:pt-10">
                {screens.map(({ name, icon: Icon }) => (
                    <div
                        className={twMerge(
                            'flex cursor-pointer gap-2',
                            screen === name ? 'text-[#3eaf7c]' : 'text-[#aaa]'
                        )}
                        onClick={() => setScreen(name)}
                    >
                        <Icon />
                        <p className="m-0 hidden xl:block">{name}</p>
                    </div>
                ))}
            </div>
        </nav>
    );
};

export default Nav;
