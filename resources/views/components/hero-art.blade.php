{{-- Ilustrasi tiga gili Sekotong dilihat dari Dermaga Tawun. --}}
<svg viewBox="0 0 640 480" {{ $attributes->merge(['class' => 'block']) }} role="img" aria-label="Ilustrasi perahu menuju Gili Nanggu, Gili Sudak, dan Gili Kedis">
    <defs>
        <linearGradient id="hs" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fbdccf"/><stop offset=".55" stop-color="#f5eee1"/><stop offset="1" stop-color="#cfeceb"/></linearGradient>
        <linearGradient id="hw" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3fa9a6"/><stop offset=".5" stop-color="#188c8a"/><stop offset="1" stop-color="#0c4448"/></linearGradient>
        <clipPath id="hc"><rect width="640" height="480" rx="28"/></clipPath>
    </defs>
    <g clip-path="url(#hc)">
        <rect width="640" height="480" fill="url(#hs)"/>
        <circle cx="470" cy="150" r="58" fill="#f6c76b"/>
        <circle cx="470" cy="150" r="80" fill="#f6c76b" opacity=".18"/>
        <path d="M60 92c14-6 28-6 40 0M84 70c10-4 20-4 28 0" stroke="#cf4f27" stroke-width="2" fill="none" opacity=".35" stroke-linecap="round"/>
        <!-- daratan Lombok di kejauhan -->
        <path d="M0 252c60-30 110-48 170-40 40 6 70 22 120 26 60 4 100-16 160-20 70-4 130 16 190 34v30H0z" fill="#9fd8d6" opacity=".6"/>

        <rect y="262" width="640" height="218" fill="url(#hw)"/>

        <!-- Gili Kedis: kecil, mungil -->
        <g transform="translate(470 262)">
            <ellipse cx="0" cy="2" rx="46" ry="10" fill="#ebdfc8"/>
            <path d="M-30 0c6-10 18-16 30-16s24 6 30 16z" fill="#2f8a5f"/>
            <path d="M-2-14c1-12 0-22-4-30" stroke="#5b3a21" stroke-width="2.5" fill="none"/>
            <path d="M-6-44c-10-2-18 2-22 8 8-4 16-4 22-2-6-6-14-8-20-6 8-4 18-2 20 0 0-8 6-14 14-14-6 4-8 10-8 14 6-6 14-6 20-2-8 0-14 2-18 6 8 0 14 4 16 10-6-6-14-8-20-6" fill="#2f8a5f"/>
            <text x="0" y="40" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-size="12" font-weight="700" fill="#fbf8f2" opacity=".9">Gili Kedis</text>
        </g>
        <!-- Gili Sudak -->
        <g transform="translate(300 266)">
            <ellipse cx="0" cy="2" rx="90" ry="12" fill="#ebdfc8"/>
            <path d="M-74 0c14-22 40-34 74-34s56 12 74 34z" fill="#1f7350"/>
            <path d="M-40-10c10-14 26-20 40-20" stroke="#2f8a5f" stroke-width="10" stroke-linecap="round" fill="none" opacity=".6"/>
            <text x="0" y="40" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-size="12" font-weight="700" fill="#fbf8f2" opacity=".9">Gili Sudak</text>
        </g>
        <!-- Gili Nanggu -->
        <g transform="translate(120 272)">
            <ellipse cx="0" cy="2" rx="104" ry="14" fill="#ebdfc8"/>
            <path d="M-86 0c18-30 50-48 86-48s70 18 86 48z" fill="#246b4a"/>
            <path d="M30-30c2-16 0-28-6-38" stroke="#5b3a21" stroke-width="3" fill="none"/>
            <path d="M24-68c-12-4-24 0-30 8 10-4 20-4 28-2-8-8-18-10-26-8 10-6 24-2 28 2 0-10 8-18 18-18-8 6-10 12-10 18 8-8 18-8 26-2-10 0-18 2-22 8 10 0 18 4 20 12-8-8-18-10-26-8" fill="#2f8a5f"/>
            <text x="0" y="42" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-size="12" font-weight="700" fill="#fbf8f2" opacity=".9">Gili Nanggu</text>
        </g>

        <!-- jalur perahu -->
        <path d="M600 430C520 410 520 340 470 312 420 296 360 330 300 312 240 300 190 330 120 318" stroke="#fbf8f2" stroke-width="2.5" stroke-dasharray="2 10" stroke-linecap="round" fill="none" opacity=".8"/>

        <!-- perahu katir -->
        <g transform="translate(380 372)">
            <g class="animate-bob">
                <path d="M-80 18h170" stroke="#3b2a1a" stroke-width="3"/>
                <path d="M-96 26c18-4 34-4 50 0M60 26c18-4 34-4 50 0" stroke="#ebdfc8" stroke-width="6" stroke-linecap="round" fill="none"/>
                <path d="M-60 18l-26 10M70 18l26 10" stroke="#3b2a1a" stroke-width="2.5"/>
                <path d="M-70 0h150c-6 20-18 28-36 28h-82c-16 0-26-10-32-28z" fill="#e8683f"/>
                <path d="M-66 9h142" stroke="#fbf8f2" stroke-width="5"/>
                <path d="M-80-8c4 6 8 8 12 8M90-12c-4 8-8 12-12 12" stroke="#e8683f" stroke-width="8" stroke-linecap="round" fill="none"/>
                <rect x="-36" y="-34" width="84" height="7" rx="2" fill="#0d5a5a"/>
                <path d="M-30-27V0M42-27V0M6-27V0" stroke="#3b2a1a" stroke-width="2.5"/>
                <circle cx="-12" cy="-10" r="5" fill="#13262b"/><circle cx="24" cy="-10" r="5" fill="#13262b"/>
                <path d="M-8-4h-8M28-4h-8" stroke="#f6c76b" stroke-width="5" stroke-linecap="round"/>
            </g>
            <path d="M-110 40c20 0 30-6 50-6M90 40c20 0 30-6 50-6" stroke="#cfeceb" stroke-width="2" fill="none" opacity=".7" stroke-linecap="round"/>
        </g>

        <path d="M0 448c32 0 32-8 64-8s32 8 64 8 32-8 64-8 32 8 64 8 32-8 64-8 32 8 64 8 32-8 64-8 32 8 64 8 32-8 64-8 32 8 64 8" stroke="#9fd8d6" stroke-width="2" fill="none" opacity=".4"/>
    </g>
</svg>
