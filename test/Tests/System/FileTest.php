<?php
use PHPUnit\Framework\TestCase;


/**
 * Main test group
 */
class TestsSystemFile extends TestCase
{

    private static $resources = 'Resources{{DS}}FileTest';



    public static function setUpBeforeClass()
    {
        self::$resources = TESTPATH . self::$resources;
        self::$resources = str_replace('{{DS}}', DS, self::$resources);
    }


    /**
     * Test File::ls
     *
     * @group   mamuph.system.file
     */
    public function test_ls()
    {


        // Check right number of files are returned
        $total_files = count(File::ls(self::$resources));
        $this->assertEquals(6, $total_files);

        // Check if right files are returned when hidden directories are excluded
        $files = File::ls(self::$resources, true, File::EXCLUDE_BLOCK | File::EXCLUDE_HIDDEN);

        foreach ($files as $file) {
            $this->assertFalse($file[0] === '.');
            $this->assertFileExists($file);
        }


        // Check if right directories are returned files are retrieved recursively
        $dirs = File::ls(self::$resources, true, File::EXCLUDE_BLOCK | File::LIST_RECURSIVE | File::EXCLUDE_FILES);

        $this->assertEquals(6, count($dirs));

        foreach ($dirs as $dir)
            $this->assertTrue(is_dir($dir));

    }


    /**
     * Test File::xcopy, File::ls and File::deltree
     *
     * @group   mamuph.system.file
     */
    public function test_xcopy()
    {

        // Take care when the $temp parameter is changed
        $temp = self::$resources . DS . '..' . DS . 'temp';

        mkdir($temp);

        // Copy files recursively
        $this->assertTrue(File::xcopy(self::$resources . DS . '*', $temp, 0755));

        // Check files
        $source_files = File::ls(self::$resources, false, File::EXCLUDE_BLOCK | File::LIST_RECURSIVE);
        $dest_files = File::ls($temp, false, File::EXCLUDE_BLOCK | File::LIST_RECURSIVE);

        $this->assertEquals($source_files, $dest_files);

        // Remove temporal folder
        $dest_files = File::ls($temp, true, File::EXCLUDE_BLOCK | File::LIST_RECURSIVE);
        $this->assertTrue(File::delTree($temp, true));

        foreach ($dest_files as $dest_file)
            $this->assertFalse(file_exists($dest_file));

    }


    /**
     * Test File::home
     *
     * @group   mamuph.system.file
     */
    public function test_home()
    {
        // Test if home directory can be detected
        $home_dir = File::home();
        $this->assertTrue(file_exists($home_dir) && is_dir($home_dir));
    }


}