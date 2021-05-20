<?php

namespace App\Http\Controllers;

use App\AssetCollectionFactory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ImageController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function getImage($collectionId,$tokenId)
    {


        $collectionFactory = new AssetCollectionFactory();
        $collectionFactory->populateLocal();
        $collectionEntity = $collectionFactory->get($collectionId);

        dd($collectionEntity);




            header('Location: https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png');
            exit;


    }

}
