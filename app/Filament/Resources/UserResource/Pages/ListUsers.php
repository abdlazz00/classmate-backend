<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportStudents; // <--- Pastikan ini ada

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->steps([
                    // --- LANGKAH 1: UPLOAD ---
                    Step::make('Upload File')
                        ->description('Upload file excel data siswa')
                        ->schema([
                            FileUpload::make('attachment')
                                ->label('File Excel')
                                ->disk('local')
                                ->directory('temp-imports')
                                ->rules(['mimes:xlsx,xls,csv'])
                                ->required(),
                        ]),

                    // --- LANGKAH 2: PREVIEW ---
                    Step::make('Review Data')
                        ->description('Cek apakah data sudah benar')
                        ->schema([
                            Placeholder::make('preview_table')
                                ->label('Preview Data')
                                ->content(function (Get $get) {
                                    $file = $get('attachment');

                                    // 1. Handle jika array
                                    if (is_array($file)) {
                                        $file = reset($file);
                                    }

                                    if (!$file) {
                                        return new HtmlString('<div class="text-orange-500">Silakan upload file terlebih dahulu.</div>');
                                    }

                                    try {
                                        // --- PERBAIKAN UTAMA DI SINI (DETECT PATH) ---

                                        // Cek 1: Apakah file ini obyek Temporary Livewire?
                                        if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            $path = $file->getRealPath();
                                        }
                                        // Cek 2: Apakah ini path absolut Windows (Temporary)?
                                        // (Jika file_exists() langsung ketemu, berarti itu path absolut C:\Users...)
                                        elseif (is_string($file) && file_exists($file)) {
                                            $path = $file;
                                        }
                                        // Cek 3: Jika bukan keduanya, berarti path relatif Storage Laravel
                                        else {
                                            $path = Storage::disk('local')->path($file);
                                        }

                                        // ---------------------------------------------

                                        // Validasi Akhir
                                        if (!file_exists($path)) {
                                            return new HtmlString('<div class="text-red-500">File tidak dapat dibaca (Path: '.htmlspecialchars($path).').</div>');
                                        }

                                        // 2. Baca Excel
                                        $data = Excel::toArray(new ImportStudents, $path);

                                        // ... (SISA KODE KE BAWAH SAMA SEPERTI SEBELUMNYA) ...

                                        if (empty($data) || !isset($data[0])) {
                                            return new HtmlString('<div class="text-orange-500">File Excel kosong.</div>');
                                        }

                                        $sheet = $data[0];
                                        $previewRows = array_slice($sheet, 0, 5);
                                        $firstRow = $previewRows[0] ?? null;

                                        if (!$firstRow) {
                                            return new HtmlString('<div class="text-orange-500">Tidak ada data ditemukan.</div>');
                                        }

                                        $headers = array_keys($firstRow);

                                        // Render Tabel
                                        $html = '<div class="overflow-x-auto border rounded-lg shadow-sm mb-2">';
                                        $html .= '<table class="w-full text-sm text-left text-gray-500 divide-y divide-gray-200">';

                                        $html .= '<thead class="bg-gray-50 text-xs text-gray-700 uppercase"><tr>';
                                        foreach($headers as $header) {
                                            $html .= '<th class="px-4 py-3 font-medium">' . htmlspecialchars($header) . '</th>';
                                        }
                                        $html .= '</tr></thead>';

                                        $html .= '<tbody class="divide-y divide-gray-200 bg-white">';
                                        foreach($previewRows as $row) {
                                            $html .= '<tr class="hover:bg-gray-50">';
                                            foreach($headers as $header) {
                                                $val = $row[$header] ?? '-';
                                                $html .= '<td class="px-4 py-2 whitespace-nowrap">' . htmlspecialchars($val) . '</td>';
                                            }
                                            $html .= '</tr>';
                                        }
                                        $html .= '</tbody></table></div>';

                                        return new HtmlString($html);

                                    } catch (\Throwable $e) {
                                        return new HtmlString('
                                            <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50">
                                                <span class="font-bold">Error:</span> '.htmlspecialchars($e->getMessage()).'
                                            </div>
                                        ');
                                    }
                                })
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        $file = $data['attachment'];

                        if (is_array($file)) {
                            $file = reset($file);
                        }

                        // --- LOGIKA PATH YANG SAMA ---
                        if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                            $path = $file->getRealPath();
                        }
                        elseif (is_string($file) && file_exists($file)) {
                            $path = $file;
                        }
                        else {
                            $path = Storage::disk('local')->path($file);
                        }
                        // -----------------------------

                        Excel::import(new ImportStudents, $path);

                        // Hapus file hanya jika ada di Storage lokal (bukan temp windows)
                        if (Storage::disk('local')->exists($file)) {
                            Storage::disk('local')->delete($file);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Import Berhasil')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal Import')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }
}
