<!-- resources/views/vendor/survloop/inc-emoji-tags.blade.php -->
<div id="{{ $spot }}Tags" class="emojiTags"><ul class="nav nav-pills">
@forelse ($emojis as $emo)
    @if (!$emo["admin"] || $admPower)
        <li id="{{ $spot }}e{{ $emo['id'] }}Tag" @if (in_array($uID, $users[$emo["id"]])) class="active" @endif 
            >{!! view('vendor.survloop.inc-emoji-tag', [
                "spot"     => $spot,
                "emo"      => $emo,
                "cnt"      => sizeof($users[$emo["id"]]),
                "isActive" => in_array($uID, $users[$emo["id"]])
            ])->render() !!}</li>
    @endif
@empty
@endforelse
</ul></div><div id="{{ $spot }}TagsMore"></div>