<!-- resources/views/components/container.blade.php -->
<div {{ $attributes->merge(['class' => 'max-w-7xl mx-auto']) }}>
    {{ $slot }}
</div>