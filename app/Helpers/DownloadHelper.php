<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Framework\Http\Response;

class DownloadHelper
{
    /**
     * @var string
     */
    protected static $filename;

    /**
     * force browser to download file
     *
     * @param  string $filename
     * @return \App\Helpers\DownloadHelper
     */
    public static function init(string $filename, bool $real_file = false): self
    {
        self::$filename = $real_file ? basename($filename) : $filename;
        
		(new Response())->headers([
			'Content-Description' => 'File Transfer',
            'Content-Type' => mime_content_type(self::$filename),
			'Content-Disposition' => 'attachment; filename="' . self::$filename . '"',
			'Cache-Control' => 'no-cache',
			'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        
        return new self();
    }
    
    /**
     * send file content
     *
     * @return void
     */
    public function sendFile(): void
    {
        ob_clean();
        flush();
        readfile(self::$filename);

        exit();
    }
    
    /**
     * send stream content
     *
     * @param  mixed $data
     * @return void
     */
    public function sendStream($data): void
    {
        $handle = fopen('php://output', 'w');
        fwrite($handle, $data);
		fclose($handle);

		exit();
    }
    
    /**
     * send CSV file
     *
     * @param  array $data
     * @param  array|null $headers
     * @return void
     */
    public function sendCSV(array $data, ?array $headers = null): void
    {
        $handle = fopen('php://output', 'w');

		//insert headers
		if (!is_null($headers)) {
            fputcsv($handle, $headers);
        }

		//insert rows
		foreach ($data as $row) {
			fputcsv($handle, $row);
		}

		fclose($handle);
		exit();
    }
    
    /**
     * send PDF file
     *
     * @param  string $html
     * @param  array|null $headers
     * @return void
     */
    public function sendPDF(string $html): void
    {
        $options = new Options();
        $options->setIsHtml5ParserEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

		exit($this->sendStream($dompdf->output()));
    }
}
