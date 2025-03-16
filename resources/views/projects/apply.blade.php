<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Apply to Project: ') . $project->name }}
            </h2>
            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 bg-gray-300 rounded-md text-gray-800 text-sm font-medium hover:bg-gray-400">
                {{ __('Back to Project') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('projects.apply.store', $project) }}" class="space-y-6">
                        @csrf
                        
                        <!-- Application Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Application Details') }}</h3>
                            
                            <!-- Position -->
                            <div class="mb-4">
                                <x-input-label for="position" :value="__('Position You Are Applying For')" />
                                <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" 
                                    :value="old('position')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('position')" />
                            </div>

                            <!-- Cover Message -->
                            <div class="mb-4">
                                <x-input-label for="message" :value="__('Cover Message (Optional)')" />
                                <textarea id="message" name="message" rows="4" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('message')" />
                            </div>
                        </div>

                        <!-- Personal Data Preview -->
                        <div class="mb-8 border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Personal Data') }}</h3>
                                <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ __('Edit Profile') }}
                                </a>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Profile Photo -->
                                @if ($user->profile_photo_path)
                                <div class="col-span-2 flex justify-center md:justify-start">
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-full h-32 w-32 object-cover">
                                </div>
                                @endif
                                
                                <!-- Name -->
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Name') }}</p>
                                    <p class="font-medium">{{ $user->name }}</p>
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Email') }}</p>
                                    <p class="font-medium">{{ $user->email }}</p>
                                </div>
                                
                                <!-- Phone Number -->
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Phone Number') }}</p>
                                    <p class="font-medium">{{ $user->phone_number ?? 'Not provided' }}</p>
                                </div>
                                
                                <!-- Birth Date -->
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Birth Date') }}</p>
                                    <p class="font-medium">{{ $user->birth_date ? $user->birth_date->format('d F Y') : 'Not provided' }}</p>
                                </div>
                                
                                <!-- Address -->
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">{{ __('Address') }}</p>
                                    <p class="font-medium">{{ $user->address ?? 'Not provided' }}</p>
                                </div>
                                
                                <!-- Description -->
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">{{ __('Description') }}</p>
                                    <p class="font-medium">{{ $user->description ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Education Data Preview -->
                        <div class="mb-8 border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Education') }}</h3>
                                <a href="{{ route('profile.edit') }}#education-container" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ __('Edit Education') }}
                                </a>
                            </div>
                            
                            @if($educations->count() > 0)
                                <div class="space-y-4">
                                    @foreach($educations as $education)
                                    <div class="border rounded-md p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Level') }}</p>
                                                <p class="font-medium">{{ $education->level }}</p>
                                            </div>
                                            
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Institution') }}</p>
                                                <p class="font-medium">{{ $education->institution }}</p>
                                            </div>
                                            
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Major') }}</p>
                                                <p class="font-medium">{{ $education->major }}</p>
                                            </div>
                                            
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Graduation Year') }}</p>
                                                <p class="font-medium">{{ $education->graduation_year }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">{{ __('No education data available.') }}</p>
                            @endif
                        </div>
                        
                        <!-- Documents Preview -->
                        <div class="mb-8 border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Documents') }}</h3>
                                <a href="{{ route('profile.edit') }}#document-section" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ __('Edit Documents') }}
                                </a>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- CV -->
                                <div class="border rounded-md p-4">
                                    <p class="text-sm text-gray-600">{{ __('CV/Resume') }}</p>
                                    @if($cv)
                                        <a href="{{ asset('storage/' . $cv->file_path) }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                            </svg>
                                            {{ __('View CV') }}
                                        </a>
                                    @else
                                        <p class="text-yellow-600 mt-1 italic">{{ __('No CV uploaded. It is recommended to upload a CV before applying.') }}</p>
                                    @endif
                                </div>
                                
                                <!-- Portfolio -->
                                <div class="border rounded-md p-4">
                                    <p class="text-sm text-gray-600">{{ __('Portfolio') }}</p>
                                    @if($portfolio)
                                        <a href="{{ asset('storage/' . $portfolio->file_path) }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                            </svg>
                                            {{ __('View Portfolio') }}
                                        </a>
                                    @else
                                        <p class="text-gray-500 mt-1 italic">{{ __('No portfolio uploaded.') }}</p>
                                    @endif
                                </div>
                                
                                <!-- Certificates -->
                                <div class="border rounded-md p-4">
                                    <p class="text-sm text-gray-600">{{ __('Certificates') }}</p>
                                    @if($certificates->count() > 0)
                                        <div class="mt-2 space-y-2">
                                            @foreach($certificates as $cert)
                                                <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $cert->title }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-500 mt-1 italic">{{ __('No certificates uploaded.') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Confirmation -->
                        <div class="mt-8 pt-4 border-t flex flex-col items-center">
                            <p class="text-gray-700 mb-4 text-center">
                                {{ __('By submitting this application, you confirm that all the information provided is accurate and complete.') }}
                            </p>
                            
                            <x-primary-button>
                                {{ __('Submit Application') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>