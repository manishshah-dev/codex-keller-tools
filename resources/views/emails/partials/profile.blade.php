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