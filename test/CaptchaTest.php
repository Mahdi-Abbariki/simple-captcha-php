<?php

use MAbbariki\SimpleCaptchaPhp\Captcha;
use PHPUnit\Framework\TestCase;

class CaptchaTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     * @test
     */
    public function getimagecaptchaTest()
    {
        $len = 4;
        $captcha = new Captcha($len);
        $image = $captcha->buildImage();//remove data:image/png;base64, (23 char) from first of string to see if it is base64
        $code = $captcha->getFinalCode();
        $this->assertTrue(self::is_base64(substr($image, 23)));
        $this->assertEquals("4", strlen($code));
    }

    private static function is_base64($s)
    {
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) return false;

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($s, true);
        if (false === $decoded) return false;

        // Encode the string again
        if (base64_encode($decoded) != $s) return false;

        return true;
    }

}