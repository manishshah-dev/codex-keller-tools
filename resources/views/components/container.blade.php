<!-- resources/views/components/container.blade.php -->
<div {{ $attributes->merge(['class' => 'max-w-7xl mx-auto sm:px-6 lg:px-8']) }}>
    {{ $slot }}
</div>