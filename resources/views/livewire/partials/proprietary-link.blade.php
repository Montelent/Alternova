@if($alternative->proprietaryTool)
    <p class="text-sm text-slate-600 dark:text-slate-400">
        Alternative to
        <a href="{{ route('alternativesto.show', $alternative->proprietaryTool) }}"
            class="font-medium text-brand-600 dark:text-brand-400 hover:underline">
            {{ $alternative->proprietaryTool->name }}
        </a>
    </p>
@endif
