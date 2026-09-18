@component('mail::message')
<div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 24px;">
    <img src="{{ url('/images/ak-logo.png') }}" alt="{{ $store['storeName'] ?? config('app.name') }} Logo" style="max-height: 95px; width: auto; margin: 0 auto; display: block;" />
</div>

# Hello {{ $user->name }},

You have been invited to join **{{ $store['storeName'] ?? config('app.name') }}**.

Click the button below to accept your invitation and complete setting up your account:

@component('mail::button', ['url' => $url, 'color' => 'success'])
Accept Invitation & Join Store
@endcomponent

*This invitation link will expire in 7 days.*

If you did not request or expect this invitation, you can safely ignore this email.

<div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #475569; line-height: 1.6;">
    <strong style="color: #0f172a;">Store Contact Details:</strong><br>
    @if (!empty($store['storePhone']))
    📞 <strong>Phone:</strong> {{ $store['storePhone'] }}<br>
    @endif
    @if (!empty($store['storeEmail']))
    ✉️ <strong>Email:</strong> {{ $store['storeEmail'] }}<br>
    @endif
    @if (!empty($store['storeAddress']))
    📍 <strong>Address:</strong> {{ $store['storeAddress'] }}<br>
    @endif
</div>

Thanks,<br>
**{{ $store['storeName'] ?? config('app.name') }}**<br>
<span style="font-size: 12px; color: #64748b;">{{ $store['storeSubtitle'] ?? '' }}</span>
@endcomponent
