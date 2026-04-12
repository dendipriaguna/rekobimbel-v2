<header class="fixed top-0 left-0 right-0 z-50 xl:hidden pointer-events-none p-4">
    <!-- Mobile Menu Toggle Button (Floats on mobile) -->
    <button
        class="pointer-events-auto flex items-center justify-center w-11 h-11 text-gray-500 rounded-xl dark:text-gray-400 bg-white/90 backdrop-blur-sm dark:bg-gray-900/90 border border-gray-200 dark:border-gray-800 shadow-sm"
        @click="$store.sidebar.toggleMobileOpen()" aria-label="Toggle Mobile Menu">
        <svg x-show="!$store.sidebar.isMobileOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" class="stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
        <svg x-show="$store.sidebar.isMobileOpen" class="stroke-current" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
</header>
