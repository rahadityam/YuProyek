<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Projects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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
            font-size: 0.875rem;
            font-weight: 600;
        }
    </style>
</head>

<x-app-layout>
    <main class="ml-64 p-6">
        <div class="flex">
            @include('ceo.sidebar_ceo')
            <div class="container mx-auto p-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">List of Project</h1>
                    <div class="w-full mb-4" style="height: 2px; background-color: #E5E5EF;"></div>
                    <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="w-full table-auto text-sm rounded-lg">
                                <thead class="bg-gray-100 text-left">
                                    <tr>
                                        <th class="px-6 py-4 border-r">Project Name</th>
                                        <th class="px-6 py-4 border-r">Description</th>
                                        <th class="px-6 py-4 border-r">Start Date</th>
                                        <th class="px-6 py-4 border-r">End Date</th>
                                        <th class="px-6 py-4 border-r">Budget</th>
                                        <th class="px-6 py-4 border-r">Status</th>
                                        <th class="px-6 py-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($projects as $project)
                                    <tr class="border-t border-gray-200">
                                        <td class="px-6 py-4 border-r">{{ $project->name }}</td>
                                        <td class="px-6 py-4 border-r">{{ \Illuminate\Support\Str::limit($project->description, 50) }}</td>
                                        <td class="px-6 py-4 border-r">{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4 border-r">{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4 border-r">Rp {{ number_format($project->budget, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 border-r">
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
                                        <td class="px-6 py-4 text-center">
                                            <button type="button" data-project='@json($project)' class="text-blue-600 hover:text-blue-800 show-project-modal">
                                                <i class="bi bi-eye-fill text-xl"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-6 border-t">No projects found.</td>
                                    </tr>
                                    @endforelse

                                    @if ($projects->hasPages())
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 border-t">
                                            {{ $projects->links() }}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div id="project-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white w-full max-w-2xl p-6 rounded shadow-lg relative">
            <button id="close-project-modal" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                <i class="bi bi-x-lg"></i>
            </button>
            <h2 class="text-2xl font-bold mb-4">Project Details</h2>
            <div class="space-y-2">
                <p><strong>Name:</strong> <span id="modal-project-name"></span></p>
                <p><strong>Description:</strong> <span id="modal-project-description"></span></p>
                <p><strong>Start Date:</strong> <span id="modal-project-start"></span></p>
                <p><strong>End Date:</strong> <span id="modal-project-end"></span></p>
                <p><strong>Budget:</strong> <span id="modal-project-budget"></span></p>
                <p><strong>Status:</strong> <span id="modal-project-status" class="inline-block px-3 py-1 rounded text-white text-sm font-semibold"></span></p>
            </div>
            <div class="mt-6 flex justify-between">
                <button id="close-project-modal-btn" class="bg-gray-300 hover:bg-gray-400 text-black font-semibold py-2 px-4 rounded">
                    Back
                </button>
                <a id="manage-project-link" href="kanban" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                    View Kanban Board
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- == SCRIPT YANG BENAR DITAMBAHKAN DI SINI == -->
    <!-- ========================================================== -->
    <script>
        // Menggunakan Event Delegation untuk menangani semua klik secara efisien
        document.addEventListener('click', function(event) {

            // Cek apakah yang diklik adalah tombol untuk MENAMPILKAN modal
            const showButton = event.target.closest('.show-project-modal');
            if (showButton) {
                const project = JSON.parse(showButton.dataset.project);
                const modal = document.getElementById('project-modal');

                document.getElementById('modal-project-name').textContent = project.name;
                document.getElementById('modal-project-description').textContent = project.description;

                const startDate = new Date(project.start_date).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                const endDate = new Date(project.end_date).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                const budget = 'Rp ' + new Intl.NumberFormat('id-ID').format(project.budget);

                document.getElementById('modal-project-start').textContent = startDate;
                document.getElementById('modal-project-end').textContent = endDate;
                document.getElementById('modal-project-budget').textContent = budget;

                const statusMap = {
                    'active': ['#40E745', 'Active'],
                    'blocked': ['#F05', 'Blocked'],
                    'cancelled': ['#F05', 'Cancelled'],
                    'inprogress': ['#F99E26', 'In Progress'],
                    'open': ['#69D3F7', 'Open'],
                    'completed': ['#92D65C', 'Completed']
                };
                const status = project.status.toLowerCase();
                const [bg, label] = statusMap[status] || ['#999', status];
                const statusEl = document.getElementById('modal-project-status');
                statusEl.textContent = label;
                statusEl.style.backgroundColor = bg;

                const manageLink = document.getElementById('manage-project-link');
                manageLink.href = `/ceo/projects/${project.id}/kanban`;

                modal.classList.remove('hidden');
            }

            // Cek apakah yang diklik adalah tombol untuk MENUTUP modal
            const closeButton = event.target.closest('#close-project-modal, #close-project-modal-btn');
            if (closeButton) {
                document.getElementById('project-modal').classList.add('hidden');
            }

        });
    </script>
</x-app-layout>

</html>