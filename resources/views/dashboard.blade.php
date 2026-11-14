<h1>Dashboard</h1>
<p>Halo, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

<form method="POST" action="/logout">
    @csrf
    <button type="submit">Keluar</button>
</form>