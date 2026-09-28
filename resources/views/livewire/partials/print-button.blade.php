<div class="print:hidden mb-4">
    <button type="button" onclick="window.print()"
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
        Print / Save PDF
    </button>
</div>
<style>
@media print {
    header, footer, .print\\:hidden, [x-cloak], nav, .fixed { display: none !important; }
    body { background: white !important; color: black !important; }
    main { max-width: 100% !important; }
    a[href]::after { content: " (" attr(href) ")"; font-size: 10px; color: #666; }
}
</style>
