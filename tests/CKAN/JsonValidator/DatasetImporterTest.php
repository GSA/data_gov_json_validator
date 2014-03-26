<?php
/**
 * @author vasili4
 * @date   3/21/14
 */

namespace CKAN\JsonValidator;


/**
 * Class DatasetImporterTest
 * @package CKAN\JsonValidator
 */
class DatasetImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DatasetImporter $dataset_importer
     */
    private $dataset_importer;

    public function testCleaner()
    {
        $testFile = DATA_DIR . '/test.json';
        $this->assertFalse(is_file($testFile));
        file_put_contents($testFile, 'test');
        $this->assertTrue(is_file($testFile));
        $this->dataset_importer->cleaner();
        $this->assertFalse(is_file($testFile));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->dataset_importer = new DatasetImporter();
    }


}
 