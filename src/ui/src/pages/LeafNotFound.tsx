const LeafNotFound = () => {
    return (
        <div className="flex w-screen h-screen justify-center items-center">
            <div className="flex flex-col justify-center items-center">
                <img
                    src="https://leafphp.dev/logo-circle.png"
                    className="w-16 h-16 mr-4"
                    alt="logo"
                />
                <p className="text-gray-300 font-bold mt-2 text-lg w-2/3 text-center">
                    This application does not appear to be using Leaf
                </p>
            </div>
        </div>
    );
};

export default LeafNotFound;
