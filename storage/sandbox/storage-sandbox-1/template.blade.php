@if(isset($user))
<div class="user-info">
  <h3>안녕하세요, {{ $user['name'] }}님!</h3>
  <p>이메일: {{ $user['email'] }}</p>
</div>
@else
<div class="guest-info">
  <p>로그인이 필요합니다.</p>
</div>
@endif