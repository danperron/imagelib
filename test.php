<?php

use danperron\imagelib\Image;

$image = Image::load('testImage.png')->scaleWidth(150)->save('testImage_thumb.png');