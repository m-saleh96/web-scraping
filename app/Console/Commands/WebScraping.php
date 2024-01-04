<?php

namespace App\Console\Commands;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;

class WebScraping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:web-scraping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $data = [];

        $client = new Client();

        $crawler = $client->request('GET', 'https://eg.opensooq.com/en');

        $crawler->filter('#headerThreeDesktop ul li ul li a')->each(function($liElement) use($client,&$data){
            $a = $liElement->first();
            $link = $a->link();
            $adpage = $client->click($link);

            $adpage->filter('#listing_posts div')->each(function($divElem) use($client,&$data) {

                $innerData=[];

                $a = $divElem->filter('a');
                if ($a->filter('.postDet')->count() > 0) {
                    $details = $a->filter('.postDet')->children();
                    $title = $details->eq(0)->text();
                    $info = $details->eq(1)->text();
                    $address = $details->eq(2)->filter('span')->text();
                    $price = $details->eq(3)->filter('.postPrice')->text();
                    $image = $a->filter('img')->attr('src');

                    $innerData['image'] = $image;
                    $innerData['title'] = $title;
                    $innerData['info'] = $info;
                    $innerData['address'] = $address;
                    $innerData['price'] = $price;

                    DB::table('data')->insert([
                        'image' => $image,
                        'title' => $title,
                        'info' => $info,
                        'address' => $address,
                        'price' => $price,
                        'created_at' =>now()
                    ]
                    );

                    $data[] = $innerData;

                }

            });

            // dd($data);


        });

    }
}
