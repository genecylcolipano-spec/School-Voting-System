@props([
    'href',
    'label',
    'active' => false,
])

<a
    href="{{ $href }}"
    @click="sidebarOpen = false"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition '.(
            $active
                ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white shadow-lg shadow-violet-900/30'
                : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'
        ),
    ]) }}
>
    {{ $icon }}
    <span x-show="!collapsed" class="truncate">{{ $label }}</span>
</a>
