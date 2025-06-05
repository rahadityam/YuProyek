<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Buat Akun Baru</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 font-sans overflow-y-auto">
  <x-app-layout>
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-md mt-4">
      <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Add Account</h1>
      <div class="w-full h-1 bg-gray-200 mb-6"></div>

      {{-- Modal Sukses --}}
      @if(session('success'))
        <div class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center">
          <div class="bg-white rounded-lg shadow-lg max-w-sm w-full text-center p-6">
            <div class="text-green-500 text-5xl mb-3">
              <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="text-xl font-bold mb-2">Succeed</h2>
            <p class="text-gray-700 mb-4">Account Created Successfully</p>
            <button onclick="window.location='{{ route('admin.users.index') }}'" class="px-4 py-2 rounded-[8px]" style="background-color: #5F65DB; color: white; hover:bg-indigo-700;">
              OK
            </button>
          </div>
        </div>
      @endif

      <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="bg-white p-6 rounded-lg shadow-inner">

          {{-- Email --}}
          <label class="block text-sm font-medium text-gray-700 mt-4">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="mt-1 block w-full rounded-md border border-gray-400 shadow-sm p-2 focus:ring focus:ring-indigo-200"
          />
          @error('email')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
          @enderror

          {{-- Username --}}
          <label class="block text-sm font-medium text-gray-700 mt-4">
            Username <span class="text-red-500">*</span>
          </label>
          <input
            type="text"
            name="name"
            value="{{ old('name') }}"
            class="mt-1 block w-full rounded-md border border-gray-400 shadow-sm p-2 focus:ring focus:ring-indigo-200"
          />
          @error('name')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
          @enderror

          {{-- Role --}}
          <label class="block text-sm font-medium text-gray-700 mt-4">
            Role <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <select
              name="role"
              class="appearance-none mt-1 block w-full rounded-md border border-gray-400 shadow-sm p-2 focus:ring focus:ring-indigo-200 pr-10"
            >
              <option value="">-- Pilih Role --</option>
              <option value="admin" {{ old('role')==='admin' ? 'selected' : '' }}>Admin</option>
              <option value="project_owner" {{ old('role')==='project_owner' ? 'selected' : '' }}>Project Manager</option>
              <option value="worker" {{ old('role')==='worker' ? 'selected' : '' }}>Project Worker</option>
            </select>
            <div class="absolute top-1/2 right-3 transform -translate-y-1/2 pointer-events-none">
              <i class="bi bi-chevron-down text-gray-700"></i>
            </div>
          </div>
          @error('role')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
          @enderror

          {{-- Password & Confirmation --}}
          <div class="flex flex-col md:flex-row gap-6 mt-4">

            {{-- Password --}}
            <div class="w-full" x-data="{ show: false }">
              <label class="block text-sm font-medium text-gray-700">
                Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  :type="show ? 'text' : 'password'"
                  name="password"
                  class="mt-1 block w-full rounded-md border border-gray-400 shadow-sm p-2 pr-10 focus:ring focus:ring-indigo-200"
                />
                <button
                  type="button"
                  @click="show = !show"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600"
                >
                  <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              @error('password')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
              @enderror
            </div>

            {{-- Confirmation --}}
            <div class="w-full" x-data="{ show: false }">
              <label class="block text-sm font-medium text-gray-700">
                Confirmation Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  :type="show ? 'text' : 'password'"
                  name="password_confirmation"
                  class="mt-1 block w-full rounded-md border border-gray-400 shadow-sm p-2 pr-10 focus:ring focus:ring-indigo-200"
                />
                <button
                  type="button"
                  @click="show = !show"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600"
                >
                  <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              @error('password_confirmation')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Buttons --}}
          <div class="flex justify-end gap-4 mt-6">
            <a
              href="{{ route('admin.users.index') }}"
              class="px-4 py-2 border border-indigo-600 text-indigo-600 rounded-[8px] hover:bg-indigo-50"
            >
              Cancel
            </a>
            <button
              type="submit"
              class="px-4 py-2 rounded-[8px]"
              style="background-color: #5F65DB; color: white;"
              onmouseenter="this.style.backgroundColor='#4e54b8'"
              onmouseleave="this.style.backgroundColor='#5F65DB'"
            >
              Save
            </button>
          </div>
        </div>
      </form>
    </div>
  </x-app-layout>
</body>
</html>
