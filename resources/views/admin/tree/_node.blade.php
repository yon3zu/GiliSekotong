@php($isLeaf = $node['type'] === 'leaf')
<li>
    @isset($edge)
        <span class="mb-1 rounded-full bg-sand-100 px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap text-stone-700">{{ $edge }}</span>
    @endisset
    @if ($isLeaf)
        @php($tone = \App\Support\Sailing::tone($node['label']))
        <div class="rounded-lg px-2.5 py-1.5 text-center text-xs whitespace-nowrap tone-{{ $tone }}">
            <p class="font-bold">{{ $node['label'] }}</p>
            <p class="num text-[10px] opacity-75">n={{ $node['samples'] }}</p>
        </div>
    @else
        <div class="rounded-xl bg-sea-900 px-3 py-2 text-center text-white shadow-sm">
            <p class="text-xs font-bold whitespace-nowrap">{{ \App\Support\Sailing::label($node['attribute']) }}</p>
            <p class="num text-[10px] whitespace-nowrap text-sea-200">n={{ $node['samples'] }} · GR {{ number_format($node['gain_ratio'], 3) }}</p>
        </div>
        <ul>
            @foreach ($node['children'] as $value => $child)
                @include('admin.tree._node', ['node' => $child, 'edge' => $value])
            @endforeach
        </ul>
    @endif
</li>
