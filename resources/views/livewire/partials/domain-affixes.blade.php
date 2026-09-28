<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Prefixes</label>
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach($prefixes as $p)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $p }}
                    <button type="button" wire:click="removePrefix('{{ $p }}')" class="ml-1 opacity-60 hover:opacity-100">&times;</button>
                </span>
            @endforeach
        </div>
        <div class="flex gap-2">
            <input wire:model="prefixInput" wire:keydown.enter.prevent="addPrefix" type="text" placeholder="Add prefix"
                class="flex-1 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm">
            <button type="button" wire:click="addPrefix" class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm">Add</button>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Suffixes</label>
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach($suffixes as $s)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $s }}
                    <button type="button" wire:click="removeSuffix('{{ $s }}')" class="ml-1 opacity-60 hover:opacity-100">&times;</button>
                </span>
            @endforeach
        </div>
        <div class="flex gap-2">
            <input wire:model="suffixInput" wire:keydown.enter.prevent="addSuffix" type="text" placeholder="Add suffix"
                class="flex-1 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm">
            <button type="button" wire:click="addSuffix" class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm">Add</button>
        </div>
    </div>
</div>
