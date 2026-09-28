<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>{{ $title }}</title>
  <link href="{{ $link }}" />
  <link rel="self" href="{{ $feedUrl }}" />
  <updated>{{ $updated }}</updated>
  <id>{{ $link }}</id>
  <subtitle>{{ $description }}</subtitle>
  @foreach($items as $item)
  <entry>
    <title>{{ $item->name }}</title>
    <link href="{{ route('alternatives.show', $item) }}" />
    <id>{{ route('alternatives.show', $item) }}</id>
    <updated>{{ optional($item->updated_at)->toAtomString() }}</updated>
    <summary type="text">{{ $item->description }}</summary>
  </entry>
  @endforeach
</feed>
