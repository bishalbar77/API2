<?php

namespace App\Http\Controllers;

use App\Supedio;
use DOMDocument;
use App\SupedioInvoice;
use App\SupedioCustomer;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SupedioAPIController extends Controller
{
    private $apiResponse;

    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function API2(Request $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'PDF_file' => 'required|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->sendResponse(400, 'Parameters missing or invalid.', $validator->errors());
        }
        try {
            $findSupedio = Supedio::where('customer_number', $request->supedio_number)->first();
            if(!$findSupedio) {
                DB::commit();
                return $this->apiResponse->sendResponse(404, 'Supedio not found', null);
            }
            $findSupedioCustomer = SupedioCustomer::where('customer_number', $request->customer_number)->first();
            if(!$findSupedioCustomer) {
                DB::commit();
                return $this->apiResponse->sendResponse(404, 'Supedio customer not found', null);
            }
            $data['base64'] = chunk_split(base64_encode(file_get_contents($request->file('PDF_file'))));
            $file = $request->file('PDF_file')->getClientOriginalName() . '';
            $data['filename'] = str_replace("pdf","PDFA3",$file);
            $fileName = str_replace("pdf","xml",$file);
            $today = date('YmdHi');
            // Production start date
            $startDate = date('YmdHi', strtotime('2012-03-14 09:06:00'));
            $range = $today - $startDate;
            $rand = rand(0, $range);
            $data['orga_number'] = "SP" . $rand;
            $data['process_number'] = \Carbon\Carbon::now()->format('dmYhis') . mt_rand(100, 999);
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($request->file('PDF_file'));
            $path = storage_path('app/public/invoice/' . $fileName);
            $isExists = file_exists($path);
            if (!$isExists) {

                $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
                <urlset xmlns="http://www.sitemaps.org" xmlns:xhtml="http://www.w3.org/1999/xhtml">
                </urlset>';

                $dom = new DOMDocument;
                $dom->preserveWhiteSpace = FALSE;
                $dom->loadXML($xmlString);
                $dom->save($path);
            }
            if ($pdf != "") {
                $original_text = $pdf->getText();
                if ($original_text != "") {
                    $text = nl2br($original_text); 
                    $text = $this->clean_ascii_characters($text); 
                    $text = str_replace(array("<br /> <br /> <br />", "<br> <br> <br>"), "<br /> <br />", $text); // Optional
                    $text = addslashes($text); 
                    $text = stripslashes($text);
                    $text = strip_tags($text);
                    $check_text = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
                     
                    $no_spacing_error = 0;
                    $excessive_spacing_error = 0;
                    foreach($check_text as $word_key => $word) {
                        if (strlen($word) >= 30) { 
                            $no_spacing_error++;
                        } else if (strlen($word) == 1) { 
                            if (preg_match('/^[A-Za-z]+$/', $word)) {
                                $excessive_spacing_error++;
                            }
                        }
                    }
                    if ($no_spacing_error >= 30 || $excessive_spacing_error >= 150) {
                        return $this->apiResponse->sendResponse(400, 'Too many formatting issues.', $text);
                    } else {
                        $doc = new DOMDocument();
                        $doc->preserveWhiteSpace = false;
                        $doc->formatOutput = true;
                        $doc->simplexml_load_string($text, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
                        $doc->load($path, LIBXML_NOWARNING);
                        $xml = $doc->saveXML($doc->createTextNode($text));
                        // $data['file_path'] = "public/invoice/" . $fileName;
                        // $data['pdf_text'] = $text;
                    }
                } else {
                    return $this->apiResponse->sendResponse(400, 'No text extracted from PDF.', null);
                }
            } else {
                return $this->apiResponse->sendResponse(400, 'Parse File fns failed, not a PDF.', null);
            }
            DB::commit();
            return $this->apiResponse->sendResponse(200, 'PDF converted to XML successfully', $data);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->apiResponse->sendResponse(500, $e->getMessage(), $e->getTraceAsString());
        }
    }

    function clean_ascii_characters($string) {
        $string = str_replace(array('-', '–'), '-', $string);
        $string = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string);  
        return $string;
    }
}
