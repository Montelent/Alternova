{{-- Back-compat: any @include('partials.share-buttons') uses the unified component --}}
<x-share-buttons
    :url="$url ?? null"
    :title="$title ?? null"
    :text="$text ?? null"
    :image="$image ?? null"
    :compact="$compact ?? false"
/>
