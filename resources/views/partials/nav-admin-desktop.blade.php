@auth
    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <a href="{{ \App\Support\AdminPanel::url() }}" class="hidden md:inline-flex px-2.5 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Admin</a>
    @endif
@endauth
