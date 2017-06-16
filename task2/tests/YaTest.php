<?php

/**
 * Created by PhpStorm.
 * User: anton.surma
 * Date: 15.06.2017
 * Time: 10:51
 */

namespace My;

use Lmc\Steward\Test\AbstractTestCase;
use My\Pages\YandexVideoPage;

class YaTest extends AbstractTestCase
{

    protected $page;
    protected $testData;

    /**
     * @before
     */
    public function init()
    {
        $this->page = new YandexVideoPage($this);
        $this->testData = new YaTestTestData();
        if ($this->wd->get($this->testData::INITIAL_URL))
            $this->log("1. Open the initial page \"" . $this->testData::INITIAL_URL . "\"");; // $this->wd holds instance of \RemoteWebDriver
    }

    public function testShouldHavePreviewOnVideo()
    {
        $this->log("2. Enter \"" . $this->testData::SEARCH_QUERY . "\" to the query");
        $this->page->searchText($this->testData::SEARCH_QUERY); //searches the entered text

        $this->log("3. Waiting for the search results to appear");
        $this->assertTrue($this->page->isSearchResultsVisible(), "Search results did not appear"); //checks whether search results are displayed

        $this->log("4. Hover mouse cursor to any video in the search result");
        $itemIndex = $this->page->hoverMousePointerToRandomVideo(); //moves mouse cursor to the random found video, returns its

        $this->log("5. Check whether this video has a trailer (changing pictures on mouse hovering)");
        $this->assertTrue($this->page->isVideoTrailerShown($itemIndex), "Video trailer is not shown");
    }
}
