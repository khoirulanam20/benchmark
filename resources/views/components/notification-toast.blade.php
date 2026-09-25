<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-sm"></div>

<script>
class ToastManager {
    constructor() {
        this.container = document.getElementById('toast-container');
    }

    show(message, type = 'success') {
        const colors = {
            success: 'border-[#10b981] bg-[#10b981]/5',
            error: 'border-[#ef4444] bg-[#ef4444]/5',
            warning: 'border-[#f59e0b] bg-[#f59e0b]/5',
            info: 'border-[#2563eb] bg-[#2563eb]/5',
        };
        const icons = {
            success: '<svg class="h-5 w-5 text-[#10b981]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            error: '<svg class="h-5 w-5 text-[#ef4444]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>',
            warning: '<svg class="h-5 w-5 text-[#f59e0b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
            info: '<svg class="h-5 w-5 text-[#2563eb]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>',
        };

        const toast = document.createElement('div');
        toast.className = `flex items-center gap-3 rounded-xl border-l-4 ${colors[type] || colors.info} bg-white px-4 py-3 shadow-lg transition-all duration-300 ease-out transform translate-x-full opacity-0`;
        toast.innerHTML = `${icons[type] || icons.info}<p class="text-sm font-medium text-[#1e293b] flex-1">${message}</p><button onclick="this.parentElement.remove()" class="text-[#94a3b8] hover:text-[#1e293b]"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>`;

        this.container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
}

window.toast = new ToastManager();

// Auto-show flash messages as toasts
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.getElementById('flash-message');
    if (flash) {
        toast.show(flash.textContent.trim(), 'success');
        flash.style.display = 'none';
    }
});
</script>
