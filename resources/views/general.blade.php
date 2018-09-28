@extends('emails.layout')

@section('page-body')
    <div style="">
        @if(!empty($input['user']['display_name']))
            <p>{{$input['user']['display_name']}} ({{$input['user']['email']}}) has sent the following message:</p>
        @else
            <p>{{$input['user']['email']}} has sent the following message:</p>
        @endif
        <p>{{$input['message']}}</p>
    </div>
@stop