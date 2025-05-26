<x-mail::message>
    <p>{{ $messageText }}</p>
    <h2>{{ $candidate->full_name }} - {{ $profile->title }}</h2>
    <p>{!! nl2br(e($profile->summary)) !!}</p>
    @if($profile->formatted_headings)
        @foreach($profile->formatted_headings as $heading)
            <h3>{{ $heading['title'] }}</h3>
            <ul>
                @foreach($heading['content'] as $bullet)
                    <li>{{ $bullet['content'] }}</li>
                @endforeach
            </ul>
        @endforeach
    @endif

<x-mail::button :url="'https://www.kellerexecutivesearch.com/'">
Check Keller Portal
</x-mail::button>

Thanks,<br>
Keller Executive Search
</x-mail::message>
