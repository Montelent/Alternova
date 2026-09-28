<div class="mb-8 space-y-4">
    @include('partials.share-buttons', [
        'url' => route('alternatives.show', $alternative),
        'text' => $alternative->name.' — open-source alternative on Alternova',
    ])

    <livewire:report-issue :alternative-id="$alternative->id" :key="'report-'.$alternative->id" />

    @if($alternative->proprietaryTool)
        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 text-xs text-slate-500">
            <p class="font-semibold text-slate-700 dark:text-slate-300 mb-1">Embed this list</p>
            <code class="block break-all bg-slate-50 dark:bg-slate-800 rounded-lg p-2">
                &lt;iframe src="{{ url('/embed/tools/'.$alternative->proprietaryTool->slug) }}" width="100%" height="360" style="border:0;border-radius:12px" loading="lazy"&gt;&lt;/iframe&gt;
            </code>
        </div>
    @endif
</div>
