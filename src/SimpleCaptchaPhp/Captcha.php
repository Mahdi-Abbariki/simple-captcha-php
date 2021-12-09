<?php

namespace MAbbariki\SimpleCaptchaPhp;


class Captcha
{
    const CaptchaKeySession = "captcha_code";

    const NUMERIC_CAPTCHA = 1;
    const ALPHANUMERIC_CAPTCHA = 2;

    private $length;
    private $string;
    private $font = 'resource/fonts/CaptchaFont.ttf';
    private $code;

    /**
     * @param int $len
     * @param int $type
     */
    public function __construct(int $len = 4, int $type = self::NUMERIC_CAPTCHA)
    {
        $this->length = $len;
        $this->string = self::getString($type);
    }

    public function setFont($font)
    {
        if (is_file($font))
            $this->font = $font;
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
    public function buildImage($width = 400, $height = 200)
    {
        ob_start();
        $im = @imagecreate($width, $height)
        or die("Cannot Initialize new GD image stream");

        //color BG
        imagefill($im, 0, 0, imagecolorallocate($im, 223, 230, 233));


        $code = $this->getCode();
        $fontSize = 18;
        $angel = 0;
        $colors = [
            imagecolorallocate($im, 45, 52, 54),
            imagecolorallocate($im, 111, 30, 81),
            imagecolorallocate($im, 27, 20, 100),
            imagecolorallocate($im, 214, 48, 49),
            imagecolorallocate($im, 234, 32, 39),
            imagecolorallocate($im, 0, 0, 0)
        ];

        list($x, $y) = $this->findCenter($im, $code, $this->font, $fontSize, $angel);
        $x -= 50;

        //write The Code
        shuffle($colors);
        $text_color = $colors[0];
        foreach (str_split($code) as $k => $item) {
            imagettftext($im, rand(35, 50), $angel, $x, $y, $text_color ?? imagecolorallocate($im, 0, 0, 0), $this->font, $item);
            $x += 50;
        }

        shuffle($colors);
        $line_color = array_pop($colors);
        shuffle($colors);
        $pixel_color = array_pop($colors);
        for ($i = 0; $i < rand(5, 10); $i++)
            imageline($im, 0, rand() % $height, $width, rand() % $height, $line_color);
        for ($i = 0; $i < rand(500, 1000); $i++)
            imagesetpixel($im, rand() % $width, rand() % $height, $pixel_color);


        imagepng($im);
        imagedestroy($im);
        $data = ob_get_contents();
        ob_end_clean();


        return 'data:image/png;base64, ' . base64_encode($data);
    }

    private function getCode(): string
    {
        $shuffle = str_shuffle($this->string);
        $str = substr($shuffle, 0, $this->length);
        $this->code = $str;
        return $str;
    }

    private function findCenter($image, $text, $font, $size, $angel)
    {

        // find the size of the image
        $xi = ImageSX($image);
        $yi = ImageSY($image);

        // find the size of the text
        $box = ImageTTFBBox($size, $angel, $font, $text);

        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));

        // compute centering
        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi + $yr) / 2);

        return [$x, $y];
    }

    private static function getString($type): string
    {
        switch ($type) {
            default:
            case 1:
            {
                return '0123456789';
            }
            case 2:
            {
                return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }
        }
    }

}