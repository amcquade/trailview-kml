<?php
$startTime = microtime(true);

$error = false;
$mapShareUrl = $kmlFeedUrl = '';

if (isset($_GET['q'])) {
    if (isValidUrl($_GET['q'])) {
        $kmlFeedUrl = $_GET['q'];
        $id = getMapId($kmlFeedUrl);
        if (empty($id)) {
            $error = true;
        } else {
            $mapShareUrl = 'https://share.garmin.com/' . $id;
        }
    } else {
        $kmlFeedUrl = "https://inreach.garmin.com/feed/share/" . $_GET['q'];
        $mapShareUrl = 'https://share.garmin.com/' . $_GET['q'];;
    }
} else {
    // exmaple URLs
    $mapShareUrl = 'https://share.garmin.com/DJFOX';
    $kmlFeedUrl = 'https://inreach.garmin.com/feed/share/DJFOX';
}



$coordinatesString = $lastTimestamp = '';
$kmh = $mph = 0;

if (!isValidUrl($kmlFeedUrl)) {
    $error = true;
} else {
    // Load the KML file
    try {
        $kml = simplexml_load_file($kmlFeedUrl);
        if (empty($kml)) {
            $error = true;
        } else {
            // Register the KML namespace (required for proper XML handling)
            $kml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
        }
    } catch (Exception $e) {
        $error = true;
    }

    if (!$error) {
        // Iterate over all placemarks
        foreach ($kml->xpath('//kml:Placemark') as $placemark) {

            // Get the name of the placemark
            if (isset($placemark->name)) {
                $name = (string) $placemark->name;
            }

            // echo "Placemark Name: $name\n<br/>";


            // echo "<br>";
            // Accessing custom attributes (ExtendedData)
            if ($placemark->ExtendedData) {
                foreach ($placemark->ExtendedData->Data as $data) {
                    $attrName = (string) $data['name']; // attribute name
                    $attrValue = (string) $data->value; // attribute value

                    // echo "$attrName: $attrValue<br>";
                    if ($attrName == 'Time') {
                        $lastTimestamp = $attrValue;
                    }

                    if ($attrName == 'Latitude') {
                        $coordinatesString .= "$attrValue, ";
                    }

                    if ($attrName == 'Longitude') {
                        $coordinatesString .= "$attrValue";
                    }

                    if ($attrName == 'Velocity') {
                        if (strpos($attrValue, 'km/h') !== false) {
                            $mph = kmhToMph($attrValue);
                        } else {
                            $mph = $attrValue;
                        }
                    }
                }
            }

            // echo "<br/><br/>";
        }

        $dest = urlencode($coordinatesString);
        $google = "https://www.google.com/maps/dir/?api=1&destination=$dest&travelmode=walking";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrailView - KML</title>
</head>

<body>
    <?php if ($error): ?>
        <strong>An error has occurred.</strong>
    <?php else: ?>
        <hr />
        <strong>Garmin Data for: '<?php echo $name; ?>'</strong>
        <br>
        <strong>Last update <?php echo $lastTimestamp ?></strong>
        <br>
        <strong>speed: <?php echo $mph; ?> mph</strong>
        <br>
        <input type="text" id="textToCopy" value="<?php echo $coordinatesString ?>" />
        <button onclick="copyToClipboard()">Copy Coordinates to Clipboard</button>
        <br><br>
        <button onclick="refreshPage()">Refresh Page</button>


        <br><br><br>
        <a href="<?php echo $google ?>">open google maps from your location to dj</a>

        <br><br><br>
        <a href="<?php echo $mapShareUrl; ?>" target="_blank">garmin map share</a>

        <script>
            function refreshPage() {
                location.reload();
            }

            function copyToClipboard() {
                const text = document.getElementById('textToCopy').value;

                if (navigator.clipboard && window.isSecureContext) {
                    // Use the Clipboard API
                    navigator.clipboard.writeText(text);
                } else {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = text;

                    // Avoid scrolling to bottom
                    textArea.style.position = 'fixed';
                    textArea.style.top = '0';
                    textArea.style.left = '0';

                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();

                    try {
                        document.execCommand('copy');
                    } catch (err) {
                        console.log('Failed to copy text: ', err);
                    }
                }

                document.body.removeChild(textArea);
            }
        </script>
    <?php endif; ?>
    <br><br><br>
    <footer>
        <?php
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        echo "Page loaded in $executionTime seconds.";
        ?>

    </footer>
</body>

</html>

<?php

/**
 * Check is a url is valid
 *
 * @param string $kmlFeedUrl
 * @return boolean
 */
function isValidUrl($kmlFeedUrl)
{
    return filter_var($kmlFeedUrl, FILTER_VALIDATE_URL) !== false;
}

/**
 * Convert km/h to mi/h 
 *
 * @param int|float $kmh
 * @return int|float
 */
function kmhToMph($kmh)
{
    $kmh = (float) str_replace('km/h', '', $kmh);
    $conversionFactor = 0.621371;
    $mph = $kmh * $conversionFactor;
    return $mph;
}

/**
 * Get user id from url
 *
 * @param string $s
 * @return string
 */
function getMapId($s)
{
    $r = explode('/', $s);
    $l = end($r);
    if (empty($l)) {
        $l = prev($r);
    }

    return $l;
}
