@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }}</label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->class(['input', 'input-invalid' => $errors->has($name)]) }}
    >

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
