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
}
