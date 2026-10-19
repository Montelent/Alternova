@auth
    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <a href="{{ \App\Support\AdminPanel::url() }}" @click="mobileOpen=false" class="flex items-center rounded-xl px-3 py-3 text-slate-500">Admin panel</a>
    @endif
@endauth
