<?php

namespace App\Services;

use Imagick;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class PdfService extends FPDI
{

    public function extractSection($filePath, $outputPath, $x, $y, $width, $height)
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($filePath);

        $tplId = $pdf->importPage(1);

        $size = $pdf->getTemplateSize($tplId);

        $pdf->AddPage("LouLandscape", array('210','90'),0);

        $pdf->useTemplate($tplId, -$x, -$y, $width, $height);

        $pdf->Output($outputPath, 'F');

        $gsPath = '"C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe"';

        $imageOutputPath = str_replace('.pdf', '.png', $outputPath);

        $command = "$gsPath -sDEVICE=png16m -o \"$imageOutputPath\" -r300 \"$outputPath\"";

        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Error converting PDF to image. Command output:\n" . implode("\n", $output));
        }

        return $imageOutputPath;
    }



    public function extractPdfSectionAsImage($filePath, $x, $y, $width, $height, $outputPath)
    {
        $imagick = new Imagick();
        $imagick->readImage($filePath . '[0]'); // Load first page
        $imagick->cropImage($width, $height, $x, $y);
        $imagick->writeImage($outputPath);

        return $outputPath;
    }

}
