<?php

namespace MAbbariki\SimpleCaptchaPhp;


class Captcha
{
    const CaptchaKeySession = "captcha_code";

    const NUMERIC_CAPTCHA = 1;
    const ALPHANUMERIC_CAPTCHA = 2;

    private $length;
    private $string;
    private $font;
    private $code;

    private $width = 400;
    private $height;

    /**
     * @param int $len
     * @param int $type
     */
    public function __construct(int $len = 4, int $type = self::NUMERIC_CAPTCHA)
    {
        define('ROOT_PATH', dirname(__DIR__) . '/SimpleCaptchaPhp/');

        $this->setLength($len);
        $this->setString($type);
        $this->setFont(ROOT_PATH . 'resource/fonts/CaptchaFont.ttf');
    }

    /**
     * @param $len
     * @return void
     * @throws \Exception
     */
    public function setLength($len)
    {
        if ($len > 10)
            throw new \Exception("Length must be smaller than 10");
        $this->length = $len;
    }

    /**
     * @throws \Exception
     */
    public function setFont($font)
    {
        if (!is_file($font))
            throw new \Exception("Font File can not be found, font : $font");
        $this->font = $font;
    }

    /**
     * @param $type
     * @return void
     */
    private function setString($type)
    {
        switch ($type) {
            default:
            case 1:
            {
                $this->string = '0123456789';
                break;
            }
            case 2:
            {
                $this->string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function getFinalCode()
    {
        if (is_null($this->code))
            throw new \Exception("there is no code, you should build the image first");

        return $this->code;
    }

    /**
     * return base64 of a randomly created image
     */
    public function buildImage($ratio = 0.5)
    {
        if ($ratio > 2 || $ratio <= 0)
            throw new \Exception("Ratio should be between 0 and 2");
        $this->height = $this->width * $ratio; //set height of picture based on ration
        $this->setCode(); // set the code used for processing
        $fontSize = $this->computeFontSize(); // compute font size based on length
        $fontSizeInPt = $this->computeFontSizePt($fontSize); // compute font size in points
        $angel = 0; //set the anger of text printed on image

        //start building image
        ob_start();
        $im = imagecreate($this->width, $this->height);
        if ($im === false)
            throw new \Exception("Cannot Initialize new GD image stream");

        //color BG
        imagefill($im, 0, 0, imagecolorallocate($im, 223, 230, 233));

        $colors = [
            imagecolorallocate($im, 45, 52, 54),
            imagecolorallocate($im, 111, 30, 81),
            imagecolorallocate($im, 27, 20, 100),
            imagecolorallocate($im, 214, 48, 49),
            imagecolorallocate($im, 234, 32, 39),
            imagecolorallocate($im, 0, 0, 0)
        ];

        // choose color for each object
        // remove the color from array so the dots and lines won't be same color
        shuffle($colors);
        $text_color = array_pop($colors) ?? imagecolorallocate($im, 0, 0, 0);
        shuffle($colors);
        $line_color = array_pop($colors);
        shuffle($colors);
        $pixel_color = array_pop($colors);

        list($x, $y) = $this->findCenter($fontSize, $angel); // find the center of image for text

        //print The Code
        foreach (str_split($this->code) as $k => $item)
            imagettftext($im, $fontSizeInPt, $angel, $x + $k * 35, $y, $text_color, $this->font, $item);


        for ($i = 0; $i < rand(5, 10); $i++) //print lines
            imageline($im, 0, rand(1, 1000) % $this->height, $this->width, rand(1, 1000) % $this->height, $line_color);

        for ($i = 0; $i < rand(500, 1000); $i++) // print dots
            imagesetpixel($im, rand() % $this->width, rand() % $this->height, $pixel_color);

        imagepng($im);
        imagedestroy($im);
        $data = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64, ' . base64_encode($data);
    }

    private function setCode()
    {
        $shuffle = str_shuffle($this->string);
        $this->code = substr($shuffle, 0, $this->length);
    }

    private function findCenter($size, $angel)
    {
        $text = implode(" ", str_split($this->code)); // add space between code chars for better sizing

        $box = ImageTTFBBox($size, $angel, $this->font, $text); // find the size of the text

        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));

        // compute center
        $x = intval(($this->width - $xr) / 2);
        $y = intval(($this->height + $yr) / 2);

        return [$x, $y];
    }

    private function computeFontSize()
    {
        $fontSize = 30;
        return $fontSize - $this->length;
    }

    private function computeFontSizePt($pixel)
    {
        return (3 / 4) * $pixel;
    }
}