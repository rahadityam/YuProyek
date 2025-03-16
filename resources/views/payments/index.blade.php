<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Pembayaran Proyek: {{ $project->name }}</h2>
                    
                    <!-- Tabs -->
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="mr-2">
                                <a href="#" id="upload-tab" class="inline-block p-4 border-b-2 border-blue-500 rounded-t-lg active" onclick="showTab('upload')">
                                    Upload Pembayaran
                                </a>
                            </li>
                            <li class="mr-2">
                                <a href="#" id="list-tab" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" onclick="showTab('list')">
                                    Daftar Pembayaran
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Upload Form -->
                    <div id="upload-content" class="tab-content">
                        <form method="POST" action="{{ route('projects.storePayment', $project) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            
                            <!-- Pekerja -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                                <select id="user_id" name="user_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Pilih Pekerja</option>
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Nama Pembayaran -->
                            <div>
                                <label for="payment_name" class="block text-sm font-medium text-gray-700">Nama Pembayaran</label>
                                <input type="text" name="payment_name" id="payment_name" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <!-- Nomor Rekening -->
                            <div>
                                <label for="bank_account" class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                                <input type="text" name="bank_account" id="bank_account" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <!-- Nominal -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Nominal</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                                </div>
                            </div>
                            
                            <!-- Bukti Pembayaran -->
                            <div>
                                <label for="proof_image" class="block text-sm font-medium text-gray-700">Bukti Pembayaran</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="proof_image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload file</span>
                                                <input id="proof_image" name="proof_image" type="file" class="sr-only">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, JPEG hingga 2MB</p>
                                    </div>
                                </div>
                                <div id="preview-container" class="mt-3 hidden">
                                    <p class="text-sm font-medium text-gray-700">Preview:</p>
                                    <img id="preview-image" src="#" alt="Preview" class="mt-2 max-h-40 rounded-md">
                                </div>
                            </div>
                            
                            <!-- Catatan -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                                <textarea id="notes" name="notes" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Upload Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Payment List -->
                    <div id="list-content" class="tab-content hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pekerja</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pembayaran</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($payments as $payment)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $payment->created_at->format('d/m/Y') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ $payment->user->name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $payment->payment_name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            Rp {{ number_format($payment->amount, 0, ',', '.') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            @if ($payment->status === 'pending')
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    Pending
                </span>
            @elseif ($payment->status === 'completed')
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Selesai
                </span>
            @else
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    Ditolak
                </span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            @if ($payment->proof_image)
                <a href="{{ Storage::url($payment->proof_image) }}" target="_blank" class="text-blue-600 hover:text-blue-900">Lihat Bukti</a>
            @else
                <span class="text-gray-400">Tidak ada bukti</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex space-x-2">
                <form method="POST" action="{{ route('payments.updateStatus', ['project' => $project->id, 'payment' => $payment->id]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="text-green-600 hover:text-green-900">Selesai</button>
                </form>
                <form method="POST" action="{{ route('payments.updateStatus', ['project' => $project->id, 'payment' => $payment->id]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="text-red-600 hover:text-red-900">Tolak</button>
                </form>
                <form method="POST" action="{{ route('payments.destroy', ['project' => $project->id, 'payment' => $payment->id]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')">Hapus</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
            Belum ada data pembayaran.
        </td>
    </tr>
@endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Function to show tab
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('a[id$="-tab"]').forEach(tab => {
                tab.classList.remove('border-blue-500');
                tab.classList.add('border-transparent');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active class to selected tab
            document.getElementById(tabName + '-tab').classList.remove('border-transparent');
            document.getElementById(tabName + '-tab').classList.add('border-blue-500');
        }
        
        // Image preview functionality
        document.getElementById('proof_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('preview-container').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    @endpush
</x-app-layout>