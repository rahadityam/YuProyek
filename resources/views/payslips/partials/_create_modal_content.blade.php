{{-- resources/views/payslips/partials/_create_modal_content.blade.php --}}
{{--
    Pastikan semua variabel $modal... ini DI-PASS dengan BENAR dari
    penggajian.calculate.blade.php saat meng-include partial ini.
    Contoh di penggajian.calculate.blade.php:
    @include('payslips.partials._create_modal_content', [
        'project' => $project, // $project dari controller calculate
        'modalWorkers' => $modalWorkers, // dari controller calculate
        'modalPaymentCalculationType' => $modalPaymentCalculationType, // dari controller calculate
        'modalPaymentTerms' => $modalPaymentTerms, // dari controller calculate
        'modalUnpaidTasksGrouped' => $modalUnpaidTasksGrouped, // dari controller calculate
        'modalDefaultTerminName' => $modalDefaultTerminName // dari controller calculate
    ])
--}}
<div x-data="payslipFormModal({
        projectId: {{ $project->id }},
        paymentCalculationType: {{ Js::from($modalPaymentCalculationType ?? 'task') }},
        workers: {{ Js::from($modalWorkers ?? []) }},
        paymentTerms: {{ Js::from($modalPaymentTerms ?? []) }},
        unpaidTasksGrouped: {{ Js::from($modalUnpaidTasksGrouped ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }},
        csrfToken: '{{ csrf_token() }}',
        storePayslipUrl: {{ Js::from(route('projects.payslips.store', $project)) }},
        defaultTerminName: {{ Js::from($modalDefaultTerminName ?? 'Termin 1') }},
        payslipListUrl: {{ Js::from(route('projects.payslips.history', $project)) }}
     })"
     x-show="showCreatePayslipModal"
     x-on:open-create-payslip-modal.window="openModal()"
     x-on:keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[100] overflow-y-auto"
     aria-labelledby="create-payslip-modal-title" role="dialog" aria-modal="true"
     style="display: none;">

    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showCreatePayslipModal"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-700 bg-opacity-75"
             @click="closeModal()" aria-hidden="true" style="display: none;">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>

        <div @click.stop
             x-show="showCreatePayslipModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl"
             style="display: none;">

            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900" id="create-payslip-modal-title">
                    Buat Slip Gaji Baru (Proyek: {{ $project->name }})
                </h3>
            </div>

            <form @submit.prevent="submitForm" x-ref="createPayslipModalForm">
                @csrf
                <div class="px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">
                    <template x-if="Object.keys(formErrors).length > 0">
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <p><strong>Error!</strong> Periksa inputan Anda:</p>
                            <ul class="list-disc list-inside mt-2 text-sm">
                                <template x-for="(messages, field) in formErrors" :key="field">
                                    <template x-for="message in messages" :key="message">
                                        <li x-text="`${getFieldName(field)}: ${message}`"></li>
                                    </template>
                                </template>
                            </ul>
                        </div>
                    </template>
                    <div x-show="generalError" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" x-text="generalError" style="display: none;"></div>

                    <div>
                        <label for="modal_user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                        <select id="modal_user_id" name="user_id" x-model="selectedWorkerId" @change="updateTasksForWorker()" required
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Pilih Pekerja</option>
                            <template x-for="worker in workers" :key="worker.id">
                                 <option :value="worker.id" x-text="worker.name"></option>
                            </template>
                        </select>
                        <template x-if="formErrors.user_id"><span class="text-red-500 text-xs mt-1" x-text="formErrors.user_id[0]"></span></template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Slip Gaji</label>
                        <fieldset class="mt-1">
                            <div class="space-y-2 sm:flex sm:items-center sm:space-y-0 sm:space-x-4">
                                <template x-if="paymentCalculationType === 'task'">
                                    <div class="flex items-center">
                                        <input id="modal_payment_type_task" name="payment_type" type="radio" value="task" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="modal_payment_type_task" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Task</label>
                                    </div>
                                </template>
                                <template x-if="paymentCalculationType === 'termin'">
                                    <div class="flex items-center">
                                        <input id="modal_payment_type_termin" name="payment_type" type="radio" value="termin" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="modal_payment_type_termin" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Termin</label>
                                    </div>
                                </template>
                                <template x-if="paymentCalculationType === 'full'">
                                    <div class="flex items-center">
                                        <input id="modal_payment_type_full" name="payment_type" type="radio" value="full" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="modal_payment_type_full" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Penuh</label>
                                    </div>
                                </template>
                                <div class="flex items-center">
                                    <input id="modal_payment_type_other" name="payment_type" type="radio" value="other" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="modal_payment_type_other" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Bonus / Lainnya</label>
                                </div>
                            </div>
                        </fieldset>
                        <template x-if="formErrors.payment_type"><span class="text-red-500 text-xs mt-1" x-text="formErrors.payment_type[0]"></span></template>
                    </div>

                    <div x-show="paymentCalculationType === 'termin' && payslipType === 'termin'" class="mt-4" style="display: none;">
                        <label for="modal_payment_term_id" class="block text-sm font-medium text-gray-700">Pilih Termin</label>
                        <select id="modal_payment_term_id" name="payment_term_id" x-model="selectedTermId" @change="filterTasksForSelectedTerm()"
                                :required="paymentCalculationType === 'termin' && payslipType === 'termin'"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">-- Pilih Termin --</option>
                            <template x-for="term in paymentTerms" :key="term.id">
                                <option :value="term.id" x-text="`${term.name} (${formatTermDate(term.start_date_formatted)} - ${formatTermDate(term.end_date_formatted)})`"></option>
                            </template>
                        </select>
                        <template x-if="paymentTerms.length === 0 && paymentCalculationType === 'termin' && payslipType === 'termin'">
                            <p class="text-xs text-red-500 mt-1 italic">Belum ada termin yang didefinisikan.</p>
                        </template>
                         <template x-if="formErrors.payment_term_id"><span class="text-red-500 text-xs mt-1" x-text="formErrors.payment_term_id[0]"></span></template>
                    </div>

                    <div x-show="payslipType === 'task' || (payslipType === 'termin' && selectedTermId)" style="display: none;">
                        <div x-show="selectedWorkerId" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tugas yang Dibayar <span x-show="payslipType === 'termin'" style="display: none;">(dalam termin terpilih)</span>
                            </label>
                            <div class="mt-1 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2 bg-gray-50">
                                <template x-if="!isLoadingTasks && availableTasksForWorker.length === 0">
                                     <p class="text-sm text-gray-500 italic text-center py-4">
                                        <span x-show="payslipType === 'task'" style="display: none;">Tidak ada tugas belum dibayar.</span>
                                        <span x-show="payslipType === 'termin'" style="display: none;">Tidak ada tugas belum dibayar dalam termin ini.</span>
                                     </p>
                                </template>
                                <template x-if="isLoadingTasks">
                                    <p class="text-sm text-gray-500 italic text-center py-4 animate-pulse">Memuat task...</p>
                                </template>
                                <template x-for="task in availableTasksForWorker" :key="task.id">
                                    <div class="flex items-start p-2 border border-gray-200 rounded bg-white shadow-sm hover:bg-indigo-50">
                                        <input :id="'modal_task_'+task.id"
       type="checkbox"
       :value="String(task.id)"
       x-model="selectedTaskIds"
       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1 cursor-pointer">
                                        <div class="ml-3 flex-1">
                                            <label :for="'modal_task_'+task.id" class="block text-sm font-medium text-gray-900 cursor-pointer" x-text="task.title"></label>
                                            <div class="text-xs text-gray-500 mt-1 space-x-2">
                                                <span>WSM: <strong x-text="task.wsm_score?.toFixed(2) || 'N/A'"></strong></span> |
                                                <span>Achv: <strong x-text="(task.achievement_percentage ?? 100) + '%'"></strong></span> |
                                                <span>Selesai: <strong x-text="formatDisplayDate(task.finished_date)"></strong></span> |
                                                <span>Nilai: <strong class="text-indigo-700" x-text="formatCurrency(task.calculated_value || 0)"></strong></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <template x-if="formErrors.task_ids"><span class="text-red-500 text-xs mt-1 block" x-text="formErrors.task_ids[0]"></span></template>
                            <template x-if="formErrors['task_ids.*']"><span class="text-red-500 text-xs mt-1 block" x-text="formErrors['task_ids.*'][0]"></span></template>
                        </div>
                        <div x-show="!selectedWorkerId && (payslipType === 'task' || payslipType === 'termin')" class="text-sm text-gray-500 mt-1 italic" style="display: none;">Pilih pekerja.</div>
                    </div>

                    <div>
                        <label for="modal_payment_name" class="block text-sm font-medium text-gray-700">Nama Slip Gaji</label>
                        <input type="text" name="payment_name" id="modal_payment_name" required
                               x-model="paymentName"
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <template x-if="formErrors.payment_name"><span class="text-red-500 text-xs mt-1" x-text="formErrors.payment_name[0]"></span></template>
                    </div>

                    <div>
                        <label for="modal_amount" class="block text-sm font-medium text-gray-700">Nominal Slip Gaji</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">Rp</span></div>
                            <input type="number" name="amount" id="modal_amount"
                                   x-model.number="calculatedAmount"
                                   :disabled="payslipType === 'task' || payslipType === 'termin'"
                                   :readonly="payslipType === 'task' || payslipType === 'termin'"
                                   required min="0" step="1"
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md"
                                   :class="{'bg-gray-100 cursor-not-allowed': payslipType === 'task' || payslipType === 'termin'}"
                                   placeholder="0">
                        </div>
                        <p x-show="payslipType === 'task' || payslipType === 'termin'" class="mt-1 text-xs text-gray-500 italic" style="display: none;">
                            Nominal dihitung otomatis: <strong x-text="formatCurrency(calculatedAmount)"></strong>.
                        </p>
                        <template x-if="formErrors.amount"><span class="text-red-500 text-xs mt-1" x-text="formErrors.amount[0]"></span></template>
                    </div>

                    <div>
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                        <textarea id="modal_notes" name="notes" rows="3" x-model="notes" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                        <template x-if="formErrors.notes"><span class="text-red-500 text-xs mt-1" x-text="formErrors.notes[0]"></span></template>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            :disabled="isSubmitting || !selectedWorkerId || (payslipType === 'task' && selectedTaskIds.length === 0) || (payslipType === 'termin' && (!selectedTermId || selectedTaskIds.length === 0)) || ((payslipType === 'full' || payslipType === 'other') && (!calculatedAmount || calculatedAmount <= 0))"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!isSubmitting">Simpan Draft</span>
                        <span x-show="isSubmitting" class="inline-flex items-center" style="display: none;">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Menyimpan...
                        </span>
                    </button>
                    <button @click="closeModal()" type="button" :disabled="isSubmitting"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
