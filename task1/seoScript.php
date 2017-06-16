<?php

/** retrieve info from *csv file
 * @param $fileName - file name
 * @return array - retrieved info
 */
function getDataFromCSV($fileName)
{
    if (file_exists($fileName)) { //check whether file exists or not
        $csvFile = fopen($fileName, "r");
        if ($csvFile === false) {  //error in opening the file
            echo("Error in opening file $fileName.\r\n");
            exit(1);
        }
    } else { //file is not found
        echo("File $fileName was not found\r\n");
        exit(1);
    }

    $csvMap = array_map("str_getcsv", file($fileName));  //copy csv info to map
    fclose($csvFile);

    if (!$csvMap) {    //if something went wrong with *.csv file
        echo "Unable to import CSV data from " . $fileName . ". Please check the file content.\r\n";
        exit(1);
    } else
        return $csvMap;
}

/** retrieve site contents by url for getMetaTags function
 * @param $url -  site URL
 * @return mixed - site contents
 */
function getSiteContents($url)
{
    $curlHandle = curl_init();

    curl_setopt($curlHandle, CURLOPT_HEADER, 0);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curlHandle, CURLOPT_COOKIE, "test=seo");

    $data = curl_exec($curlHandle);
    curl_close($curlHandle);

    return $data;
}

/** retrieve title and description meta tags from the site contents
 * @param $siteUrl -  site URL
 * @return array - found title and description content
 */
function getMetaTags($siteUrl)
{
    $html = getSiteContents($siteUrl);

    $doc = new DOMDocument();
    @$doc->loadHTML($html);


    $metaTitle = $doc->getElementsByTagName('title')->item(0)->nodeValue; //title found
    $metaDescription = "";
    $metaDescriptions = array();
    $metaTags = $doc->getElementsByTagName('meta');
    $k = 0;
    for ($i = 0; $i < $metaTags->length; $i++) {
        $mTag = $metaTags->item($i);

        if ('description' == trim($mTag->getAttribute('name'))) {
            $metaDescription = trim($mTag->getAttribute('content'));
            $metaDescriptions[$k] = $metaDescription;
            $k++;
        }
    }

    return array($metaTitle, $metaDescriptions);
}

/** checks whether the title and description are the same as in passed parameters
 * @param $url - site URL
 * @param $metaTitle - wanted title content
 * @param $metaDescription - wanted description content
 * @return array - check result (flags is was tag found and if content is as expected)
 */
function isMetaTagUpdated($url, $metaTitle, $metaDescription)
{
    $metaData = getMetaTags($url);
    $pageTitle = $metaData[0];
    $pageDescriptions = $metaData[1];
    $isTitleFound = false;
    $isTitleCorrect = false;
    $isDescriptionFound = false;
    $isDescriptionCorrect = false;

    if ($pageTitle) { //title found
        $isTitleFound = true;
        if ($metaTitle == trim($pageTitle)) //title content is as expected
            $isTitleCorrect = true;
        else {
            echo "  Found title is: " . $pageTitle . "\r\n";    //debug
            echo " Wanted title is: " . $metaTitle . "\r\n";
        }
    }

    $resultString = "";

    if (count($pageDescriptions) > 0) { //at least 1 description tag is found
        $isDescriptionFound = true;
        for ($i = 0; $i < count($pageDescriptions); $i++){
            if ($metaDescription == trim($pageDescriptions[$i])) {    //description is as expected
                $isDescriptionCorrect = true;
                $resultString = "";
                break;
            } else {
                $resultString = $resultString . "  Found description #" . ($i+1) . " is: " . $pageDescriptions[$i] . "\r\n";
            }
        }
    }

    if ($resultString) {
        echo $resultString;
        echo " Wanted description is: " . $metaDescription . "\r\n";
    }

    return array($isTitleFound, $isTitleCorrect, $isDescriptionFound, $isDescriptionCorrect);
}

if (isset($argv[1])) //retrieves *.csv file name if passed from console
    $fileName = $argv[1];
else {
    echo "Please pass an update *.csv file with url, meta title and meta description fields as a second parameter";
    exit(1);
}

$csvMap = getDataFromCSV($fileName);  //retrieve data from *.csv file

for ($i = 1; $i < count($csvMap); $i++) { //loop through the records received from *.csv file
    $siteURL = $csvMap[$i][0];  //retrieve url from the record
    $tagTitle = $csvMap[$i][1]; //retrieve meta title
    $tagDescription = $csvMap[$i][2]; //retrieve meta description content

    echo "Checking the " . $siteURL . ": \r\n";

    $checkResult = isMetaTagUpdated($siteURL, $tagTitle, $tagDescription);  //check if the $tagTitle meta tag has the correct content

    if ($checkResult[0]) {  //meta title exists
        if ($checkResult[1]) //meta title is correct
            echo "  Meta Title is updated. \r\n";
        else {
            echo "  Meta Title is not updated. \r\n";
        }
    } else { //title not found
        echo "  Meta Title \"" . $tagTitle . "\" is not found. \r\n";
    }

    if ($checkResult[2]) { //meta description exists
        echo "  Meta description found. \r\n";
        if ($checkResult[3]) { //meta description is correct
            echo "  Meta description(s) is(are) updated. \r\n";
        } else {
            echo "  Meta description is NOT updated. \r\n";
        }
    } else
        echo "  Meta description \"" . $tagDescription . "\" is NOT found. \r\n";
    echo "\r\n";
}
