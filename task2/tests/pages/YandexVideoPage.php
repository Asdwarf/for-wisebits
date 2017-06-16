<?php

/**
 * Created by PhpStorm.
 * User: anton.surma
 * Date: 15.06.2017
 * Time: 11:06
 */

namespace My\Pages;

use Lmc\Steward\Component\AbstractComponent;
use My\YaTestTestData;

class YandexVideoPage extends AbstractComponent
{
    const SEARCH_INPUT_XPATH = '//*[@class=\'search2__input\']//input[@type=\'search\']';
    const SEARCH_BUTTON_XPATH = '//*[@class=\'search2__button\']//button[contains(@class,\'button\')]';
    const FOUND_VIDEOS_CONTAINER_XPATH = '//*[contains(@class,\'serp-controller\')]//div[contains(@class,\'serp-list\')]';
    const FOUND_VIDEOS_VIDEO_XPATH = '//*[contains(@class,\'serp-controller\')]//div[contains(@class,\'serp-list\')]/div';
    const FOUND_VIDEOS_VIDEO_IMG_XPATH = '//*[contains(@class,\'serp-controller\')]//div[contains(@class,\'serp-list\')]/div[$index$]//img[contains(@class,\'thumb-image__image\')]';

    /**
     * Enter the text to be searched on the page
     * @param $text - text for search
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    private function enterSearchTextAndSubmit($text)
    {
        $searchInput = $this->waitForXpath(self::SEARCH_INPUT_XPATH);
        return $searchInput->sendKeys($text)->submit();
    }

    /**
     * Enters text and submits the search
     * @param $text - text for search
     */
    public function searchText($text)
    {
        $this->enterSearchTextAndSubmit($text);
        $this->waitForPartialTitle($text);
    }

    /**
     * Checks whether the search results are visible (container with videos)
     * @return bool -
     */
    public function isSearchResultsVisible()
    {
        if ($this->waitForXpath(self::FOUND_VIDEOS_CONTAINER_XPATH))
            return $this->findByXpath(self::FOUND_VIDEOS_CONTAINER_XPATH)->isDisplayed();
        else
            return false;
    }

    /**
     * Hovers the random found element with mouse pointer
     * @return int - index of the hovered video in the list
     */
    public function hoverMousePointerToRandomVideo()
    {
        $i = rand(1, count($this->findMultipleByXpath(self::FOUND_VIDEOS_VIDEO_XPATH)));
        $videoFound = $this->findByXpath(self::FOUND_VIDEOS_VIDEO_XPATH . "[" . $i . "]");
        $this->wd->getMouse()->mouseMove($videoFound->getCoordinates(), 3, 3);
        return $i;
    }

    /**
     * Retrieves the thumbnail image source url for the hovered video
     * @param $index - index of the hovered video in the list
     * @return null|string - image source url
     */
    private function getTrailerPictureSrc($index)
    {
        return $this->findByXpath(str_replace("\$index\$", $index, self::FOUND_VIDEOS_VIDEO_IMG_XPATH))->getAttribute("src");
    }

    /**
     * Check the trailer for the hovered video (picture changes)
     * @param $index - index of the hovered video in the list
     * @return bool - is trailer shown
     */
    public function isVideoTrailerShown($index)
    {
        $initialPictureURL = $this->getTrailerPictureSrc($index);
        for ($time = 1; $time < YaTestTestData::MOUSE_HOVER_TIMEOUT; $time++) {
            sleep($time);
            if ($initialPictureURL != $this->getTrailerPictureSrc($index))
                return true;
        }
        return false;
    }

}