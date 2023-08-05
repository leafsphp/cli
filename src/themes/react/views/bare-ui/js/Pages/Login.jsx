const Hello = ({ auth, welcome }) => {
    return (
        <div className="flex bg-green-500">
            <h1>Login</h1>
            <p>{welcome}</p>
        </div>
    )
}

export default Hello;
