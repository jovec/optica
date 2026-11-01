<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css'])

        <style>
            @page {
                size: A4;
                margin: 12mm;
            }

            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body class="bg-zinc-100 text-zinc-900 print:bg-white">
        <div class="no-print mx-auto flex max-w-[148mm] justify-end gap-2 py-4">
            <button type="button" onclick="history.back()" class="rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm">
                {{ __('Back') }}
            </button>
            <button type="button" onclick="window.print()" class="rounded-md bg-zinc-900 px-3 py-1.5 text-sm text-white">
                {{ __('Print') }}
            </button>
        </div>

        <main class="mx-auto min-h-[210mm] max-w-[148mm] bg-white p-[12mm] shadow print:min-h-0 print:max-w-none print:p-0 print:shadow-none">
            {{ $slot }}
        </main>
    </body>
</html>
