# danperron/imagelib

## A simple Image manipulation library for PHP



sample usage - squarifying an image and resizing to 200x200

    use danperron\imagelib\Image;

    $image = Image::load('testImage.png')->squarify()
            ->scale(200, 200)
            ->save('testImage_squared.png');


sample usage - resizing image to width of 500 while maintaining aspect ratio

    use danperron\imagelib\Image;

    $image = Image::load('testImage.png')->scaleWidth(150)->save('testImage_thumb.png');