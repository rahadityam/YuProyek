<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YuProyek - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-white h-screen overflow-hidden">

    <!-- Navbar -->
    <nav class="flex justify-between items-center px-10 py-4 border-b shadow-md bg-[#EFF1F9]">
        <div class="flex items-center">
            <img src="{{ asset('images/logo_yuproyek-cropped.svg') }}" alt="YuProyek Logo" class="h-10 w-auto mr-2">
        </div>
        <div class="flex gap-6 items-center">
            <a href="{{ route('login') }}" class="bg-indigo-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-indigo-600">Login</a>
            <a href="{{ route('register') }}" class="bg-indigo-500 text-white px-4 py-2 rounded-md font-semibold hover:bg-indigo-600">Register</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex h-[calc(110vh-0px)] justify-center items-center px-6 md:px-30">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-7xl w-full items-start">

            <!-- Left Content -->
            <div class="flex flex-col justify-center">
                <h2 class="text-4xl font-bold text-indigo-600 mb-4">Welcome to</h2>
                <div class="flex items-center mb-6">
                    <img src="{{ asset('images/logo_yuproyek-cropped.svg') }}" alt="YuProyek Logo" class="h-12 mr-3">
                </div>
                <p class="text-gray-600 mb-2">Here, we believe that building a strong professional network begins with your participation.</p>
                <p class="text-gray-600 mb-6">We are delighted to offer a modern and user-friendly service to ensure you have the best experience.</p>
                <a href="#" class="text-indigo-600 font-bold hover:underline">Join Now!</a>

              <div class="relative">
                <img src="{{ asset('images/image 30.svg') }}" alt="Illustration" class="w-[400px] h-[400px] translate-y-[-40px]">
            </div>
            </div>

            <!-- Login Form -->
            <div class="flex justify-end items-start">
                <form method="POST" action="{{ route('login') }}" class="flex flex-col w-full max-w-[400px] items-start gap-3">
                    @csrf

                    <h2 class="text-3xl font-bold mb-4 text-gray-700 text-left">Sign in</h2>

                    @if (session('status'))
                        <div class="mb-3 text-green-600">{{ session('status') }}</div>
                    @endif

                    <!-- Email -->
                    <div class="w-full">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            placeholder="Enter Email"
                            class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        @error('email')
                            <div class="text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="w-full relative">
                        <input id="password" name="password" type="password" required
                            placeholder="Password"
                            class="block w-full rounded-xl border border-gray-300 px-4 py-3 pr-12 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">

                        <button type="button" onclick="togglePassword()" 
                        class="absolute top-3 right-3 text-gray-400 text-2xl">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>


                        @error('password')
                            <div class="text-red-600 mt-1">{{ $message }}</div>
                        @enderror

                        <div class="mt-1 text-right w-full">
                            <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">
                                Recover Password ?
                            </a>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="w-full">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="w-full">
                        <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition">
                            Sign In
                        </button>
                    </div>

                    <!-- Register link -->
                    <div class="w-full text-center">
                        <span class="text-sm text-gray-600">Don't have an account?</span>
                        <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:underline">Register</a>
                    </div>

                    <!-- Divider -->
                    <div class="w-full flex items-center justify-center">
                        <span class="text-sm text-gray-400">Or Continue with</span>
                    </div>

                    <!-- Google Button -->
                    <div class="w-full flex items-center justify-center">
                    <button type="button" class="p-3 border rounded-full shadow hover:bg-gray-100 transition text-2xl text-red-500">
                        <i class="bi bi-google"></i>
                    </button>
                </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Toggle Password Script -->
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
