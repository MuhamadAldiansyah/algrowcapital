@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ asset('storage/ipos/logo.jpeg') }}" alt="Algrow Capital Logo" style="height: 65px; width: auto; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
</div>
<div style="text-align: center; font-size: 22px; font-weight: bold; color: #042f22;">
    {!! $slot !!}
</div>
</a>
</td>
</tr>
