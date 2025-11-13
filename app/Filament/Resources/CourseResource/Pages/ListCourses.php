<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportCourses; // <-- Import Class ImportCourses

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import Courses')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->steps([
                    Step::make('Upload File')
                        ->description('Upload file excel data mata kuliah')
                        ->schema([
                            FileUpload::make('attachment')
                                ->label('File Excel')
                                ->disk('local')
                                ->directory('temp-imports')
                                ->rules(['mimes:xlsx,xls,csv'])
                                ->required(),
                        ]),

                    Step::make('Review Data')
                        ->description('Preview data mata kuliah')
                        ->schema([
                            Placeholder::make('preview_table')
                                ->label('Preview Data')
                                ->content(fn (Get $get) => $this->getPreviewContent($get, new ImportCourses))
                        ]),
                ])
                ->action(fn (array $data) => $this->processImport($data, new ImportCourses)),

            Actions\CreateAction::make(),
        ];
    }

    // --- Helper Function untuk Preview (Bisa dipindah ke Trait jika mau reuse) ---
    protected function getPreviewContent(Get $get, $importClass)
    {
        $file = $get('attachment');
        if (is_array($file)) $file = reset($file);

        if (!$file) return new HtmlString('<div class="text-orange-500">Silakan upload file.</div>');

        try {
            if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $path = $file->getRealPath();
            } elseif (is_string($file) && file_exists($file)) {
                $path = $file;
            } else {
                $path = Storage::disk('local')->path($file);
            }

            if (!file_exists($path)) return new HtmlString('<div class="text-red-500">File tidak ditemukan.</div>');

            $data = Excel::toArray($importClass, $path);
            if (empty($data) || !isset($data[0])) return new HtmlString('<div class="text-orange-500">File kosong.</div>');

            $previewRows = array_slice($data[0], 0, 5);
            if (!$previewRows) return new HtmlString('<div class="text-orange-500">Tidak ada data.</div>');

            $headers = array_keys($previewRows[0]);

            $html = '<div class="overflow-x-auto border rounded-lg shadow-sm mb-2"><table class="w-full text-sm text-left text-gray-500 divide-y divide-gray-200"><thead class="bg-gray-50 text-xs text-gray-700 uppercase"><tr>';
            foreach($headers as $header) $html .= '<th class="px-4 py-3 font-medium">' . htmlspecialchars($header) . '</th>';
            $html .= '</tr></thead><tbody class="divide-y divide-gray-200 bg-white">';
            foreach($previewRows as $row) {
                $html .= '<tr class="hover:bg-gray-50">';
                foreach($headers as $header) $html .= '<td class="px-4 py-2 whitespace-nowrap">' . htmlspecialchars($row[$header] ?? '-') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            $html .= '<div class="text-xs text-gray-500 bg-blue-50 p-2 rounded">Pastikan header: <b>name, code, lecturer, class_name</b></div>';
            return new HtmlString($html);

        } catch (\Throwable $e) {
            return new HtmlString('<div class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }

    protected function processImport(array $data, $importClass)
    {
        try {
            $file = $data['attachment'];
            if (is_array($file)) $file = reset($file);

            if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $path = $file->getRealPath();
            } elseif (is_string($file) && file_exists($file)) {
                $path = $file;
            } else {
                $path = Storage::disk('local')->path($file);
            }

            Excel::import($importClass, $path);
            if (Storage::disk('local')->exists($file)) Storage::disk('local')->delete($file);

            \Filament\Notifications\Notification::make()->title('Import Berhasil')->success()->send();
        } catch (\Throwable $e) {
            \Filament\Notifications\Notification::make()->title('Gagal Import')->body($e->getMessage())->danger()->send();
        }
    }
}
