<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\UserEducation;
use App\Models\UserDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $educations = $user->educations;
        $cv = $user->getCv();
        $portfolio = $user->getPortfolio();
        $certificates = $user->getCertificates();

        return view('profile.edit', [
            'user' => $user,
            'educations' => $educations,
            'cv' => $cv,
            'portfolio' => $portfolio,
            'certificates' => $certificates,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        // Validate main user information
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        }

        // Update user data
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Handle education data
        $this->handleEducationData($request, $user);

        // Handle document uploads
        $this->handleDocumentUploads($request, $user);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Handle education data updates
     */
    private function handleEducationData(Request $request, $user)
    {
        // Delete existing education data if requested
        if ($request->has('education_delete')) {
            foreach ($request->education_delete as $id) {
                UserEducation::where('id', $id)->where('user_id', $user->id)->delete();
            }
        }

        // Update existing education data
        if ($request->has('education_id')) {
            foreach ($request->education_id as $key => $id) {
                if ($id && !empty($request->education_level[$key])) {
                    UserEducation::where('id', $id)->where('user_id', $user->id)->update([
                        'level' => $request->education_level[$key],
                        'institution' => $request->education_institution[$key],
                        'major' => $request->education_major[$key],
                        'graduation_year' => $request->education_year[$key],
                    ]);
                }
            }
        }

        // Add new education data
        if ($request->has('new_education_level')) {
            foreach ($request->new_education_level as $key => $level) {
                if (!empty($level)) {
                    UserEducation::create([
                        'user_id' => $user->id,
                        'level' => $level,
                        'institution' => $request->new_education_institution[$key] ?? '',
                        'major' => $request->new_education_major[$key] ?? '',
                        'graduation_year' => $request->new_education_year[$key] ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Handle document uploads
     */
    private function handleDocumentUploads(Request $request, $user)
    {
        // Handle CV upload
        if ($request->hasFile('cv_file')) {
            $request->validate([
                'cv_file' => 'file|mimes:pdf|max:5120',
            ]);

            $path = $request->file('cv_file')->store('user-documents', 'public');

            // Delete previous CV if exists
            $oldCv = $user->getCv();
            if ($oldCv) {
                Storage::disk('public')->delete($oldCv->file_path);
                $oldCv->delete();
            }

            UserDocument::create([
                'user_id' => $user->id,
                'type' => 'cv',
                'title' => 'CV/Resume',
                'file_path' => $path,
            ]);
        }

        // Handle Portfolio upload
        if ($request->hasFile('portfolio_file')) {
            $request->validate([
                'portfolio_file' => 'file|mimes:pdf|max:10240',
            ]);

            $path = $request->file('portfolio_file')->store('user-documents', 'public');

            // Delete previous portfolio if exists
            $oldPortfolio = $user->getPortfolio();
            if ($oldPortfolio) {
                Storage::disk('public')->delete($oldPortfolio->file_path);
                $oldPortfolio->delete();
            }

            UserDocument::create([
                'user_id' => $user->id,
                'type' => 'portfolio',
                'title' => 'Portfolio',
                'file_path' => $path,
            ]);
        }

        // Handle Certificate uploads
        if ($request->hasFile('certificate_files')) {
            foreach ($request->file('certificate_files') as $key => $file) {
                $request->validate([
                    "certificate_files.$key" => 'file|mimes:pdf|max:5120',
                    "certificate_titles.$key" => 'required|string|max:255',
                ]);

                $path = $file->store('user-documents', 'public');

                UserDocument::create([
                    'user_id' => $user->id,
                    'type' => 'certificate',
                    'title' => $request->certificate_titles[$key] ?? 'Certificate',
                    'file_path' => $path,
                ]);
            }
        }

        // Remove certificates if requested
        if ($request->has('certificate_delete')) {
            foreach ($request->certificate_delete as $id) {
                $certificate = UserDocument::where('id', $id)
                    ->where('user_id', $user->id)
                    ->where('type', 'certificate')
                    ->first();

                if ($certificate) {
                    Storage::disk('public')->delete($certificate->file_path);
                    $certificate->delete();
                }
            }
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete user files from storage
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Delete user documents
        foreach ($user->documents as $document) {
            Storage::disk('public')->delete($document->file_path);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function switchRole(Request $request, $role)
    {
        $user = Auth::user();

        // Validasi role yang diizinkan
        if (!in_array($role, ['worker', 'project_owner'])) {
            return redirect()->back()->with('error', 'Peran tidak valid');
        }

        // Update role pengguna
        $user->role = $role;
        $user->save();

        return redirect()->back()->with('success', 'Peran berhasil diubah menjadi ' . ucfirst($role));
    }
    // app/Http/Controllers/UserController.php

    public function show(User $user)
    {
        $educations = $user->educations;
        $cv = $user->getCv();
        $portfolio = $user->getPortfolio();
        $certificates = $user->getCertificates();

        return view('profile.show', compact('user', 'educations', 'cv', 'portfolio', 'certificates'));
    }
}
