<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    // Menampilkan halaman penggajian
    public function index(Project $project)
    {
        // Get tasks with status "Done" for the current project, paginated
        $completedTasks = Task::where('project_id', $project->id)
                             ->where('status', 'Done')
                             ->with('assignedUser')  // Eager load user relationship
                             ->orderBy('updated_at', 'desc')
                             ->paginate(10);
        
        return view('penggajian.index', compact('project', 'completedTasks'));
    }

    // Melakukan pembayaran gaji
    public function pay($userId)
    {
        // Logika untuk melakukan pembayaran
    }

    // Menampilkan halaman pembayaran (form upload dan list)
    public function payment(Project $project)
    {
        // Dapatkan semua pekerja dalam proyek
        $workers = $project->workers()->get();
        
        // Dapatkan semua pembayaran untuk proyek ini
        $payments = Payment::where('project_id', $project->id)
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        return view('payments.index', compact('project', 'workers', 'payments'));
    }

    // Menampilkan form upload pembayaran
    public function createPayment(Project $project)
    {
        // Dapatkan semua pekerja dalam proyek
        $workers = $project->workers()->get();
        
        return view('payments.create', compact('project', 'workers'));
    }

    // Menyimpan pembayaran
    public function storePayment(Request $request, Project $project)
    {
        // Validasi input
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);
        
        // Upload bukti pembayaran
        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('payment_proofs', 'public');
            $validated['proof_image'] = $path;
        }
        
        // Tambahkan project_id ke data
        $validated['project_id'] = $project->id;
        
        // Buat pembayaran baru
        Payment::create($validated);
        
        return redirect()->route('projects.pembayaran', $project)
                        ->with('success', 'Bukti pembayaran berhasil diunggah.');
    }

    // Menampilkan daftar pembayaran
    public function listPayments(Project $project)
    {
        $payments = Payment::where('project_id', $project->id)
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        return view('payments.list', compact('project', 'payments'));
    }

    // Mengubah status pembayaran
    public function updateStatus(Request $request, Project $project, Payment $payment)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,completed,rejected',
    ]);

    $payment->update($validated);

    return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui.');
}

    // Menghapus pembayaran
    public function destroy(Payment $payment)
    {
        // Hapus gambar bukti pembayaran jika ada
        if ($payment->proof_image) {
            Storage::disk('public')->delete($payment->proof_image);
        }
        
        $payment->delete();
        
        return redirect()->back()->with('success', 'Pembayaran berhasil dihapus.');
    }
}