<?php

namespace Modules\Printer\Services\Render;

use Illuminate\View\View;
use InvalidArgumentException;
use Modules\Printer\Enum\PrintContentType;
use Modules\Printer\Enum\PrinterPaperSize;
use Modules\Printer\Services\Render\Templates\TemplateBuilder;
use Spatie\Browsershot\Browsershot;

class PrintRenderService implements PrintRenderServiceInterface
{
    /** @inheritDoc */
    public function renderToBase64(
        PrintContentType $type,
        array            $payload,
        PrinterPaperSize $paperSize = PrinterPaperSize::Paper80mm,
        string           $renderAs = 'image'
    ): string
    {
        return match ($renderAs) {
            'image' => base64_encode($this->renderToImage($type, $payload, $paperSize)),
            'pdf' => $this->createPdfBrowsershot(
                $this->renderToHtml($type, $payload, $paperSize),
                $paperSize
            )->base64pdf(),
            default => throw new InvalidArgumentException("Unsupported render output format [{$renderAs}]."),
        };
    }

    /** @inheritDoc */
    public function renderToImage(
        PrintContentType $type,
        array            $payload,
        PrinterPaperSize $paperSize = PrinterPaperSize::Paper80mm
    ): string
    {
        return $this->createBrowsershot(
            $this->renderToHtml($type, $payload, $paperSize),
            $paperSize
        )
            ->setScreenshotType('png')
            ->select('.receipt')
            ->screenshot();
    }

    /**
     * Create a shared Browsershot instance configured for receipt rendering.
     *
     * @param string $html
     * @param PrinterPaperSize $paperSize
     * @return Browsershot
     */
    private function createBrowsershot(string $html, PrinterPaperSize $paperSize): Browsershot
    {
        $profile = $this->resolveMediaProfile($paperSize);

        return Browsershot::html($html)
            ->emulateMedia('print')
            ->windowSize($profile['pixel_width'], 6000)
            ->showBackground()
            ->setOption('defaultBackgroundColor', [
                'r' => 255,
                'g' => 255,
                'b' => 255,
                'a' => 1,
            ])
            ->addChromiumArguments([
                'font-render-hinting' => 'none',
                'force-color-profile' => 'srgb',
            ]);
    }

    /**
     * Resolve configured media profile.
     *
     * @param PrinterPaperSize $paperSize
     * @return array{paper_width_mm:int,pixel_width:int}
     */
    private function resolveMediaProfile(PrinterPaperSize $paperSize): array
    {
        $profile = config('printer.media_profiles.' . $paperSize->value, []);
        $paperWidthMm = (int)($profile['paper_width_mm'] ?? 80);

        return [
            ...$profile,
            'paper_width_mm' => $paperWidthMm,
            'pixel_width' => (int)($profile['pixel_width'] ?? 576),
        ];
    }

    /** @inheritDoc */
    public function renderToHtml(
        PrintContentType $type,
        array            $payload,
        PrinterPaperSize $paperSize = PrinterPaperSize::Paper80mm
    ): string
    {
        return $this->build($type, $payload, $paperSize)->render();
    }

    /** @inheritDoc */
    public function build(
        PrintContentType $type,
        array            $payload,
        PrinterPaperSize $paperSize = PrinterPaperSize::Paper80mm
    ): View
    {
        return (new TemplateBuilder())->build($type, $payload, $paperSize);
    }

    /**
     * Create a Browsershot instance configured for receipt PDF export.
     *
     * @param string $html
     * @param PrinterPaperSize $paperSize
     * @return Browsershot
     */
    private function createPdfBrowsershot(string $html, PrinterPaperSize $paperSize): Browsershot
    {
        $profile = $this->resolveMediaProfile($paperSize);

        return $this->createBrowsershot($html, $paperSize)
            ->paperSize($profile['paper_width_mm'], 200)
            ->margins(0, 0, 0, 0)
            ->showBackground();
    }

    /** @inheritDoc */
    public function renderToPdf(
        PrintContentType $type,
        array            $payload,
        PrinterPaperSize $paperSize = PrinterPaperSize::Paper80mm
    ): string
    {
        return $this->createPdfBrowsershot(
            $this->renderToHtml($type, $payload, $paperSize),
            $paperSize
        )->pdf();
    }
}
