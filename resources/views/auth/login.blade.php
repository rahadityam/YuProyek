<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YuProyek - Internal Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<!-- Body dibuat untuk meletakkan konten di tengah layar dengan background netral -->
<body class="bg-slate-100 h-screen flex items-center justify-center p-6">

    <!-- Kartu Login Utama -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">

        <!-- Logo Perusahaan -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo_yuproyek-cropped.svg') }}" alt="YuProyek Logo" class="h-12 w-auto">
        </div>

        <!-- Judul Form -->
        <div class="text-center mb-8">
            <p class="text-gray-500 mt-1">Sign in to continue to your account.</p>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            @if (session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Input Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    placeholder="your email"
                    class="block w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                @error('email')
                    <p class="text-red-600 mt-1 text-xs">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required
                        placeholder="••••••••"
                        class="block w-full rounded-lg border border-gray-300 px-4 py-3 pr-12 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-gray-700">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-600 mt-1 text-xs">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Submit -->
            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Sign In
                </button>
            </div>
        </form>

    </div>

    <!-- Script Toggle Password (Tidak berubah, tapi saya perbaiki posisi tombol di HTML) -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>

</body>
</html>