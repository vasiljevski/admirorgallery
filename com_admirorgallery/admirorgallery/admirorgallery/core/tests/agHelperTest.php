<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;

include_once '../agHelper.php';

class agHelperTest extends TestCase
{
    /**
     * @dataProvider fileRoundSizeProvider
     */
    public function testAg_fileRoundSize($expected, $given)
    {
        $this->assertEquals(
            $expected,
            agHelper::ag_fileRoundSize($given)
        );
    }

    public function fileRoundSizeProvider()
    {
        return array(
            array('1 KB', 1028),
            array('1 MB', 1048579),
            array('10 KB', 10240)
        );
    }

    /**
     * @dataProvider osProvider
     */
    public function testAgGetOs($expected, $given)
    {
        $this->assertEquals(
            $expected,
            agHelper::ag_get_os_($given)
        );
    }

    public function osProvider()
    {
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
            array('Search Bot', '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp\/cat)|(msnbot)|(ia_archiver)'),
            array('Unknown', 'Mozilla/4.0 (compatible; MSIE 6.0; Mobile)')
        );
    }

    /**
     * @dataProvider shrinkStringProvider
     */
    public function testAg_shrinkString($expected, $given)
    {
        $this->assertEquals(
            $expected,
            agHelper::ag_shrinkString($given[0], $given[1], isset($given[2]) ? $given[2] : '...')
        );
    }

    public function shrinkStringProvider()
    {
        return array(
            array('Test;;;', array('Test 123', 4, ';;;')),
            array('Test...', array('Test 123', 4)),
            array('Test 123', array('Test 123', 50))
        );
    }

    public function testAg_cleanThumbsFolder()
    {

    }

    public function testAg_remote_exists()
    {

    }

    public function testAg_mkdir_recursive()
    {

    }

    public function testAg_sureRemoveDir()
    {

    }

    public function testAg_clearOldThumbs()
    {

    }

    public function testAg_imageInfo()
    {

    }

    public function testAg_foldersArrayFromFolder()
    {

    }

    public function testAg_getExtension()
    {

    }

    public function testAg_indexWrite()
    {

    }

    public function testAg_removeExtension()
    {

    }

    public function testAg_imageArrayFromFolder()
    {

    }

    public function testAg_createThumb()
    {

    }

    public function testAg_foregroundColor()
    {

    }

    public function testArray_sorting()
    {

    }

    /// ag_foregroundColor
    public function testLightColor()
    {
        $this->assertEquals('ffffff', agHelper::ag_foregroundColor('f0f0f0', 20));
    }

    public function testDarkColor()
    {
        $this->assertEquals('000000', agHelper::ag_foregroundColor('101010', -20));
    }

    public function testZeroAdjustment()
    {
        $this->assertEquals('ff0000', agHelper::ag_foregroundColor('ff0000', 0));
        $this->assertEquals('00ff00', agHelper::ag_foregroundColor('00ff00', 0));
        $this->assertEquals('0000ff', agHelper::ag_foregroundColor('0000ff', 0));
    }

    public function testBasicColors()
    {
        $this->assertEquals('ff1414', agHelper::ag_foregroundColor('ff0000', 20));
        $this->assertEquals('14ff14', agHelper::ag_foregroundColor('00ff00', 20));
        $this->assertEquals('1414ff', agHelper::ag_foregroundColor('0000ff', 20));
    }

    public function testNegativeAdjustment()
    {
        $this->assertEquals('eb0000', agHelper::ag_foregroundColor('ff0000', -20));
        $this->assertEquals('00e100', agHelper::ag_foregroundColor('00ff00', -30));
        $this->assertEquals('0000cd', agHelper::ag_foregroundColor('0000ff', -50));
    }

    public function testLargeAdjustment()
    {
        $this->assertEquals('ffffff', agHelper::ag_foregroundColor('f0f0f0', 255));
        $this->assertEquals('000000', agHelper::ag_foregroundColor('101010', -255));
    }

    public function testAgImageInfo()
    {
        $imageURL = 'https://via.placeholder.com/150';
        $imageInfo = agHelper::ag_imageInfo($imageURL);

        $this->assertNotNull($imageInfo);
        $this->assertArrayHasKey('width', $imageInfo);
        $this->assertArrayHasKey('height', $imageInfo);
        $this->assertArrayHasKey('type', $imageInfo);
        $this->assertArrayHasKey('size', $imageInfo);

        $this->assertEquals(150, $imageInfo['width']);
        $this->assertEquals(150, $imageInfo['height']);
        $this->assertEquals('PNG', $imageInfo['type']);
        $this->assertEquals(filesize($imageURL), $imageInfo['size']);

        $imageURL = 'https://via.placeholder.com/300.jpg';
        $imageInfo = agHelper::ag_imageInfo($imageURL);

        $this->assertNotNull($imageInfo);
        $this->assertArrayHasKey('width', $imageInfo);
        $this->assertArrayHasKey('height', $imageInfo);
        $this->assertArrayHasKey('type', $imageInfo);
        $this->assertArrayHasKey('size', $imageInfo);

        $this->assertEquals(300, $imageInfo['width']);
        $this->assertEquals(300, $imageInfo['height']);
        $this->assertEquals('JPG', $imageInfo['type']);
        $this->assertEquals(filesize($imageURL), $imageInfo['size']);

        $imageURL = 'https://via.placeholder.com/400.gif';
        $imageInfo = agHelper::ag_imageInfo($imageURL);

        $this->assertNotNull($imageInfo);
        $this->assertArrayHasKey('width', $imageInfo);
        $this->assertArrayHasKey('height', $imageInfo);
        $this->assertArrayHasKey('type', $imageInfo);
        $this->assertArrayHasKey('size', $imageInfo);

        $this->assertEquals(400, $imageInfo['width']);
        $this->assertEquals(400, $imageInfo['height']);
        $this->assertEquals('GIF', $imageInfo['type']);
        $this->assertEquals(filesize($imageURL), $imageInfo['size']);

        $imageURL = 'https://via.placeholder.com/500.bmp';
        $imageInfo = agHelper::ag_imageInfo($imageURL);

        $this->assertNotNull($imageInfo);
        $this->assertArrayHasKey('width', $imageInfo);
        $this->assertArrayHasKey('height', $imageInfo);
        $this->assertArrayHasKey('type', $imageInfo);
        $this->assertArrayHasKey('size', $imageInfo);

        $this->assertEquals(500, $imageInfo['width']);
        $this->assertEquals(500, $imageInfo['height']);
        $this->assertEquals('BMP', $imageInfo['type']);
        $this->assertEquals(filesize($imageURL), $imageInfo['size']);
    }

    public function testFileRoundSize()
    {
        $this->assertSame('1 B', agHelper::ag_fileRoundSize(1));
        $this->assertSame('1023 B', agHelper::ag_fileRoundSize(1023));
        $this->assertSame('1 KB', agHelper::ag_fileRoundSize(1024));
        $this->assertSame('1.5 KB', agHelper::ag_fileRoundSize(1536));
        $this->assertSame('1.98 MB', agHelper::ag_fileRoundSize(2076189));
        $this->assertSame('1 GB', agHelper::ag_fileRoundSize(1073741824));
        $this->assertSame('3.5 GB', agHelper::ag_fileRoundSize(3.5 * 1024 * 1024 * 1024));
        $this->assertSame('5.2 GB', agHelper::ag_fileRoundSize(5583457484));
    }
}
