<h1>Dashboard</h1>
<p>Halo, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

<form method="POST" action="/logout">
    @csrf
    <button type="submit">Keluar</button>
    <a href="/ganti-password">Ganti password</a>
    <p>{{ session('status') }}</p>
    
</form>