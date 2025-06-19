<?php

namespace scrapify\PdfTools;

use Illuminate\Support\ServiceProvider;

class PdfToolsServiceProvider extends ServiceProvider
{
    public function boot() {}

    public function register()
    {
        $this->app->bind('pdfmerger', function () {
            return new PdfMerger();
        });
    }
}
