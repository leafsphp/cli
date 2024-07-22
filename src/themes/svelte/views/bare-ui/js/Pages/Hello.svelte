<script>
    export let auth;

    let count = 0;

    const increment = () => {
        count++;
    };
</script>

<div>
    <h1>Hello World!</h1>
    <p>Current user: {auth?.user?.name ?? "No auth is active"}</p>
    <p>{count}</p>
    <button on:click={increment} class="bg-green-900">Increment</button>
</div>
