<?php
/**
 * @author Alex Perfilov
 * @date   2/27/14
 */

namespace CKAN\JsonValidator;


use CKAN\Core\CkanClient;
use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

/**
 * Class JsonValidator
 * @package CKAN\JsonValidator
 */
class JsonValidator
{
    private $packageSearchPerPage = 200;

    private $titlesPerSearch = 1;

    private $Ckan;

    /**
     *
     */
    public function __construct()
    {
        $this->Ckan = new CkanClient(CKAN_API_URL);
    }

    public function clear()
    {
        is_file(RESULTS_LOG) && unlink(RESULTS_LOG);

        foreach (glob(RESULTS_DIR_YMD . '/*.csv') as $dataset) {
            unlink($dataset);
        }

        foreach (glob(RESULTS_DIR_YMD . '/*.json') as $dataset) {
            unlink($dataset);
        }
    }

    /**
     * @param $data_file_path
     * @param $schema_file_path
     * @param bool $search_enabled
     */
    public function validate($data_file_path, $schema_file_path, $search_enabled = false)
    {
        $basename = basename($data_file_path);
        error_log(str_pad($basename, 70, ' . '), 3, RESULTS_LOG);
        // Get the schema and data as objects
        $retriever = new UriRetriever();

        //get data
        $data_string = file_get_contents($data_file_path);
        $data_array  = json_decode($data_string);

        $success         = false;
        $json_file_error = 'Input JSON file could not be decoded: ';

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $success = true;
                break;
            case JSON_ERROR_DEPTH:
                error_log($json_file_error . ' - Maximum stack depth exceeded' . PHP_EOL, 3, RESULTS_LOG);
                break;
            case JSON_ERROR_STATE_MISMATCH:
                error_log($json_file_error . ' - Underflow or the modes mismatch' . PHP_EOL, 3, RESULTS_LOG);
                break;
            case JSON_ERROR_CTRL_CHAR:
                error_log($json_file_error . ' - Unexpected control character found' . PHP_EOL, 3, RESULTS_LOG);
                break;
            case JSON_ERROR_SYNTAX:
                error_log($json_file_error . ' - Syntax error, malformed JSON' . PHP_EOL, 3, RESULTS_LOG);
                break;
            case JSON_ERROR_UTF8:
                error_log($json_file_error . ' - Malformed UTF-8 characters, possibly incorrectly encoded' . PHP_EOL, 3, RESULTS_LOG);
                break;
            default:
                error_log($json_file_error . ' - Unknown error' . PHP_EOL, 3, RESULTS_LOG);
                break;
        }

        if (!$success) {
            return;
        }

        if (!is_array($data_array)) {
            error_log('Input JSON is not an ARRAY - ' . PHP_EOL, 3, RESULTS_LOG);

            return;
        }

        //get schema
        $schema = $retriever->retrieve('file://' . $schema_file_path);

        // If you use $ref or if you are unsure, resolve those references here
        // This modifies the $schema object
        $refResolver = new RefResolver($retriever);
        $refResolver->resolve($schema, 'file://' . __DIR__);
        $json_validation_results = [];

        $valid   = $invalid = 0;
        $counter = [];

        foreach ($data_array as $data) {
            $id = md5($data->identifier . $data->title);
            if (isset($counter[$id])) {
                echo PHP_EOL . "Duplicate found:" . PHP_EOL;
                echo '(' . $data->identifier . ')' . $data->title . PHP_EOL;
            }
            $counter[$id] = 1;

            // Validate
            $validator = new Validator();
            $validator->check($data, $schema);
            $json_validation_results[$id]["Title"]      = $data->title;
            $json_validation_results[$id]["identifier"] = $data->identifier;
            if ($validator->isValid()) {
                $json_validation_results[$id]['Valid'] = true;
                $valid++;
            } else {
                $invalid++;
                $json_validation_results[$id]['Valid'] = false;
                foreach ($validator->getErrors() as $error) {
                    $json_validation_results[$id]['Errors'][] = $error;
                }
            }
        }

        $json_total_results = json_encode($json_validation_results, JSON_PRETTY_PRINT);
        $resultsFile        = str_replace('.json', '_results.json', $basename);
        file_put_contents(RESULTS_DIR_YMD . '/' . $resultsFile, $json_total_results);
        echo str_pad($basename, 70, ' . ');

        $percent = ceil($valid / ($valid + $invalid) * 100);
        echo $out = '|' . str_pad("$percent% |", 7, ' ', STR_PAD_LEFT)
            . str_pad("$valid valid |", 13, ' ', STR_PAD_LEFT)
            . str_pad("$invalid invalid |", 15, ' ', STR_PAD_LEFT) . PHP_EOL;

        error_log($out, 3, RESULTS_LOG);
//    print_r($json_total_results);

        //search CKAN.
        if ($search_enabled) {
            $this->ckan_search($data_array, $json_validation_results, $basename);
        }
    }

    /**
     * @param $data_array
     * @param $json_validation_results
     * @param $basename
     * @throws \Exception
     */
    private function ckan_search($data_array, $json_validation_results, $basename)
    {
        $resultsFile = str_replace('.json', '_results.csv', $basename);
        $fp          = fopen(RESULTS_DIR_YMD . '/' . $resultsFile, 'w');

        $csv_header = [
            'Dataset Title in JSON',
            'Access URL in JSON',
            'Valid to POD Schema',
            'Number of Errors',
            'Number of Matches in catalog.data.gov',
            'Access URL match flag',
            'Is Geospatial',
            'Categories',
            'catalog.data.gov URLs for matches found',
        ];

        fputcsv($fp, $csv_header);

        /**
         * Split json data to sets of titlesPerSearch , for speed-up script running by
         * reducing count CKAN request, grouping them together
         */
        $data_chunks = array_chunk($data_array, $this->titlesPerSearch);

        $totalTitles         = sizeof($data_array);
        $currentTitleCounter = 0;

        foreach ($data_chunks as $data_chunk) {
//        prepare solr search request
            $titles = $original_titles = $jsonDatasetFound = [];
            foreach ($data_chunk as $jsonDataset) {
                $original_titles[] = $jsonDataset->title;
                $titles[]          = '(\"' . $this->escapeSolrValue($jsonDataset->title) . '\")';
            }

            $titles = array_unique($titles);

            $title = '(' . join(' OR ', $titles) . ')';

            $csv_result_urls                     = [];
            $csv_result_urls_access_url_no_match = [];
            $csv_is_geospatial                   = [];
            $csv_categories                      = [];
            $csv_access_url_match                = [];
            $csv_number_of_matches               = [];
            $csv_no_match                        = [];

            /**
             * Preparing csv results containers
             */
            foreach ($data_chunk as $index => $jsonDataset) {
                $csv_result_urls[$index]                     = '';
                $csv_result_urls_access_url_no_match[$index] = '';
                $csv_is_geospatial[$index]                   = 'na';
                $csv_categories[$index]                      = 'na';
                $csv_access_url_match[$index]                = 'no';
                $csv_number_of_matches[$index]               = 0;
                $csv_no_match[$index]                        = [];
            }

            $page = 0;
            while (true) {
                $start = $page++ * $this->packageSearchPerPage;

                /**
                 * hack for temporary CKAN problems, e.g. 500 server errors,
                 * let's just wait 3 seconds and retry our request ;)
                 * only 5 retries allowed, we don't want to be banned by CKAN
                 */
                $try = $ckanResult = 0;
                while (true) {
                    try {
                        $ckanResult = $this->Ckan->package_search('title:' . $title, $this->packageSearchPerPage, $start, 'fq');
                    } catch (\Exception $ex) {
                        if ($try++ > 5) {
                            throw $ex;
                        } else {
                            echo "RETRY title:" . $title . PHP_EOL;
                            sleep(3);
                            continue;
                        }
                    }
                    break;
                }


                if ($start > $this->packageSearchPerPage) {
                    echo PHP_EOL . 'Multi page search start from ' . $start . ' for request:' . PHP_EOL . $title . PHP_EOL . PHP_EOL;
                }

                $ckanResult = json_decode($ckanResult, true); //  decode json as array

                if (!isset($ckanResult['result'])) {
                    throw new \Exception('empty reply from ckan');
                    break;
                }

                $ckanResult = $ckanResult['result'];

                $count = $ckanResult['count'];

                if (!$count) {
                    break;
                }

                $sz = sizeof($ckanResult['results']);

                if (!$sz) {
                    break;
                }

                foreach ($data_chunk as $index => $jsonDataset) {
//                    boost
                    if (in_array($jsonDataset->title, $jsonDatasetFound)) {
                        continue;
                    }
                    if (!($currentTitleCounter++ % 50)) {
                        echo $currentTitleCounter . " of $totalTitles" . PHP_EOL;
                    }

                    //number of results
                    $number_of_results = sizeof($ckanResult['results']);

                    //If one or more matches found then try to match access URL
                    if ($number_of_results) {
                        foreach ($ckanResult['results'] as $ckanDataset) {
                            if (trim($jsonDataset->title) !== trim($ckanDataset['title'])) {
//                            if the CKAN has another title, we skip it
                                continue;
                            }
                            $csv_number_of_matches[$index]++;

                            if (isset($ckanDataset['extras'])) {
                                if (strpos(json_encode($ckanDataset['extras']), '"geospatial"')) {
                                    $csv_is_geospatial[$index] = 'true';
                                }
                            }

                            $accessUrl = isset($jsonDataset->accessURL) ? trim($jsonDataset->accessURL) : false;

                            if (!sizeof($csv_no_match[$index])) {
                                $csv_categories[$index] = $this->ckan_extract_category_tags($ckanDataset);
                            }

                            $dataset_dump = print_r($ckanDataset, true);
                            if ($accessUrl && strstr($dataset_dump, $accessUrl)) {
                                $csv_result_urls[$index]      = "http://catalog.data.gov/dataset/" . $ckanDataset['name'];
                                $csv_access_url_match[$index] = 'yes';

//                                boost
                                $jsonDatasetFound[] = $jsonDataset->title;
                                break 2; //  skip next search : we already found our result
                            } else {
                                $csv_no_match[$index][] = 'http://catalog.data.gov/dataset/' . $ckanDataset['name'];
                            }
                        }
                    }
                }

                if (sizeof($jsonDatasetFound) == sizeof($data_chunk)) {
                    break;
                }

                if (($ckanResult['count'] - $this->packageSearchPerPage < $start) || ($sz < $this->packageSearchPerPage)) {
                    break;
                }
            }

            /**
             * Writing CSV results for current data set
             */
            foreach ($data_chunk as $index => $jsonDataset) {
                $url_match = ('yes' == $csv_access_url_match[$index]);

                if ('na' == $csv_is_geospatial[$index] && $csv_number_of_matches[$index] && $url_match) {
                    $csv_is_geospatial[$index] = 'false';
                }

                if (sizeof($csv_no_match[$index])) {
                    $csv_result_urls_access_url_no_match[$index] = join(' | ', $csv_no_match[$index]);
                }

                $csv_line        = [];
                $csv_line[]      = $jsonDataset->title;
                $distributionUrl = (isset($jsonDataset->distribution) && is_array($jsonDataset->distribution)
                    && isset($jsonDataset->distribution[0]->accessURL)) ? $jsonDataset->distribution[0]->accessURL : false;
                $csv_line[]      = isset($jsonDataset->accessURL) ? $jsonDataset->accessURL : ($distributionUrl ? : '');

                $id = md5($jsonDataset->identifier . $jsonDataset->title);

                $csv_line[] = $json_validation_results[$id]['Valid'] ? 'true' : 'false';

                if (isset($json_validation_results[$id]['Errors']) && is_array($ers = $json_validation_results[$id]['Errors'])) {
                    $csv_line[] = sizeof($ers);
                } else {
                    $csv_line[] = 0;
                }

                $csv_line[] = $csv_number_of_matches[$index];
                $csv_line[] = $csv_access_url_match[$index];
                $csv_line[] = $csv_is_geospatial[$index];
                $csv_line[] = $csv_categories[$index];
                $csv_line[] = $csv_result_urls[$index] ? : $csv_result_urls_access_url_no_match[$index];

                fputcsv($fp, $csv_line);
            }
        }

        fclose($fp);
    }

    /**
     * @param $dataset
     * @internal param $dataset_url
     * @return array|string
     */
    private function ckan_extract_category_tags($dataset)
    {
        if (!isset($dataset['groups']) || !is_array($dataset['groups']) || !sizeof($groups = $dataset['groups'])) {
            return 'na';
        }

        $return = [];

        $extras = isset($dataset['extras']) ? $dataset['extras'] : [];

        foreach ($groups as $group) {
            $category_id = $group['id'];
            $tag_key     = '__category_tag_' . $category_id;
            $return[$group['title']] = '';
            foreach ($extras as $extra) {
                if ($tag_key == $extra['key']) {
                    $return[$group['title']] = json_decode($extra['value']);
                }
            }
        }

        if (!sizeof($return)) {
            return 'na';
        }

        return json_encode($return);
    }

    /**
     * @param $string
     * @return mixed
     */
    private function escapeSolrValue($string)
    {
        $string = preg_replace('/[\W]+/', ' ', $string);

        return $string;
    }
}