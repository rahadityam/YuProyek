// resources/js/app.js

// (Opsional) Impor bootstrap.js Laravel jika Anda menggunakannya untuk Axios, Echo, dll.
// Jika tidak, Anda bisa menghapus baris ini.
import './bootstrap';

// 1. Impor Alpine.js
import Alpine from 'alpinejs';

// 2. Definisikan Alpine Store untuk sidebar
Alpine.store('sidebar', {
    isCollapsed: localStorage.getItem('sidebarGlobalCollapsed') === 'true', // Default global
    projectId: null, // ID proyek yang sedang aktif di sidebar
    _initializedForProject: null, // Flag internal untuk melacak inisialisasi per proyek

    isInitializedForProject(projectIdToCheck) {
        return this._initializedForProject === projectIdToCheck.toString();
    },

    toggle() {
        this.isCollapsed = !this.isCollapsed;
        if (this.projectId) {
            localStorage.setItem(`sidebarCollapsed_${this.projectId}`, this.isCollapsed.toString());
        }
        localStorage.setItem('sidebarGlobalCollapsed', this.isCollapsed.toString());
        // console.log(`Alpine Store: Sidebar toggled. Collapsed: ${this.isCollapsed}, Project: ${this.projectId || 'Global'}`);
    },

    initProjectState(currentProjectId) {
        const projectIdStr = currentProjectId ? currentProjectId.toString() : null;

        if (!projectIdStr) {
            this.projectId = null;
            this.isCollapsed = localStorage.getItem('sidebarGlobalCollapsed') === 'true';
            this._initializedForProject = 'global_or_none';
            return;
        }

        this.projectId = projectIdStr;
        this._initializedForProject = projectIdStr;

        const projectSpecificState = localStorage.getItem(`sidebarCollapsed_${this.projectId}`);
        if (projectSpecificState !== null) {
            this.isCollapsed = projectSpecificState === 'true';
        } else {
            this.isCollapsed = localStorage.getItem('sidebarGlobalCollapsed') === 'true';
            localStorage.setItem(`sidebarCollapsed_${this.projectId}`, this.isCollapsed.toString());
        }
        // console.log(`Alpine Store: Sidebar state initialized for project ${this.projectId}. Collapsed: ${this.isCollapsed}`);
    }
});

// 3. Definisikan Fungsi Global yang akan dipanggil dari x-init di Blade
window.initializeAlpineSidebarStoreForProject = function(projectId) {
    if (Alpine.store('sidebar') && typeof Alpine.store('sidebar').initProjectState === 'function') {
        if (!Alpine.store('sidebar').isInitializedForProject(projectId)) {
            Alpine.store('sidebar').initProjectState(projectId);
        }
    } else {
        console.warn('Alpine store "sidebar" or "initProjectState" not found when called from x-init. Retrying in 100ms.');
        setTimeout(() => {
            if (Alpine.store('sidebar') && typeof Alpine.store('sidebar').initProjectState === 'function') {
                if (!Alpine.store('sidebar').isInitializedForProject(projectId)) {
                    Alpine.store('sidebar').initProjectState(projectId);
                }
            } else {
                console.error('Alpine store "sidebar" still not available after delay from x-init.');
            }
        }, 100);
    }
};

// 4. Jadikan Alpine global dan Mulai Alpine
window.Alpine = Alpine;
Alpine.start();

// 5. Impor Hotwire Turbo
import * as Turbo from '@hotwired/turbo';

document.addEventListener('turbo:load', function() {
    console.log('Global turbo:load event. Page content updated. Current path:', window.location.pathname);

    if (Alpine.store('sidebar')) {
        const pathSegments = window.location.pathname.split('/');
        let currentEventProjectId = null;
        if (pathSegments.length > 2 && pathSegments[1] === 'projects' && !isNaN(parseInt(pathSegments[2]))) {
            currentEventProjectId = pathSegments[2];
        }
        Alpine.store('sidebar').initProjectState(currentEventProjectId);
    } else {
        console.warn("Alpine store 'sidebar' not available during turbo:load.");
    }
});

// Listener global untuk event dari modal Kanban (DIPASANG SEKALI)
// resources/js/app.js
// ... (Alpine dan Turbo setup tetap sama) ...

// Listener global untuk event dari modal Kanban (DIPASANG SEKALI)
if (!window.globalKanbanEventListenersSetup) {
    console.log('[app.js] Setting up global event listeners (fallback).');

    window.handleGlobalModalTaskFormSuccess = function(event) {
        console.log('[app.js Global Listener FALLBACK] task-form-success received. Detail:', event.detail);
        // Cek jika KanbanApp ada dan siap, dan jika BELUM ditangani langsung
        if (window.KanbanApp && window.KanbanApp.isInitialized && typeof window.KanbanApp.handleTaskFormSuccess === 'function') {
            // Jika ini dipanggil, berarti pemanggilan langsung dari modal gagal atau tidak dilakukan.
            console.warn('[app.js Global Listener FALLBACK] Forwarding task-form-success to KanbanApp. Pemanggilan langsung mungkin gagal.');
            window.KanbanApp.handleTaskFormSuccess(event.detail);
        } else {
            console.warn('[app.js Global Listener FALLBACK] task-form-success received, but window.KanbanApp is not ready or function missing.');
        }
    };
    document.addEventListener('task-form-success', window.handleGlobalModalTaskFormSuccess);

    window.handleGlobalModalShowStatusMessage = function(event) {
        console.log('[app.js Global Listener FALLBACK] show-status-message received:', event.detail);
        if (window.KanbanApp && window.KanbanApp.isInitialized && typeof window.KanbanApp.showStatusMessage === 'function') {
            console.warn('[app.js Global Listener FALLBACK] Forwarding show-status-message to KanbanApp.');
            window.KanbanApp.showStatusMessage(event.detail.message, event.detail.success);
        } else {
            // Fallback paling sederhana jika KanbanApp tidak ada
            console.warn('[app.js Global Listener FALLBACK] KanbanApp not ready for status message. Displaying basic alert.');
            alert((event.detail.success ? "Success: " : "Error: ") + event.detail.message);
        }
    };
    document.addEventListener('show-status-message', window.handleGlobalModalShowStatusMessage);

    window.globalKanbanEventListenersSetup = true;
}