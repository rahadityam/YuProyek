<x-app-layout>
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