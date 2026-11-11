@props(['name', 'class' => 'size-5'])
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
    @case('users')<path d="M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="9" cy="7" r="3.5"/><path d="M22 19v-1a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>@break
    @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
    @case('calendar')<rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>@break
    @case('ruler')<path d="M3 17 17 3l4 4L7 21z"/><path d="m7.5 12.5 2 2M10.5 9.5l2 2M13.5 6.5l2 2"/>@break
    @case('engine')<path d="M4 10h3l2-3h6v3h3v6h-3l-2 2H9l-2-2H4z"/><path d="M1 12v3M23 11v4"/>@break
    @case('captain')<circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/><path d="M7.5 6.5h9"/>@break
    @case('star')<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9z"/>@break
    @case('check')<path d="m5 12.5 4.5 4.5L19 7.5"/>@break
    @case('wave')<path d="M2 9c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2M2 15c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/>@break
    @case('wind')<path d="M3 8h11a3 3 0 1 0-3-3M3 12h16a3 3 0 1 1-3 3M3 16h7"/>@break
    @case('eye')<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>@break
    @case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>@break
    @case('tide')<path d="M12 3v12M8 11l4 4 4-4M3 20c2 0 2-1.5 4.5-1.5S10 20 12 20s2-1.5 4.5-1.5S19 20 21 20"/>@break
    @case('pin')<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>@break
    @case('phone')<path d="M5 3h3.5l1.5 4.5-2.2 1.4a12 12 0 0 0 7.3 7.3l1.4-2.2L21 15.5V19a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2z"/>@break
    @case('mail')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6 8.5 7 8.5-7"/>@break
    @case('buoy')<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="m5.6 5.6 3.6 3.6M14.8 14.8l3.6 3.6M18.4 5.6l-3.6 3.6M9.2 14.8l-3.6 3.6"/>@break
    @case('food')<path d="M4 3v8a3 3 0 0 0 3 3v7M7 3v7M10 3v8a3 3 0 0 1-3 3"/><path d="M17 21V3c-2.5 1.5-4 4.5-4 8h4"/>@break
    @case('arrow')<path d="M5 12h14M13 6l6 6-6 6"/>@break
    @case('back')<path d="M19 12H5M11 6l-6 6 6 6"/>@break
    @case('menu')<path d="M4 7h16M4 12h16M4 17h10"/>@break
    @case('x')<path d="M6 6l12 12M18 6 6 18"/>@break
    @case('ticket')<path d="M3 8V6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2a2.5 2.5 0 0 0 0 5v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-2a2.5 2.5 0 0 0 0-5z" transform="translate(0 1.5)"/><path d="M14 6.5v11" stroke-dasharray="2 2"/>@break
    @case('snorkel')<path d="M3 10a4 4 0 0 1 4-4h6a4 4 0 0 1 0 8h-1l-1.5-2h-1L8 14H7a4 4 0 0 1-4-4z"/><path d="M19 3v11a5 5 0 0 1-5 5h-3"/>@break
    @case('shield')<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>@break
    @case('upload')<path d="M12 16V4M7 9l5-5 5 5M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>@break
    @case('chart')<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>@break
    @case('tree')<circle cx="12" cy="4.5" r="2"/><circle cx="5.5" cy="13" r="2"/><circle cx="18.5" cy="13" r="2"/><circle cx="12" cy="20" r="1.6"/><circle cx="18.5" cy="20" r="1.6"/><path d="M11 6.2 6.5 11.2M13 6.2l4.5 5M17.6 14.8 12.8 18.6M18.5 15v3.4"/>@break
    @case('database')<ellipse cx="12" cy="5.5" rx="8" ry="3"/><path d="M4 5.5v13c0 1.7 3.6 3 8 3s8-1.3 8-3v-13M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>@break
    @case('home')<path d="M3 11 12 3l9 8M5 9.5V21h14V9.5"/>@break
    @case('boat')<path d="M2 15h20l-2.5 4.5h-15z"/><path d="M12 15V3l7 9h-7M12 5 6.5 12H12"/>@break
    @case('settings')<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-2.7-1.1l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3.6 15H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.1-2.7l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 9.7 4.6V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0 1.1 2.7H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5.9z"/>@break
    @case('logout')<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l-5-5 5-5M5 12h11"/>@break
    @case('scan')<path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10"/>@break
    @case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.5v.5"/>@break
    @case('whatsapp')<path d="M4 20l1.3-3.8A8 8 0 1 1 8 19z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8A4 4 0 0 1 10.7 11l.8-1-1-2z"/>@break
    @case('cloud')<path d="M7 18a4.5 4.5 0 0 1-.5-9A6 6 0 0 1 18 9.5a4 4 0 0 1-.5 8.5z"/>@break
    @case('cloud-sun')<path d="M8 4.5v1M3.9 6.4l.7.7M2.5 10.5h1M12.1 6.4l-.7.7"/><path d="M5.2 12A3.5 3.5 0 0 1 11 8.3"/><path d="M9 20a3.5 3.5 0 0 1-.4-7A4.8 4.8 0 0 1 18 13a3.5 3.5 0 0 1-.5 7z"/>@break
    @case('rain')<path d="M7 15a4.5 4.5 0 0 1-.5-9A6 6 0 0 1 18 6.5a4 4 0 0 1-.5 8.5z"/><path d="M8 18l-1 3M12 18l-1 3M16 18l-1 3"/>@break
    @case('storm')<path d="M7 15a4.5 4.5 0 0 1-.5-9A6 6 0 0 1 18 6.5a4 4 0 0 1-.5 8.5z"/><path d="m12.5 13-2.5 4h3l-2 4"/>@break
    @case('fog')<path d="M4 9h16M3 13h18M5 17h14"/>@break
    @case('quake')<path d="M2 12h4l2-6 3 12 3-9 2 3h6"/>@break
    @case('alert')<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17v.5"/>@break
    @default<circle cx="12" cy="12" r="9"/>
@endswitch
</svg>
