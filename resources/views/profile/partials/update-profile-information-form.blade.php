<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="space-y-6">
            <h3 class="text-md font-medium text-gray-700">{{ __('Personal Data') }}</h3>

            <!-- Profile Photo -->
            <div>
                <x-input-label for="profile_photo" :value="__('Profile Photo')" />
                
                @if ($user->profile_photo_path)
                <div class="mt-2 mb-3">
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-full h-20 w-20 object-cover">
                </div>
                @endif
                
                <x-text-input id="profile_photo" name="profile_photo" type="file" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Bank Account -->
            <div>
                <x-input-label for="bank_account" :value="__('Bank Account Number')" />
                <x-text-input id="bank_account" name="bank_account" type="text" class="mt-1 block w-full" :value="old('bank_account', $user->bank_account)" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_account')" />
            </div>

            <!-- Phone Number -->
            <div>
                <x-input-label for="phone_number" :value="__('Phone Number')" />
                <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" />
                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            </div>

            <!-- Birth Date -->
            <div>
                <x-input-label for="birth_date" :value="__('Birth Date')" />
                <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '')" />
                <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
            </div>

            <!-- ID Number (KTP/Passport) -->
            <div>
                <x-input-label for="id_number" :value="__('ID Number (KTP/Passport)')" />
                <x-text-input id="id_number" name="id_number" type="text" class="mt-1 block w-full" :value="old('id_number', $user->id_number)" />
                <x-input-error class="mt-2" :messages="$errors->get('id_number')" />
            </div>

            <!-- Address -->
            <div>
                <x-input-label for="address" :value="__('Address')" />
                <textarea id="address" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $user->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <!-- Gender -->
            <div>
                <x-input-label for="gender" :value="__('Gender')" />
                <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Select Gender') }}</option>
                    <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                    <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>

            <!-- Description -->
            <div>
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $user->description) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>
        </div>

        <!-- Education Data -->
        <div class="space-y-6 mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-md font-medium text-gray-700">{{ __('Education Data') }}</h3>
            
            <div id="education-container">
                <!-- Existing Education Data -->
                @forelse($educations as $index => $education)
                <div class="education-item border rounded-md p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-medium">{{ __('Education') }} #{{ $index + 1 }}</h4>
                        <button type="button" class="text-red-600 hover:text-red-800" 
                                onclick="toggleDeleteEducation(this, {{ $education->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <input type="hidden" name="education_delete[]" class="delete-education" disabled value="{{ $education->id }}">
                    </div>
                    
                    <input type="hidden" name="education_id[]" value="{{ $education->id }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label :for="'education_level_'.$index" :value="__('Education Level')" />
                            <x-text-input :id="'education_level_'.$index" name="education_level[]" type="text" 
                                        class="mt-1 block w-full" :value="old('education_level.'.$index, $education->level)" />
                        </div>
                        
                        <div>
                            <x-input-label :for="'education_institution_'.$index" :value="__('Institution')" />
                            <x-text-input :id="'education_institution_'.$index" name="education_institution[]" type="text" 
                                        class="mt-1 block w-full" :value="old('education_institution.'.$index, $education->institution)" />
                        </div>
                        
                        <div>
                            <x-input-label :for="'education_major_'.$index" :value="__('Major/Program')" />
                            <x-text-input :id="'education_major_'.$index" name="education_major[]" type="text" 
                                        class="mt-1 block w-full" :value="old('education_major.'.$index, $education->major)" />
                        </div>
                        
                        <div>
                            <x-input-label :for="'education_year_'.$index" :value="__('Graduation Year')" />
                            <x-text-input :id="'education_year_'.$index" name="education_year[]" type="number" min="1950" max="2099" 
                                        class="mt-1 block w-full" :value="old('education_year.'.$index, $education->graduation_year)" />
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 italic mb-4">{{ __('No education data added yet.') }}</p>
                @endforelse
                
                <!-- New Education Form Fields (Initially Empty) -->
                <div id="new-education-container"></div>
            </div>
            
            <button type="button" id="add-education" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('+ Add Education') }}
            </button>
        </div>

        <!-- Document Section -->
        <div class="space-y-6 mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-md font-medium text-gray-700">{{ __('Documents') }}</h3>
            
            <!-- CV/Resume -->
            <div>
                <x-input-label for="cv_file" :value="__('CV/Resume (PDF)')" />
                
                @if($cv)
                <div class="mt-2 mb-3 flex items-center">
                    <a href="{{ asset('storage/' . $cv->file_path) }}" target="_blank" rel="noopener noreferrer" 
                       class="text-blue-600 hover:text-blue-800 mr-2">
                        {{ __('View Current CV') }}
                    </a>
                    <span class="text-gray-400 text-sm">{{ __('(Uploading a new file will replace the current one)') }}</span>
                </div>
                @endif
                
                <x-text-input id="cv_file" name="cv_file" type="file" accept=".pdf" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('cv_file')" />
            </div>
            
            <!-- Portfolio -->
            <div>
                <x-input-label for="portfolio_file" :value="__('Portfolio (PDF)')" />
                
                @if($portfolio)
                <div class="mt-2 mb-3 flex items-center">
                    <a href="{{ asset('storage/' . $portfolio->file_path) }}" target="_blank" rel="noopener noreferrer" 
                       class="text-blue-600 hover:text-blue-800 mr-2">
                        {{ __('View Current Portfolio') }}
                    </a>
                    <span class="text-gray-400 text-sm">{{ __('(Uploading a new file will replace the current one)') }}</span>
                </div>
                @endif
                
                <x-text-input id="portfolio_file" name="portfolio_file" type="file" accept=".pdf" class="mt-1 block w-full" />
                <x-input-error class="mt-2" :messages="$errors->get('portfolio_file')" />
            </div>
            
            <!-- Certificates -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <x-input-label :value="__('Certificates')" />
                </div>
                
                @if(count($certificates) > 0)
                <div class="mb-4">
                    <h5 class="text-sm font-medium text-gray-700 mb-2">{{ __('Current Certificates:') }}</h5>
                    <div class="space-y-2">
                        @foreach($certificates as $cert)
                        <div class="flex items-center justify-between p-2 border rounded-md">
                            <div class="flex items-center">
                                <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" rel="noopener noreferrer" 
                                   class="text-blue-600 hover:text-blue-800">
                                    {{ $cert->title }}
                                </a>
                                <span class="text-xs text-gray-500 ml-2">
                                    ({{ \Carbon\Carbon::parse($cert->created_at)->format('d M Y') }})
                                </span>
                            </div>
                            <div class="flex items-center">
                                <button type="button" class="text-red-600 hover:text-red-800" 
                                        onclick="toggleDeleteCertificate(this, {{ $cert->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input type="hidden" name="certificate_delete[]" class="delete-certificate" disabled value="{{ $cert->id }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <div id="certificate-container">
                    <div class="certificate-item border rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="certificate_titles_0" :value="__('Certificate Title')" />
                                <x-text-input id="certificate_titles_0" name="certificate_titles[]" type="text" 
                                            class="mt-1 block w-full" :value="old('certificate_titles.0')" />
                            </div>
                            
                            <div>
                                <x-input-label for="certificate_files_0" :value="__('Certificate File (PDF)')" />
                                <x-text-input id="certificate_files_0" name="certificate_files[]" type="file" accept=".pdf" class="mt-1 block w-full" />
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="add-certificate" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('+ Add Certificate') }}
                </button>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<!-- JavaScript for dynamic form fields -->
<script>
    // For Education Fields
    document.addEventListener('DOMContentLoaded', function() {
        let educationCounter = 0;
        
        // Add Education Button
        document.getElementById('add-education').addEventListener('click', function() {
            const container = document.getElementById('new-education-container');
            educationCounter++;
            
            const wrapper = document.createElement('div');
            wrapper.className = 'education-item border rounded-md p-4 mb-4';
            wrapper.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-medium">New Education</h4>
                    <button type="button" class="text-red-600 hover:text-red-800 remove-education">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700" for="new_education_level_${educationCounter}">
                            Education Level
                        </label>
                        <input id="new_education_level_${educationCounter}" name="new_education_level[]" type="text" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block font-medium text-sm text-gray-700" for="new_education_institution_${educationCounter}">
                            Institution
                        </label>
                        <input id="new_education_institution_${educationCounter}" name="new_education_institution[]" type="text" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block font-medium text-sm text-gray-700" for="new_education_major_${educationCounter}">
                            Major/Program
                        </label>
                        <input id="new_education_major_${educationCounter}" name="new_education_major[]" type="text" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block font-medium text-sm text-gray-700" for="new_education_year_${educationCounter}">
                            Graduation Year
                        </label>
                        <input id="new_education_year_${educationCounter}" name="new_education_year[]" type="number" min="1950" max="2099" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            `;
            
            container.appendChild(wrapper);
            
            // Add event listener to the new remove button
            wrapper.querySelector('.remove-education').addEventListener('click', function() {
                wrapper.remove();
            });
        });
        
        // Toggle delete for existing education
        window.toggleDeleteEducation = function(button, id) {
            const input = button.nextElementSibling;
            const educationItem = button.closest('.education-item');
            
            if (input.disabled) {
                input.disabled = false;
                educationItem.classList.add('bg-red-50');
                button.innerHTML = 'Cancel';
            } else {
                input.disabled = true;
                educationItem.classList.remove('bg-red-50');
                button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>`;
            }
        };
        
        // For Certificate Fields
        let certificateCounter = 1;
        
        // Add Certificate Button
        document.getElementById('add-certificate').addEventListener('click', function() {
            const container = document.getElementById('certificate-container');
            
            const wrapper = document.createElement('div');
            wrapper.className = 'certificate-item border rounded-md p-4 mb-4';
            wrapper.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <h5 class="text-sm font-medium">Additional Certificate</h5>
                    <button type="button" class="text-red-600 hover:text-red-800 remove-certificate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700" for="certificate_titles_${certificateCounter}">
                            Certificate Title
                        </label>
                        <input id="certificate_titles_${certificateCounter}" name="certificate_titles[]" type="text" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block font-medium text-sm text-gray-700" for="certificate_files_${certificateCounter}">
                            Certificate File (PDF)
                        </label>
                        <input id="certificate_files_${certificateCounter}" name="certificate_files[]" type="file" accept=".pdf"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            `;
            
            container.appendChild(wrapper);
            certificateCounter++;
            
            // Add event listener to the new remove button
            wrapper.querySelector('.remove-certificate').addEventListener('click', function() {
                wrapper.remove();
            });
        });
        
        // Toggle delete for existing certificates
        window.toggleDeleteCertificate = function(button, id) {
            const input = button.nextElementSibling;
            const certItem = button.closest('.flex.items-center.justify-between');
            
            if (input.disabled) {
                input.disabled = false;
                certItem.classList.add('bg-red-50');
                button.innerHTML = 'Cancel';
            } else {
                input.disabled = true;
                certItem.classList.remove('bg-red-50');
                button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>`;
            }
        };
    });
</script>