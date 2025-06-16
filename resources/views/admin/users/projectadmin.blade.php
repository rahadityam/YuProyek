<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .smsbox-overlay { position: fixed; inset: 0; z-index: 50; background-color: transparent; }
        .smsbox-modal { background: #FFF; border: 1px solid #CACACA; border-radius: 10px; padding: 1.5rem; max-width: 300px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; font-family: 'Inter', sans-serif; }
        .smsbox-title { font-size: 36px; font-weight: 700; margin-bottom: 1rem; }
        .smsbox-message { font-size: 16px; margin-bottom: 1.5rem; }
        .smsbox-button { padding: 8px 12px; border-radius: 5px; font-size: 14px; font-weight: 500; }
        .smsbox-confirm-block { background: #F44336; color:white; }
        .smsbox-confirm-unblock { background: #4CAF50; color:white; }
        .smsbox-cancel { border: 1px solid #5F65DB; background: white; color: #1E1B39; }
        .status-badge { display: flex; width: 85px; height: 29px; padding: 10px; justify-content: center; align-items: center;
            border-radius: 10px; color: white; font-size: 0.875rem; font-weight: 600; }

        /* Tambahan pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        .pagination-info {
            font-size: 0.875rem;
            color: #4B5563;
        }
        .pagination-links a, .pagination-links span {
            margin: 0 0.125rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #D1D5DB;
            font-size: 0.875rem;
        }
        .pagination-links a:hover {
            background-color: #F3F4F6;
        }
        .pagination-links .active {
            background-color: #EF4444;
            color: white;
            border-color: #EF4444;
        }
        .pagination-links .disabled {
            background-color: #E5E7EB;
            color: #9CA3AF;
        }
    </style>
</head>

<x-app-layout>
<main class="ml-64 p-6">
<div class="flex">
    @include('admin.users.sidebaradmin')

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Manage Project</h1>
            <div class="w-full mb-4" style="height: 2px; background-color: #E5E5EF;"></div>

            <div class="overflow-x-auto bg-white rounded-lg shadow-lg border">
                <table class="w-full table-auto text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-6 py-4 border-r w-56">Project Name</th>
                            <th class="px-6 py-4 border-r w-56">Project Manager</th>
                            <th class="px-6 py-4 border-r w-80">Description</th>
                            <th class="px-6 py-4 border-r w-48">Start Date</th>
                            <th class="px-6 py-4 border-r w-48">End Date</th>
                            <th class="px-6 py-4 border-r w-40">Budget</th>
                            <th class="px-6 py-4 border-r w-28">Status</th>
                            <th class="px-6 py-4 w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        <tr class="border-t border-gray-200">
                            <td class="px-6 py-4 border-r">{{ $project->name }}</td>
                            <td class="px-6 py-4 border-r">{{ $project->owner->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 border-r">{{ $project->description }}</td>
                            <td class="px-6 py-4 border-r">{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 border-r">{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 border-r">Rp {{ number_format($project->budget, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 border-r">
                                @php
                                    $map = [
                                        'active' => ['#40E745', 'Active'],
                                        'blocked' => ['#F05', 'Blocked'],
                                        'cancelled' => ['#F05', 'Cancelled'],
                                        'inprogress' => ['#F99E26', 'In Progress'],
                                        'open' => ['#69D3F7', 'Open'],
                                        'completed' => ['#92D65C', 'Completed'],
                                    ];
                                    $status = strtolower($project->status);
                                    $info = $map[$status] ?? ['#999', ucfirst($status)];
                                @endphp
                                <span class="status-badge" style="background-color: {{ $info[0] }};">
                                    {{ $info[1] }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
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
    <td colspan="8" class="px-4 py-3 text-sm border-t">
                <div class="flex justify-between items-center">
                    <div>
                Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} entries
            </div>
            <div class="flex items-center space-x-1">
                @if ($projects->onFirstPage())
                    <span class="px-3 py-1 bg-gray-200 text-gray-500 rounded"><</span>
                @else
                    <a href="{{ $projects->previousPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-100"><</a>
                @endif

                @for ($i = 1; $i <= $projects->lastPage(); $i++)
                    <a href="{{ $projects->url($i) }}" class="px-3 py-1 rounded border {{ $projects->currentPage() == $i ? 'bg-red-500 text-white' : 'bg-white hover:bg-gray-100' }}">
                        {{ $i }}
                    </a>
                @endfor

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

            <!-- Modal Sukses -->
            <div id="successModal" class="smsbox-overlay {{ session('success') ? '' : 'hidden' }}">
                <div class="absolute inset-0 bg-gray-600 bg-opacity-75"></div>
                <div class="relative z-10 flex items-center justify-center min-h-screen px-4">
                    <div class="smsbox-modal">
                        <i class="bi bi-check-circle-fill text-green-500 text-7xl mb-3"></i>
                        <h2 class="smsbox-title">Success</h2>
                        <p class="smsbox-message">{{ session('success') }}</p>
                        <div class="flex justify-center">
                            <button onclick="closeSuccessModal()" class="smsbox-button" style="background:#5F65DB; color:white;">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function openModal(actionUrl, isBlock) {
                    const modal = document.getElementById('confirmationModal');
                    const form = document.getElementById('modalForm');
                    const title = document.getElementById('modalTitle');
                    const message = document.getElementById('modalMessage');
                    const confirmBtn = document.getElementById('modalConfirmBtn');

                    form.action = actionUrl;

                    if (isBlock) {
                        title.textContent = 'Block Project';
                        message.textContent = 'Are you sure to block this project?';
                        confirmBtn.textContent = 'Block';
                        confirmBtn.classList.add('smsbox-confirm-block');
                        confirmBtn.classList.remove('smsbox-confirm-unblock');
                    } else {
                        title.textContent = 'Unblock Project';
                        message.textContent = 'Are you sure to unblock this project?';
                        confirmBtn.textContent = 'Unblock';
                        confirmBtn.classList.add('smsbox-confirm-unblock');
                        confirmBtn.classList.remove('smsbox-confirm-block');
                    }

                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal() {
                    document.getElementById('confirmationModal').classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }

                function closeSuccessModal() {
                    document.getElementById('successModal').classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }

                window.addEventListener('load', function () {
                    if (!document.getElementById('successModal').classList.contains('hidden')) {
                        document.body.classList.add('overflow-hidden');
                    }
                });
            </script>
        </div>
    </div>
</div>
</main>
</x-app-layout>
</html>
