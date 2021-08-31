<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/*
|--------------------------------------------------------------------------
| StoreImage Trait
|--------------------------------------------------------------------------
|
| This trait will be used for Save Images.
|
*/

trait StoreImage
{
    /**
     * Return a success JSON response.
     *
     * @param object $file
     * @param string $directory
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    protected function save(object $file, string $directory, int $width = null, int $height = null): string
    {
        $save_path = public_path('storage/' . $directory . '/' . Str::random(40) . '.jpg');

        if (!File::exists(public_path('storage/' . $directory))) {
            Storage::makeDirectory('/public/' . $directory);
        }

        # make original image instance
        $img = Image::make($file);

        #resize image
        if ($width && $height) {
            $img->fit($width, $height);
        }

        # save original or resized image
        $img->save($save_path);

        return $save_path;
    }
}
