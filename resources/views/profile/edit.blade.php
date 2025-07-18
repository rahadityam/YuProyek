<x-app-layout>
    <div x-data="{ 
            show: {{ session()->has('success') || (isset($errors) && $errors->any()) ? 'true' : 'false' }}, 
            isSuccess: {{ session()->has('success') ? 'true' : 'false' }},
            message: '{{ session('success') ?: ((isset($errors) && $errors->any()) ? 'Failed to update profile. Please check the form.' : '') }}' 
        }"
         x-init="
            if (show) {
                setTimeout(() => show = false, 4000); // Sembunyikan setelah 4 detik
            }
         "
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-20 right-5 z-[100] w-full max-w-sm rounded-md shadow-lg"
         style="display: none;">
        
        <div class="rounded-md p-4" :class="isSuccess ? 'bg-green-500' : 'bg-red-500'">
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Ikon Sukses -->
                    <svg x-show="isSuccess" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <!-- Ikon Gagal -->
                    <svg x-show="!isSuccess" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white" x-text="message"></p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="show = false" type="button" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2" :class="isSuccess ? 'bg-green-500 text-green-50 hover:bg-green-600 focus:ring-offset-green-50 focus:ring-green-600' : 'bg-red-500 text-red-50 hover:bg-red-600 focus:ring-offset-red-50 focus:ring-red-600'">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Navigation Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <nav class="flex space-x-4">
                        <button class="tab-btn active px-4 py-2 font-medium rounded-md" data-target="profile-section">
                            {{ __('Personal Data') }}
                        </button>
                        <button class="tab-btn px-4 py-2 font-medium rounded-md" data-target="password-section">
                            {{ __('Password') }}
                        </button>
                        <button class="tab-btn px-4 py-2 font-medium rounded-md" data-target="delete-section">
                            {{ __('Delete Account') }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Profile Info Section -->
            <div id="profile-section" class="tab-content p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Password Section -->
            <div id="password-section" class="tab-content p-4 sm:p-8 bg-white shadow sm:rounded-lg hidden">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account Section -->
            <div id="delete-section" class="tab-content p-4 sm:p-8 bg-white shadow sm:rounded-lg hidden">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Functionality JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Reset all buttons and hide all content sections
                    tabButtons.forEach(btn => btn.classList.remove('active', 'bg-gray-100', 'text-gray-900'));
                    tabContents.forEach(content => content.classList.add('hidden'));
                    
                    // Activate clicked button and show corresponding content
                    button.classList.add('active', 'bg-gray-100', 'text-gray-900');
                    
                    const targetId = button.getAttribute('data-target');
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });
        });
    </script>
</x-app-layout>