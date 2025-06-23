<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<x-app-layout>
    <main class="ml-64 p-6">
        <div class="flex">
            @include('ceo.sidebar_ceo')
            <div class="container mx-auto p-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">List of Users</h1>
                    <div class="w-full mb-4" style="height: 2px; background-color: #E5E5EF;"></div>

                    <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="w-full table-auto text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 border">Name</th>
                                        <th class="px-4 py-2 border">Email</th>
                                        <th class="px-4 py-2 border">Role</th>
                                        <th class="px-4 py-2 border">Status</th>
                                        <th class="px-4 py-2 border">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border">{{ $user->name }}</td>
                                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                                        <td class="px-4 py-2 border">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                                        <td class="px-4 py-2 border text-center">
                                            <span class="{{ $user->status === 'active' ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-1 rounded-full">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 border text-center">
                                            <button onclick="openModal({{ $user->id }})" class="text-blue-600 hover:text-blue-800">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal -->
                                    <div id="modal-{{ $user->id }}" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
                                        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6">
                                            <h2 class="text-xl font-bold mb-4">User Profile</h2>

                                            <!-- Tabs -->
                                            <nav class="flex space-x-4 mb-4">
                                                <button class="tab-btn px-4 py-2 font-medium rounded-md bg-gray-100 text-gray-900"
                                                    onclick="showTab({{ $user->id }}, 'profile')">Personal Data</button>
                                                <button class="tab-btn px-4 py-2 font-medium rounded-md"
                                                    onclick="showTab({{ $user->id }}, 'documents')">Documents</button>
                                            </nav>

                                            <!-- Tab: Personal Data -->
                                            <div id="profile-tab-{{ $user->id }}" class="tab-content">
                                                <div class="space-y-2 text-sm">
                                                    <div><strong>Name:</strong> {{ $user->name }}</div>
                                                    <div><strong>Email:</strong> {{ $user->email }}</div>
                                                    <div><strong>Phone:</strong> {{ $user->phone_number ?: '-' }}</div>
                                                    <div><strong>ID Number:</strong> {{ $user->id_number ?: '-' }}</div>
                                                    <div><strong>Bank Account:</strong> {{ $user->bank_account ?: '-' }}</div>
                                                    <div><strong>Gender:</strong> {{ ucfirst($user->gender ?: '-') }}</div>
                                                    <div><strong>Birth Date:</strong> {{ $user->birth_date ?: '-' }}</div>
                                                    <div><strong>Address:</strong> {{ $user->address ?: '-' }}</div>
                                                    <div><strong>Description:</strong> {{ $user->description ?: '-' }}</div>
                                                    @if ($user->profile_photo_path)
                                                    <div>
                                                        <strong>Photo:</strong><br>
                                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="w-24 h-24 rounded-full object-cover mt-2">
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Tab: Documents -->
                                            <div id="documents-tab-{{ $user->id }}" class="tab-content hidden">
                                                <div class="space-y-2 text-sm">
                                                    @if ($user->cv_path)
                                                    <div><strong>CV:</strong> <a href="{{ asset('storage/' . $user->cv_path) }}" target="_blank" class="text-blue-600 underline">View</a></div>
                                                    @endif
                                                    @if ($user->portfolio_path)
                                                    <div><strong>Portfolio:</strong> <a href="{{ asset('storage/' . $user->portfolio_path) }}" target="_blank" class="text-blue-600 underline">View</a></div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Modal Actions -->
                                            <div class="mt-6 flex justify-end gap-3">
                                                <button onclick="closeModal({{ $user->id }})" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Back</button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    @if ($users->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center px-4 py-6">No users found.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="p-4 border-t flex items-center justify-between">
                                <div>
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                                </div>
                                <div class="flex space-x-1">
                                    {{ $users->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>

<script>
    function openModal(id) {
        document.getElementById(`modal-${id}`).classList.remove('hidden');
        showTab(id, 'profile');
    }

    function closeModal(id) {
        document.getElementById(`modal-${id}`).classList.add('hidden');
    }

    function showTab(id, tab) {
        const profileTab = document.getElementById(`profile-tab-${id}`);
        const documentsTab = document.getElementById(`documents-tab-${id}`);
        const buttons = document.querySelectorAll(`#modal-${id} .tab-btn`);

        profileTab.classList.add('hidden');
        documentsTab.classList.add('hidden');
        buttons.forEach(btn => btn.classList.remove('bg-gray-100', 'text-gray-900'));

        document.getElementById(`${tab}-tab-${id}`).classList.remove('hidden');
        buttons.forEach(btn => {
            if (btn.textContent.includes(tab === 'profile' ? 'Personal' : 'Documents')) {
                btn.classList.add('bg-gray-100', 'text-gray-900');
            }
        });
    }
</script>
</body>
</html>
