<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RS Mitra Husada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .text-navy { color: #1A237E; }
        .gradient-button {
            background: linear-gradient(90deg, #1A237E 0%, #3949AB 100%);
        }
        select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
    </style>
</head>
<body class="bg-white overflow-hidden">
    <div class="flex h-screen">
        
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-20 bg-white">
            <div class="w-full max-w-md">
                
                <div class="mb-10 flex items-center gap-3">
                    <div class="bg-[#1A237E] text-white p-2 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-navy leading-none">RS MITRA HUSADA</h2>
                        <p class="text-[10px] tracking-widest text-gray-500 uppercase">Melayani Dengan Hati</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-navy mb-2">Selamat Datang !!!</h1>
                    <p class="text-gray-400">Silakan login untuk melanjutkan</p>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-xl">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Email atau Username</label>
                        <input type="text" name="email" required 
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white outline-none transition duration-200"
                            placeholder="Masukkan email atau username" value="{{ old('email') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Password</label>
                        <input type="password" name="password" required 
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white outline-none transition duration-200"
                            placeholder="Masukkan password">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-widest">Pilih Role</label>
                        <select name="role" required 
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white outline-none transition duration-200 cursor-pointer">
                            <option value="" disabled selected>Pilih role anda</option>
                            <option value="staff">Staff</option>
                            <option value="manager">Manajer</option>
                            <option value="nurse">Perawat</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <label class="flex items-center text-sm text-gray-700 font-medium cursor-pointer">
                            <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4"> 
                            Ingat saya
                        </label>
                        <a href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition">Lupa Password?</a>
                    </div>

                    <button type="submit" class="gradient-button w-full text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-blue-200/50 hover:opacity-95 transform active:scale-[0.98] transition duration-200">
                        Login
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-500">Belum punya akun ? <a href="#" class="text-blue-700 font-bold hover:underline">Hubungi Administrator</a></p>
                </div>

            </div>
        </div>

        <div class="hidden lg:flex lg:w-1/2 bg-[#E8F0FE] items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-[#E8F0FE]"></div>
            <div class="relative z-10 text-center">
                <h1 class="text-5xl font-black text-navy mb-4 leading-tight">Hospital Asset<br>Monitoring System</h1>
                <p class="text-xl text-gray-600 font-medium">Sistem terintegrasi untuk Pengelolaan,<br>Pelacakan, dan Monitoring Aset Rumah Sakit</p>
            </div>
        </div>

    </div>
</body>
</html>