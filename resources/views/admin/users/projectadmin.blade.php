<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Proyek</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* SMSBOX & MODAL STYLES - RINGKASAN */
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
            max-width: 300px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-family: 'Inter', sans-serif;
        }

        .smsbox-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .smsbox-message {
            font-size: 16px;
            margin-bottom: 1.5rem;
        }

        .smsbox-button {
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .smsbox-confirm-block {
            background: #F44336; color: white;
        }

        .smsbox-confirm-unblock {
            background: #4CAF50; color: white;
        }

        .smsbox-cancel {
            border: 1px solid #5F65DB;
            background: white;
            color: #1E1B39;
        }

    
        .status-badge {
    display: flex;
    width: 85.092px;
    height: 29px;
    padding: 10px;
    justify-content: center;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    border-radius: 10px;
    color: white;
    font-size: 0.875rem; /* text-sm */
    font-weight: 600; /* font-semibold */
}

    </style>
</head>
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

<body class="bg-gray-100">

<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Manage Project</h1>
        </div>
        <div class="w-full mb-4" style="height: 2px; background-color: #E5E5EF;"></div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full table-auto text-sm rounded-lg">
                    <thead class="bg-gray-100 text-left">
        <tr>
            <th class="px-6 py-4 text-left border-r">Project Name</th>
            <th class="px-6 py-4 text-left border-r">Project Description</th>
            <th class="px-6 py-4 text-left border-r">Start Date</th>
            <th class="px-6 py-4 text-left border-r">End Date</th>
            <th class="px-6 py-4 text-left border-r">Budget</th>
            <th class="px-6 py-4 text-left border-r">Status</th>
            <th class="px-6 py-4 text-left">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projects as $project)
        <tr class="border-t border-gray-200">
            <td class="px-6 py-4 border-r">{{ $project->name }}</td>
            <td class="px-6 py-4 border-r">{{ $project->description }}</td>
            <td class="px-6 py-4 border-r">{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</td>
            <td class="px-6 py-4 border-r">{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</td>
            <td class="px-6 py-4 border-r">{{ $project->budget }}</td>
           <td class="px-4 py-2 border w-28">
                @php
                    $status = strtolower($project->status);
                    $map = [
                        'active' => ['#40E745', 'Active'],
                        'blocked' => ['#F05', 'Blocked'],
                        'cancelled' => ['#F05', 'Cancelled'],
                        'inprogress' => ['#F99E26', 'In Progress'],
                        'open' => ['#69D3F7', 'Open'],
                        'completed' => ['#92D65C', 'Completed'],
                    ];
                    $info = $map[$status] ?? ['#999', ucfirst($status)];
                @endphp
                <span class="status-badge" style="background-color: {{ $info[0] }};">
                    {{ $info[1] }}
                </span>
            </td>
            <td class="px-4 py-2 border w-28 text-center">
                <button type="button"
                 onclick="openModal('{{ route('admin.projects.toggleStatus', $project->id) }}', {{ $project->status !== 'blocked' ? 'true' : 'false' }})"
                    class="text-black hover:text-gray-700">
                    @if($project->status === 'blocked')
                        <i class="bi bi-unlock-fill"></i>
                    @else
                        <i class="bi bi-lock-fill"></i>
                    @endif
                </button>
            </td>
        </tr>
        @endforeach

        {{-- Pagination --}}
        <tr>
            <td colspan="7" class="px-4 py-3 text-sm border-t">
                <div class="flex justify-between items-center">
                    <div>
                        Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} entries
                    </div>
                    <div class="flex space-x-1">
                        {{-- Previous --}}
                        @if ($projects->onFirstPage())
                            <span class="px-3 py-1 bg-gray-200 text-gray-500 rounded"><</span>
                        @else
                            <a href="{{ $projects->previousPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-100"><</a>
                        @endif

                        {{-- Pages --}}
                        @for ($i = 1; $i <= $projects->lastPage(); $i++)
                            <a href="{{ $projects->url($i) }}" class="px-3 py-1 border rounded {{ $projects->currentPage() == $i ? 'bg-red-500 text-white' : 'bg-white hover:bg-gray-100' }}">{{ $i }}</a>
                        @endfor

                        {{-- Next --}}
                        @if ($projects->hasMorePages())
                            <a href="{{ $projects->nextPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-100">></a>
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

    <!-- Tambahkan atribut data-success -->
<div id="successModal" class="smsbox-overlay hidden" data-success="{{ session('success') ? 'true' : 'false' }}">
    <div class="absolute inset-0 bg-gray-600 bg-opacity-75"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
        <div class="smsbox-modal">
            <i class="bi bi-check-circle-fill text-green-500 text-5xl mb-3"></i>
            <h2 id="successModalTitle" class="smsbox-title">Success</h2>
            <p id="successModalMessage" class="smsbox-message">
                {{ session('success') }}
            </p>
            <div class="flex justify-center">
                <button onclick="closeSuccessModal()" class="smsbox-button smsbox-success-button">OK</button>
            </div>
        </div>
    </div>
</div>


    {{-- Tampilkan modal sukses jika ada session success --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('successModal');
                const message = document.getElementById('successModalMessage');
                message.textContent = @json(session('success'));
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });
        </script>
    @endif

</div>
<script>
    // Modal Blokir / Unblokir
    function openModal(actionUrl, isBlock) {
        const modal = document.getElementById('confirmationModal');
        const form = document.getElementById('modalForm');
        const title = document.getElementById('modalTitle');
        const message = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');

        form.action = actionUrl;

        if (isBlock) {
            title.textContent = 'Blokir';
            message.textContent = 'Apakah Anda yakin ingin memblokir proyek ini?';
            confirmBtn.textContent = 'Blokir';
            confirmBtn.classList.add('smsbox-confirm-block');
            confirmBtn.classList.remove('smsbox-confirm-unblock');
        } else {
            title.textContent = 'Unblokir';
            message.textContent = 'Apakah Anda yakin ingin membuka blokir proyek ini?';
            confirmBtn.textContent = 'Unblokir';
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

    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }


</script>

</body>
</main>
</x-app-layout>
</html>
