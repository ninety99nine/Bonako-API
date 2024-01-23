<?php

namespace App\Services\AWS;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AWSService
{
    /**
     *  Store the specified file in Amazon S3
     *
     * @param string $folderName
     * @param \Illuminate\Http\File|\Illuminate\Http\UploadedFile $file
     * @return string
     */
    public static function store($folderName, $file)
    {
        /**
         *  Check if this file was UploadedFile on the request.
         *  Note that the getClientOriginalExtension() method only works for an UploadedFile
         */
        if ($file instanceof \Illuminate\Http\UploadedFile) {

            $fileName = Str::random(40).'.'.$file->getClientOriginalExtension();
            $path = $folderName.'/'.$fileName;

            Storage::disk('s3')->put($path, file_get_contents($file));

        //  Check if this file is an HtmlString e.g a generated QR Code
        } elseif ($file instanceof \Illuminate\Support\HtmlString) {

            // Assuming $file is an HtmlString representing the QR Code image
            $fileName = Str::random(30).time().'.png';  // Set the desired extension
            $path = $folderName.'/'.$fileName;

            // Convert the HtmlString to string and save it as a file
            $qrCodeContent = (string) $file;
            Storage::disk('s3')->put($path, $qrCodeContent);

        } else {

            // Handle the case when $file is neither an UploadedFile nor an HtmlString
            throw new \InvalidArgumentException('Invalid file type');

        }

        $awsUrl = AWSService::pathToUrl($path);

        return $awsUrl;
    }

    /**
     *  Delete the specified file in Amazon S3 using the specified URL
     *
     * @param string $url
     * @return bool
     */
    public static function delete($url)
    {
        if(self::exists($url)) {

            return Storage::disk('s3')->delete(AWSService::urlToPath($url));

        }else{

            return true;

        }
    }

    /**
     *  Check if the specified file in Amazon S3 exists using the specified URL
     *
     * @param string $url
     * @return bool
     */
    public static function exists($url)
    {
        return Storage::disk('s3')->exists(AWSService::urlToPath($url));
    }

    /**
     *  Generate the Amazon file URL using the specified path
     *
     *  @param array $path The path to the file e.g "logos/somelogo.png"
     *  @return string
     */
    public static function pathToUrl($path)
    {
        if( empty(config('app.AWS_DEFAULT_REGION')) ) {

            //  Throw an exception
            throw new \Exception('The AWS default region must be provided');

        }else if ( empty(config('app.AWS_BUCKET')) ) {

            //  Throw an exception
            throw new \Exception('The AWS bucket must be provided');

        }else if ( empty($path) ) {

            //  Throw an exception
            throw new \Exception('The file path must be provided');

        }else{

            /**
             *  Return the Amazon file URL e.g
             *
             *  "logos/somelogo.png" to "https://s3.eu-west-2.amazonaws.com/bonako/logos/somelogo.png"
             *
             *  In this example, the AWS_DEFAULT_REGION is "eu-west-2" and the AWS_BUCKET is "bonako"
             */

            //  Return the Amazon file URL
            return 'https://s3.'.config('app.AWS_DEFAULT_REGION').'.amazonaws.com/'.config('app.AWS_BUCKET').'/'.$path;

        }
    }

    /**
     *  Generate the Amazon file path using the specified URL
     *
     *  @param string $url The url to the file e.g "https://s3.eu-west-2.amazonaws.com/bonako/logos/somelogo.png"
     *  @return string
     */
    public static function urlToPath($url)
    {
        if ( empty(config('app.AWS_BUCKET')) ) {

            //  Throw an exception
            throw new \Exception('The AWS bucket must be provided');

        }else if ( empty($url) ) {

            //  Throw an exception
            throw new \Exception('The url must be provided');

        }else{

            /**
             *  Return the Amazon file path e.g
             *
             *  "https://s3.eu-west-2.amazonaws.com/bonako/logos/somelogo.png" to "logos/somelogo.png"
             *
             *  In this example, the AWS_BUCKET is "bonako"
             */
            return Arr::last(explode(config('app.AWS_BUCKET').'/', $url));

        }
    }
}
