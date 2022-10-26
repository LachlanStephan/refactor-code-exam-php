<?php

namespace Squiz\PhpCodeExam;

require_once __DIR__ . '/../mocks/TestData.php';

use Squiz\PhpCodeExam\Mocks\TestData;

class Searcher
{
    public $allData = [];

    public function __construct()
    {
        /**
         * We just assume that we get all of this data from the DB
         * in a reasonably quick way
         */
        $this->allData = (new TestData())->getFromDbMock();
    }

    // get term related to contents
    // term = search term e.g. yellow
    private function executeContents($term)
    {
        $result = [];
        foreach($this->allData as $key => $data) {
            if (!isset($data['content'])) {
                continue;
            }

            $content = $data['content'];

            if (strrpos($content, strtolower($term)) > 0) {
                array_push($result, $data);
            } 
        }
        return empty($result) ? false : $result;
    }

    // get term related to tags 
    // term = search term e.g. galaxy
    private function executeTags($term)
    {
        $result = [];
        foreach($this->allData as $key => $data) {
            if (!isset($data['tags'])) {
                continue;
            }

            $tags = $data['tags'];

            if (in_array($term, $tags)) {
                array_push($result, $data);
            }
        }
        return empty($result) ? false : $result;
    }

    public function execute($term, $type)
    {
        if ($type === 'content') {
            return $this->executeContents($term);
        }
        if ($type === 'tags') {
            return $this->executeTags($term);
        }
    }

    public function getPageById($id)
    {
        $pageIds = array_column($this->allData, 'id');
        $key = array_search($id, $pageIds);
        if ($key) {
            return $this->allData[$key];
        }
    }
}
