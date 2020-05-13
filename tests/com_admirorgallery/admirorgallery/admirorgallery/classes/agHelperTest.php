<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class agHelperTest extends TestCase {

    /**
     * @dataProvider osProvider
     */
    public function testAgGetOs($expected, $given) {
        $this->assertEquals(
                $expected,
                agHelper::ag_get_os_($given)
        );
    }

    public function osProvider() {
        return array(
            array('Windows 3.11', 'Win16'),
            array('Windows 95', '(Windows 95)|(Win95)|(Windows_95)'),
            array('Windows 98', '(Windows 98)|(Win98)'),
            array('Windows 2000', '(Windows NT 5.0)|(Windows 2000)'),
            array('Windows XP', '(Windows NT 5.1)|(Windows XP)'),
            array('Windows 2003', '(Windows NT 5.2)'),
            array('Windows NT 4.0', '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)'),
            array('Windows ME', 'Windows ME'),
            array('Open BSD', 'OpenBSD'),
            array('Sun OS', 'SunOS'),
            array('Linux', '(Linux)|(X11)'),
            array('Macintosh', '(Mac_PowerPC)|(Macintosh)'),
            array('QNX', 'QNX'),
            array('BeOS', 'BeOS'),
            array('OS\/2', 'OS/2'),
            array('Search Bot', '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp\/cat)|(msnbot)|(ia_archiver)')
        );
    }

    /**
     * @dataProvider fileNameProvider
     */
    public function testRemoveExtension($given, $expected) {
        $this->assertEquals(agHelper::ag_removExtension($given), $expected);
    }

    public function fileNameProvider() {
        return array(
            array('test.jpg', 'test'),
            array('test.jpg', 'test'),
            array('test.test.jpg', 'test.test'),
            array('test.jpg', 'test')
        );
    }

}
