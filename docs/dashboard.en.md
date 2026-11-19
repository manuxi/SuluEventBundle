# Dashboard tiles

If [manuxi/sulu-dashboard-bundle](https://github.com/manuxi/SuluDashboardBundle) is installed, the bundle adds two tiles to the Sulu dashboard. Without the dashboard nothing changes: it is only suggested (`composer suggest`), the tiles are loaded only when it is installed.

```bash
composer require manuxi/sulu-dashboard-bundle
```

Both tiles are in the narrow column. They are visible with the permission "View" for events (`sulu.events.events`, the same context that applies to locations). The texts are available in German and English.

## Upcoming events

The next five published events with date and location, each with a link to the event form. The location is the grey second line. The footer of the tile shows the number of events that are not published (a draft without a live version) in the language selected in the toolbar, with a link to the list.

- Only the published version in the selected language is considered, as on the website.
- An event is "upcoming" while it is running or starts today or later. Without an end date, the start date counts from today on.
- Recurring events: the bundle stores one entry per occurrence (command `sulu:events:generate-recurring`). The next date of a series is therefore the next occurrence that has been generated.

## Check locations

Lists the locations that have no coordinates or an incomplete address (street, postal code and city). The second column names what is missing (the number of items if there are several). A click opens the location form. If everything is complete, the tile says so.

The coordinates are the ones from the map field of the location. A map position of 0/0 counts as missing.

## Screenshots needed

- Both tiles on the dashboard, with and without findings (`docs/img/dashboard.en.png`, `docs/img/dashboard.de.png`)
