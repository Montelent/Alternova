<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{{ $title }}</title>
    <link>{{ $link }}</link>
    <description>{{ $description }}</description>
    <language>en</language>
    <lastBuildDate>{{ \Carbon\Carbon::parse($updated)->toRfc2822String() }}</lastBuildDate>
    <atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml" />
    @foreach($items as $item)
    <item>
      <title>{{ $item->name }} — alternative to {{ $item->proprietaryTool?->name ?? 'proprietary software' }}</title>
      <link>{{ route('alternatives.show', $item) }}</link>
      <guid isPermaLink="true">{{ route('alternatives.show', $item) }}</guid>
      <pubDate>{{ optional($item->updated_at)->toRfc2822String() }}</pubDate>
      <description><![CDATA[{{ $item->description }}]]></description>
    </item>
    @endforeach
  </channel>
</rss>
