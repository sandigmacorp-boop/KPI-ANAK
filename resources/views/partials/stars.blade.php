{{-- $stars: 0-3 atau null · $id: opsional, target update JS --}}
<div class="stars" @isset($id) id="{{ $id }}" @endisset aria-label="{{ $stars === null ? 'Belum ada penilaian' : $stars.' dari 3 bintang' }}">
    @for ($i = 1; $i <= 3; $i++)
        <span class="star {{ ($stars ?? 0) >= $i ? 'on' : '' }}">★</span>
    @endfor
</div>
