{{-- Styles for TinyMCE / CMS HTML on the public site --}}
<style>
    .cms-content, .prose {
        color: #334155;
        font-size: 1.0625rem;
        line-height: 1.75;
    }
    .dark .cms-content, .dark .prose { color: #cbd5e1; }
    .cms-content > *:first-child, .prose > *:first-child { margin-top: 0; }
    .cms-content h1, .prose h1 {
        font-size: 1.875rem; font-weight: 800; line-height: 1.25;
        color: #0f172a; margin: 1.75rem 0 0.75rem;
    }
    .cms-content h2, .prose h2 {
        font-size: 1.5rem; font-weight: 700; line-height: 1.3;
        color: #0f172a; margin: 1.75rem 0 0.65rem;
    }
    .cms-content h3, .prose h3 {
        font-size: 1.25rem; font-weight: 700; line-height: 1.35;
        color: #0f172a; margin: 1.5rem 0 0.5rem;
    }
    .cms-content h4, .prose h4 {
        font-size: 1.125rem; font-weight: 600;
        color: #0f172a; margin: 1.25rem 0 0.5rem;
    }
    .dark .cms-content h1, .dark .cms-content h2, .dark .cms-content h3, .dark .cms-content h4,
    .dark .prose h1, .dark .prose h2, .dark .prose h3, .dark .prose h4 { color: #f8fafc; }
    .cms-content p, .prose p { margin: 0.9rem 0; }
    .cms-content a, .prose a {
        color: #4f46e5; font-weight: 500; text-decoration: underline;
        text-underline-offset: 2px;
    }
    .dark .cms-content a, .dark .prose a { color: #a5b4fc; }
    .cms-content strong, .prose strong { font-weight: 700; color: #0f172a; }
    .dark .cms-content strong, .dark .prose strong { color: #f1f5f9; }
    .cms-content em, .prose em { font-style: italic; }
    .cms-content ul, .prose ul {
        list-style-type: disc; padding-left: 1.5rem; margin: 0.9rem 0;
    }
    .cms-content ol, .prose ol {
        list-style-type: decimal; padding-left: 1.5rem; margin: 0.9rem 0;
    }
    .cms-content li, .prose li { margin: 0.35rem 0; padding-left: 0.25rem; }
    .cms-content li > ul, .cms-content li > ol,
    .prose li > ul, .prose li > ol { margin: 0.25rem 0; }
    .cms-content blockquote, .prose blockquote {
        border-left: 4px solid #c7d2fe; padding: 0.35rem 0 0.35rem 1rem;
        margin: 1.25rem 0; color: #475569; font-style: italic;
    }
    .dark .cms-content blockquote, .dark .prose blockquote {
        border-left-color: #4338ca; color: #94a3b8;
    }
    .cms-content img, .prose img {
        max-width: 100%; height: auto; border-radius: 0.75rem;
        margin: 1.25rem auto; display: block;
    }
    .cms-content figure, .prose figure { margin: 1.5rem 0; }
    .cms-content figcaption, .prose figcaption {
        font-size: 0.875rem; color: #64748b; text-align: center; margin-top: 0.5rem;
    }
    .cms-content hr, .prose hr {
        border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;
    }
    .dark .cms-content hr, .dark .prose hr { border-top-color: #334155; }
    .cms-content table, .prose table {
        width: 100%; border-collapse: collapse; margin: 1.25rem 0;
        font-size: 0.95rem;
    }
    .cms-content th, .cms-content td, .prose th, .prose td {
        border: 1px solid #e2e8f0; padding: 0.6rem 0.75rem; text-align: left;
    }
    .dark .cms-content th, .dark .cms-content td,
    .dark .prose th, .dark .prose td { border-color: #334155; }
    .cms-content th, .prose th {
        background: #f8fafc; font-weight: 600; color: #0f172a;
    }
    .dark .cms-content th, .dark .prose th { background: #1e293b; color: #f1f5f9; }
    .cms-content code, .prose code {
        font-size: 0.9em; background: #f1f5f9; padding: 0.15rem 0.4rem;
        border-radius: 0.25rem; color: #0f172a;
    }
    .dark .cms-content code, .dark .prose code { background: #1e293b; color: #e2e8f0; }
    .cms-content pre, .prose pre {
        background: #0f172a; color: #e2e8f0; padding: 1rem 1.15rem;
        border-radius: 0.75rem; overflow-x: auto; margin: 1.25rem 0;
        font-size: 0.875rem; line-height: 1.6;
    }
    .cms-content pre code, .prose pre code { background: transparent; padding: 0; color: inherit; }
    .cms-content [style*="text-align: center"], .cms-content [align="center"],
    .prose [style*="text-align: center"], .prose [align="center"] { text-align: center; }
    .cms-content [style*="text-align: right"], .cms-content [align="right"],
    .prose [style*="text-align: right"], .prose [align="right"] { text-align: right; }
    .cms-content [style*="text-align: left"], .cms-content [align="left"],
    .prose [style*="text-align: left"], .prose [align="left"] { text-align: left; }
    .cms-content [style*="text-align: justify"],
    .prose [style*="text-align: justify"] { text-align: justify; }
    .cms-content img[style*="float: left"], .prose img[style*="float: left"] {
        float: left; margin: 0.25rem 1rem 0.75rem 0;
    }
    .cms-content img[style*="float: right"], .prose img[style*="float: right"] {
        float: right; margin: 0.25rem 0 0.75rem 1rem;
    }
    .cms-content::after, .prose::after { content: ""; display: table; clear: both; }
    .cms-content iframe, .prose iframe { max-width: 100%; border-radius: 0.75rem; }
    .cms-content video, .prose video { max-width: 100%; height: auto; border-radius: 0.75rem; }
</style>
