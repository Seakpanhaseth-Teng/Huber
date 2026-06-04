@props(['rating' => 0, 'max' => 5, 'size' => 'text-2xl'])

<div class="{{ $size }} text-amber-400">
    @for($i = 1; $i <= $max; $i++)
        <i class="fas fa-star{{ $i <= $rating ? '' : '-o' }}"></i>
    @endfor
</div>
