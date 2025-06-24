<?php
use Filament\Support\Facades\FilamentView;
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}"
    @class([
        'filament-panel-anggota min-h-screen',
        'dark' => filament()->hasDarkModeForced(),
    ])>

<head>
    {{ \Filament\Support\Facades\FilamentAsset::renderHtml() }}

    {{ filament()->getTheme()->getHtml() }}
    {{ filament()->getFontHtml() }}

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @livewireStyles

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const modal = document.getElementById('anggota-tetap-modal');
                if (modal) {
                    modal.dispatchEvent(new CustomEvent('open-modal'));
                }
            }, 100);
        });
    </script>
</head>

<body class="filament-body bg-gray-100 dark:bg-gray-950">
    {{ filament()->renderHook('panels::body.start') }}

    {{ $slot }}

    @livewire('notifications')

    {{ \Filament\Support\Facades\FilamentAsset::renderScripts() }}

    @livewireScriptConfig

    @stack('scripts')

    <!-- Modal Component -->
    <x-anggota-tetap-modal />

    {{ filament()->renderHook('panels::body.end') }}
</body>

</html>
