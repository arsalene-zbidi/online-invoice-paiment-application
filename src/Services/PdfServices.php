<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use function PHPUnit\Framework\throwException;

class PdfServices
{  private $dompdf;

     public function __construct()
     {  $this->dompdf=new Dompdf();
         $pdfOptions= new Options();
         $pdfOptions->set("defaultFont","Garamond");


         $this->dompdf->setOptions($pdfOptions);


     }
     public function showpdfFile($html){
         $this->dompdf->loadHtml($html);
         $this->dompdf->render();
         $this->dompdf->stream("facture.pdf",[
             'Attachement'=>false
         ]);

     }
    public function generateBinaryPdf($html){
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $this->dompdf->output();

    }



}