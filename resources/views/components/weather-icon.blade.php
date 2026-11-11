@props(['code', 'class' => 'size-6'])
@php
    $code = (int) $code;
    $name = match (true) {
        in_array($code, [0, 1], true) => 'sun',
        $code === 2 => 'cloud-sun',
        in_array($code, [5, 10, 45], true) => 'fog',
        in_array($code, [95, 97], true) => 'storm',
        $code >= 60 => 'rain',
        default => 'cloud',
    };
@endphp
<x-icon :name="$name" :class="$class" {{ $attributes }}/>
