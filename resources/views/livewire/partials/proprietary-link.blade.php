@if($alternative->proprietaryTool)
    <p class="text-sm text-slate-500 dark:text-slate-400">
        More options for
        <a href="{{ route('tools.show', $alternative->proprietaryTool) }}"
            class="font-semibold text-brand-600 hover:underline">
            {{ $alternative->proprietaryTool->name }}
        </a>
    </p>
@endif
