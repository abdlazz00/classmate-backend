<div class="space-y-2">
    @foreach($files as $m)
        <div class="py-2 border-b">
            <a href="{{ $m->getUrl() }}"
               target="_blank"
               class="text-blue-600 hover:text-blue-800 underline">

                {{ $m->file_name }}
                ({{ number_format($m->size / 1024, 1) }} KB)

            </a>
        </div>
    @endforeach

    @if($files->isEmpty())
        <p class="text-gray-500">Belum ada file yang diupload.</p>
    @endif
</div>
