<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Html2Text\Html2Text;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function show(Request $request)
    {
        $url = $request->input('url');
        $text = '';
        $categories = [];

        if (!empty($url)) {
            try {
                $response = Http::get($url);
                $html = new Html2Text($response->body(), ['do_links' => 'none']);
                $text = trim($html->getText());

                $response = Http::post('https://default.api.witty.works/v1.1/check', [
                    'text' => $text,
                ]);

                $json = $response->json();
                if (isset($json['results'])) {
                    foreach ($response->json()['results'] as $result) {
                        $categories[$result['category']][$result['subcategory']] = isset($categories[$result['category']][$result['subcategory']])
                            ? ($categories[$result['category']][$result['subcategory']] + 1) : 1;
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $data = [
            'url' => $url,
            'text' => $text,
            'categories' => $categories,
        ];

        return view('scanner', $data);
    }

    protected function strip_tags_content($string)
    {
        // ----- remove HTML TAGs ----- 
        $string = preg_replace('/<[^>]*>/', ' ', $string);
        // ----- remove control characters ----- 
        $string = str_replace("\r", '', $string);
        $string = str_replace("\n", ' ', $string);
        $string = str_replace("\t", ' ', $string);
        // ----- remove multiple spaces ----- 
        $string = trim(preg_replace('/ {2,}/', ' ', $string));
        return $string;
    }
}
