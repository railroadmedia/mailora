<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>

<body>

{{--<div style="">--}}
{{--@if(!empty($input['user']['display_name']))--}}
{{--<p>{{$input['user']['display_name']}} ({{$input['user']['email']}}) has sent the following message:</p>--}}
{{--@else--}}
{{--<p>{{$input['user']['email']}} has sent the following message:</p>--}}
{{--@endif--}}
{{--<p>{{$input['message']}}</p>--}}
{{--</div>--}}

<div>
    <?php

    var_export($input, true);

    ?>
</div>

</body>
</html>
