<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <style>
        /* Modal Styling */
        .smsbox-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            background-color: transparent;
        }

        .smsbox-modal {
            background: #FFF;
            border: 1px solid #CACACA;
            border-radius: 10px;
            padding: 1.5rem;
            width: 100%;
            max-width: 300px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-family: 'Inter', sans-serif;
            font-feature-settings: 'liga' off, 'clig' off;
            font-size: 16px;
            font-weight: 400;
            line-height: 28px;
        }

        .smsbox-title {
            color: var(--Neutral-Colors-Black, #1E1B39);
            font-size: 36px;
            font-weight: 700;
            line-height: 28px;
            margin-bottom: 1rem;
        }

        .smsbox-message {
            color: var(--Neutral-Colors-Black, #1E1B39);
            font-size: 16px;
            font-weight: 400;
            line-height: 28px;
            margin-bottom: 1.5rem;
        }

        .smsbox-button {
            display: flex;
            width: 87px;
            height: 30px;
            padding: 10px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
        }

        .smsbox-confirm-block {
            background: #F44336; /* Merah */
            color: white;
        }

        .smsbox-confirm-unblock {
            background: #4CAF50; /* Hijau */
            color: white;
        }

        .smsbox-cancel {
            border: 1px solid #5F65DB;
            background: white;
            color: #1E1B39;
            border-radius: 10px;
        }

        /* Success Modal Styling */
        .smsbox-success {
            background: #4CAF50; /* Hijau */
            color: white;
        }

        .smsbox-success-button {
            background: #388E3C; /* Hijau Tua */
            color: white;
        }
        .status-badge-active,
    .status-badge-blocked {
        display: flex;
        width: 88.15px;
        height: 29px;
        padding: 10px;
        justify-content: center;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        border-radius: 10px;
        color: white;
        font-size: 0.75rem; /* text-xs */
        font-weight: 600;    /* font-semibold */
    }

    .status-badge-active {
        background: #40E745;
    }

    .status-badge-blocked {
        background: #F05;
    }
    </style>
</head>
<body>
<x-app-layout>
<main class="ml-64 p-6">
     <div class="flex">
        {{-- Sidebar --}}
        @include('admin.users.sidebaradmin')
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">

  {{-- SMS Box Flash Message --}}
@if(session('status'))
    <div id="smsboxModal" class="smsbox-overlay">
        <div class="absolute inset-0 bg-gray-600 bg-opacity-75"></div>

        <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
            <div class="smsbox-modal">
                <div class="flex flex-col items-center justify-center mb-4">
                    <!-- Bootstrap Success Icon -->
                    <i class="bi bi-check-circle-fill text-green-500 text-7xl mb-3"></i>  <!-- Ikon lebih besar -->
                    <h2 class="smsbox-title">Success</h2>
                </div>
                <p class="smsbox-message">
                    {{ session('status') === 'Data successfully blocked' ? 'Data successfully blocked' : 'Data successfully unblocked' }}
                </p>
                <div class="flex justify-center">
                    <button onclick="closeSmsboxModal()" class="smsbox-button" style="border-radius: var(--borderRadius, 4px); background: #5F65DB; color: white; box-shadow: 0px 1px 5px 0px rgba(0, 0, 0, 0.12), 0px 2px 2px 0px rgba(0, 0, 0, 0.14), 0px 3px 1px -2px rgba(0, 0, 0, 0.20);">OK</button>
                </div>
            </div>
        </div>
    </div>
@endif

            {{-- Header --}}
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800" style="color: var(--Neutral-Colors-Black, #1E1B39); font-feature-settings: 'liga' off, 'clig' off; font-family: Inter; font-size: 36px; font-style: normal; font-weight: 700; line-height: 28px;">
                Manage Users
            </h1>
        </div>

        <!-- Garis Bawah -->
        <div class="w-full" style="height: 2px; background-color: #E5E5EF;"></div>

        <!-- Tombol Add Account di sebelah kanan dengan style yang diinginkan -->
                <div class="flex justify-end mt-4 mb-6"> <!-- Menambahkan margin bawah (mb-6) -->
                    <a href="{{ route('admin.users.create') }}" class="text-white font-semibold" style="border-radius: 10px; background: #5F65DB; display: flex; width: 156px; height: 43px; padding: 10px; justify-content: center; align-items: center; gap: 8px; flex-shrink: 0;">
                        Add Account +
                    </a>
                </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full table-auto text-sm rounded-lg">
                    <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="px-4 py-2 border rounded-tl-lg">Name</th>
                                <th class="px-4 py-2 border">Email</th>
                                <th class="px-4 py-2 border">Role</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border rounded-tr-lg">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr class="border-t">
                                    <td class="px-4 py-2 border">{{ $user->name }}</td>
                                    <td class="px-4 py-2 border">{{ $user->email }}</td>
                                    <td class="px-4 py-2 border">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                                 <td class="px-4 py-2 border w-28">
                                    <span class="{{ $user->status === 'active' ? 'status-badge-active' : 'status-badge-blocked' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                    <td class="px-4 py-2 border w-28 text-center">
                                        <button 
                                            onclick="openModal('{{ route('admin.users.toggleStatus', $user->id) }}', {{ $user->status === 'active' ? 'true' : 'false' }})"
                                            title="{{ $user->status === 'active' ? 'Block User' : 'Unblock User' }} "
                                            class="p-2 text-lg text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-full">
                                            @if($user->status === 'active')
                                                <i class="bi bi-lock-fill"></i>
                                            @else
                                                <i class="bi bi-unlock-fill"></i>
                                            @endif
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center px-4 py-6">No users found.</td>
                                </tr>
                            @endforelse

                            {{-- Pagination --}}
                            <tr>
                                <td colspan="5" class="px-4 py-3 bg-white text-sm text-center">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                                        </div>
                                        <div class="flex space-x-1">
                                            @if ($users->onFirstPage())
                                                <span class="px-3 py-1 bg-gray-200 text-gray-500 rounded"><</span>
                                            @else
                                                <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-100"><</a>
                                            @endif

                                            @for ($i = 1; $i <= $users->lastPage(); $i++)
                                                <a href="{{ $users->url($i) }}" class="px-3 py-1 rounded border {{ $users->currentPage() == $i ? 'bg-red-500 text-white' : 'bg-white hover:bg-gray-100' }}">
                                                    {{ $i }}
                                                </a>
                                            @endfor

                                            @if ($users->hasMorePages())
                                                <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-100">></a>
                                            @else
                                                <span class="px-3 py-1 bg-gray-200 text-gray-500 rounded">></span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div id="confirmationModal" class="smsbox-overlay hidden">
        <div class="absolute inset-0 bg-gray-600 bg-opacity-75"></div>

        <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
            <div class="smsbox-modal">
                <h2 id="modalTitle" class="smsbox-title">Confirm</h2>
                <p id="modalMessage" class="smsbox-message">Are you sure?</p>
                <div class="flex justify-between gap-4">
                    <button onclick="closeModal()" class="smsbox-button smsbox-cancel">Cancel</button>
                    <form id="modalForm" method="POST">
                        @csrf
                        <button type="submit" id="modalConfirmBtn" class="smsbox-button">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sukses - User has been blocked successfully -->
    <div id="successModal" class="smsbox-overlay hidden">
        <div class="absolute inset-0 bg-gray-600 bg-opacity-75"></div>

        <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
            <div class="smsbox-modal">
                <h2 id="successModalTitle" class="smsbox-title">Success</h2>
                <p id="successModalMessage" class="smsbox-message">User has been blocked successfully!</p>
                <div class="flex justify-center">
                    <button onclick="closeSuccessModal()" class="smsbox-button smsbox-success-button">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Modal -->
    <script>
        function openModal(actionUrl, isBlock) {
            const modal = document.getElementById('confirmationModal');
            const form = document.getElementById('modalForm');
            const title = document.getElementById('modalTitle');
            const message = document.getElementById('modalMessage');
            const confirmBtn = document.getElementById('modalConfirmBtn');

            form.action = actionUrl;

         if (isBlock) {
            title.textContent = 'Block';
            message.textContent = 'Are you sure you want to block this account?';
            confirmBtn.textContent = 'Block';
            confirmBtn.classList.add('smsbox-confirm-block');
            confirmBtn.classList.remove('smsbox-confirm-unblock');
        } else {
            title.textContent = 'Unblock';
            message.textContent = 'Are you sure you want to unblock this account?';
            confirmBtn.textContent = 'Unblock';
            confirmBtn.classList.add('smsbox-confirm-unblock');
            confirmBtn.classList.remove('smsbox-confirm-block');
        }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            const modal = document.getElementById('confirmationModal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Menutup smsbox modal setelah sesi status
        function closeSmsboxModal() {
            const modal = document.getElementById('smsboxModal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</main>
</x-app-layout>
</body>
</html>
