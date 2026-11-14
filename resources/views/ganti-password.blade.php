<h1>Ganti Password</h1>

<form method="POST" action="/ganti-password">
    @csrf
    <p>
        <label>Password lama</label><br>
        <input type="password" name="password_lama">
        @error('password_lama') <span style="color:red">{{ $message }}</span> @enderror
    </p>
    <p>
        <label>Password baru</label><br>
        <input type="password" name="password_baru">
        @error('password_baru') <span style="color:red">{{ $message }}</span> @enderror
    </p>
    <p>
        <label>Ulangi password baru</label><br>
        <input type="password" name="password_baru_confirmation">
    </p>
    <button type="submit">Simpan</button>
</form>