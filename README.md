# TrailView - Garmin KML Viewer

TrailView is a simple PHP application that fetches and displays data from a Garmin MapShare KML file. The app extracts GPS coordinates, velocity, and other data from the KML feed and provides a link to open the location in Google Maps. It's designed for users who want to visualize their trail or outdoor activity data in an easy-to-read format.

## Features

- **Fetch KML Data**: Provides Garmin MapShare details like coordinates, speed (in mph), and the last updated timestamp.
- **Google Maps Integration**: Generates a link to open the last recorded GPS location in Google Maps.
- **Garmin MapShare Link**: Provides a direct link to the MapShare page.
- **Clipboard Functionality**: Users can copy the GPS coordinates to their clipboard.
- **Automatic Page Refresh**: A button to refresh the displayed data.

## How It Works

1. **Input**: The app accepts either a Garmin MapShare URL or a user-specific identifier (`q` parameter) via the query string.
   - Example: `https://your-domain.com?q=DJFOX` or `https://your-domain.com?q=https://inreach.garmin.com/feed/share/DJFOX`.
2. **Processing**: It fetches and parses the KML feed data associated with the provided Garmin MapShare link.
3. **Output**: Displays the extracted information including the placemark's name, last update timestamp, and speed. You can also view the location on Google Maps or the Garmin MapShare page.

## Example Usage

### Default Example

If no input (`q` parameter) is provided, the app defaults to an example Garmin MapShare ID:

- **KML Feed**: `https://inreach.garmin.com/feed/share/DJFOX`
- **MapShare Link**: `https://share.garmin.com/DJFOX`

### User Input

You can also pass your own Garmin MapShare ID or feed URL:

```
https://your-domain.com/?q=<Garmin-MapShare-ID-or-URL>
```

### Example Query

```
https://your-domain.com/?q=DJFOX
```

or

```
https://your-domain.com/?q=https://inreach.garmin.com/feed/share/DJFOX
```

## Running the PHP Native Web Server

You can quickly run the app using PHP's built-in web server, which is ideal for local development.

1. Open a terminal or command prompt.
2. Navigate to the directory where your PHP file is located.
3. Run the following command to start the PHP web server on port 8000:

```bash
php -S localhost:8000
```

4. Open a web browser and visit:

```
http://localhost:8000
```

You can now access your TrailView app locally.

## Installation

1. **PHP Environment**: Ensure your web server is running PHP.
2. **File Setup**: Place the PHP file on your web server.
3. **Access**: Use a web browser to navigate to the PHP file and pass the Garmin MapShare URL or ID via the query string.

## Deploying with Tiiny Host

To deploy your PHP app using [Tiiny Host](https://tiiny.host/), follow these steps:

1. **Prepare Your Project**:
   - Zip your PHP file and any other required assets (if any) into a `.zip` file.

2. **Upload to Tiiny Host**:
   - Visit the [Tiiny Host website](https://tiiny.host/).
   - Click "Upload" and select the `.zip` file you created.
   - Enter your subdomain name and deploy the app.

3. **Access the App**:
   - Once the deployment is complete, you'll be provided with a link where your app is hosted. You can now access and share your PHP app directly from the Tiiny Host URL.

## Key Functions

- **isValidUrl**: Validates if the provided input is a valid URL.
- **kmhToMph**: Converts speed from kilometers per hour to miles per hour.
- **getMapId**: Extracts the MapShare ID from a Garmin URL.

## Dependencies

- PHP 5.3 or higher
- SimpleXML Extension (used for parsing KML)

## License

This project is open source and available under the [MIT License](LICENSE).

## Acknowledgements

- **Garmin MapShare**: Data provided by Garmin MapShare's KML feed.
