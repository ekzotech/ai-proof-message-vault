@props(['segments'])
<div {{ $attributes->class(['capsule-text']) }} dir="auto">@foreach ($segments as $segment)@if ($segment['kind'] === 'rtl')<bdo dir="rtl">{{ $segment['text'] }}</bdo>@else{{ $segment['text'] }}@endif@endforeach</div>
