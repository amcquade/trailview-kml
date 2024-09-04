<?php

$error = false;
$sheet = '';
$share = '';


if (isset($_GET['q'])) {
    if (isValidUrl($_GET['q'])) {
        $url = $_GET['q'];
        $id = getMapId($url);
        if (empty($id)) {
            $error = true;
        } else {
            $share = 'https://share.garmin.com/' . $id;
        }
    } else {
        $url = "https://inreach.garmin.com/feed/share/" . $_GET['q'];
        $share = 'https://share.garmin.com/' . $_GET['q'];;
    }
} else {
    $share = 'https://share.garmin.com/DJFOX';
    $url = 'https://inreach.garmin.com/feed/share/DJFOX';
    $sheet = 'https://docs.google.com/spreadsheets/d/1LVJXIMINj0LVr4OnpQkIlaCgr2zb-qEIumOQTjdq5CY/edit?usp=drivesdk';
}

if (!isValidUrl($url)) {
    $error = true;
}



function isValidUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}


function kmhToMph($kmh)
{
    $kmh = (float) str_replace('km/h', '', $kmh);
    $conversionFactor = 0.621371;
    $mph = $kmh * $conversionFactor;
    return $mph;
}

function getMapId($s)
{
    $r = explode('/', $s);
    $l = end($r);
    if (empty($l)) {
        $l = prev($r);
    }

    return $l;
}

$kmh = 0;
$mph = 0;

$str = $last = '';

// Load the KML file
try {
    $kml = simplexml_load_file($url);
    if (empty($kml)) {
        $error = true;
    } else {
        // Register the KML namespace (required for proper XML handling)
        $kml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
    }
} catch (Exception $e) {
    $error = true;
}



if ($error) {
?>

    <strong>an error has occured.</strong>

<?php
} else {
    // Iterate over all placemarks
    foreach ($kml->xpath('//kml:Placemark') as $placemark) {

        // Get the name of the placemark
        $name = (string) $placemark->name;
        // echo "Placemark Name: $name\n<br/>";


        //echo "<br />";
        // Accessing custom attributes (ExtendedData)
        if ($placemark->ExtendedData) {
            foreach ($placemark->ExtendedData->Data as $data) {
                $attrName = (string) $data['name']; // attribute name
                $attrValue = (string) $data->value; // attribute value

                // echo "$attrName: $attrValue<br />";
                if ($attrName == 'Time')
                    $last = $attrValue;

                if ($attrName == 'Latitude')
                    $str .= "$attrValue, ";
                if ($attrName == 'Longitude')
                    $str .= "$attrValue";

                if ($attrName == 'Velocity')
                    $mph = kmhToMph($attrValue);
            }
        }

        // echo "<br/><br/>";
    }
    $dest = urlencode($str);
    $google = "https://www.google.com/maps/dir/?api=1&destination=$dest&travelmode=walking";
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>garmin quick copy</title>
    </head>

    <body>
        <hr />
        <strong>Garmin Data for: '<?php echo $name; ?>'</strong>
        <br />
        <strong>Last update <?php echo $last ?></strong>
        <br />
        <strong>speed: <?php echo $mph; ?> mph</strong>
        <br />
        <input type="text" id="textToCopy" value="<?php echo $str ?>" />
        <button onclick="copyToClipboard()">Copy to Clipboard</button>
        <br /><br />
        <button onclick="refreshPage()">Refresh Page</button>


        <br /><br /><br />
        <a href="<?php echo $google ?>">open google maps from your location to dj</a>

        <br /><br /><br />

        <a href="<?php echo $share; ?>" target="_blank">garmin map share</a>

        <?php

        if (!empty($sheet)) {
        ?>

            <br /><br /><br />
            <a href="<?php echo $sheet; ?>" target="_blank">google spreadsheet</a>

        <?php
        }

        ?>

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

                    document.body.removeChild(textArea);
                }
            }
        </script>
    </body>

    </html>
<?php
}
