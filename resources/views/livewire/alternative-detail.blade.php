<div class="min-h-screen bg-gray-50">
    {{-- Schema.org JSON-LD --}}
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
    </script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm text-gray-500">
            <a href="{{ route('finder') }}" class="hover:text-indigo-600">Alternatives</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $alternative->name }}</span>
        </nav>

        {{-- Hero --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $alternative->name }}</h1>
                    <p class="mt-2 text-lg text-gray-600">
                        Open-source alternative to
                        <span class="font-semibold text-gray-900">{{ $proprietary?->name }}</span>
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($alternative->license_type)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $alternative->license_type }}
                            </span>
                        @endif
                        @if($alternative->primary_language)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $alternative->primary_language }}
                            </span>
                        @endif
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            Self-host difficulty: {{ $alternative->self_host_difficulty }}/5
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                        </svg>
                        View on GitHub
                    </a>
                    @if($alternative->website_url)
                        <a href="{{ $alternative->website_url }}" target="_blank" rel="noopener"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Official Website
                        </a>
                    @endif
                </div>
            </div>

            <p class="mt-6 text-gray-700 leading-relaxed">{{ $alternative->description }}</p>
        </div>

        {{-- Metrics Widgets --}}
        @if($metric)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($metric->github_stars) }}</div>
                    <div class="text-sm text-gray-500 mt-1">GitHub Stars</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($metric->github_forks) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Forks</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($metric->open_issues) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Open Issues</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($alternative->overall_health_score, 1) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Health Score</div>
                </div>
            </div>
        @endif

        {{-- Side-by-side Comparison --}}
        @if($proprietary && $proprietary->key_features)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Feature Comparison</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feature</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $proprietary->name }}</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $alternative->name }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($proprietary->key_features as $feature)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $feature }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <svg class="mx-auto h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <svg class="mx-auto h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Docker Compose Preview --}}
        @if($alternative->docker_compose_blueprint)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-8"
                x-data="{ copied: false }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Docker Compose Blueprint</h2>
                    <button
                        @click="navigator.clipboard.writeText($refs.code.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-green-600">Copied!</span>
                    </button>
                </div>
                <pre class="bg-gray-900 text-gray-100 rounded-xl p-5 overflow-x-auto text-sm leading-relaxed"><code x-ref="code">{{ $alternative->docker_compose_blueprint }}</code></pre>
            </div>
        @endif

        {{-- Affiliate Deploy Buttons --}}
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 p-8 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">One-Click Deploy</h2>
            <div class="flex flex-wrap gap-4">
                <a href="https://render.com/deploy?repo={{ urlencode($alternative->repo_url) }}" target="_blank" rel="noopener sponsored"
                    class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Deploy on Render
                </a>
                <a href="https://cloud.digitalocean.com/apps/new?repo={{ urlencode($alternative->repo_url) }}" target="_blank" rel="noopener sponsored"
                    class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Deploy on DigitalOcean
                </a>
            </div>
        </div>

        {{-- SEO Footer Block --}}
        <div class="prose prose-indigo max-w-none bg-white rounded-2xl border border-gray-200 p-8">
            <h2>Why choose {{ $alternative->name }} over {{ $proprietary?->name }}?</h2>
            <p>
                {{ $alternative->name }} is a community-driven open-source project that provides a powerful,
                self-hostable alternative to {{ $proprietary?->name }}. With a health score of
                {{ number_format($alternative->overall_health_score, 1) }}/100, active development, and a
                {{ $alternative->license_type }} license, it gives you full control over your data and infrastructure.
            </p>

            <h3>Self-hosting guide</h3>
            <p>
                The provided Docker Compose blueprint makes deployment straightforward. Adjust environment variables,
                volumes, and networking according to your needs. For production, consider adding reverse-proxy,
                SSL termination, and automated backups.
            </p>

            <h3>Frequently Asked Questions</h3>
            <h4>Is {{ $alternative->name }} free?</h4>
            <p>Yes. It is released under the {{ $alternative->license_type }} license and can be self-hosted at no cost.</p>

            <h4>How difficult is it to self-host?</h4>
            <p>Difficulty is rated {{ $alternative->self_host_difficulty }}/5. Basic Docker knowledge is recommended.</p>

            <h4>Where can I contribute?</h4>
            <p>Visit the <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener">GitHub repository</a> to open issues or submit pull requests.</p>
        </div>
    </div>
</div>
