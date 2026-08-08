@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm text-red-600', 'id' => str_replace('.', '_', $for).'-error', 'role' => 'alert']) }}>{{ $message }}</p>
@enderror
