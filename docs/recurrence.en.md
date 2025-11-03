# Recurring Events

## Overview

The recurrence feature enables the creation of events that repeat on a regular schedule. The system generates individual event occurrences based on recurrence rules.

## Configuration

Recurrence settings can be configured for each event in the **Recurrence** tab of the event editor.

### Recurrence Options

#### Basic Settings
- **Enable Recurrence**: Activate recurring schedule for this event
- **Repeat Pattern**: Frequency of recurrence
    - Daily
    - Weekly
    - Monthly
    - Yearly
- **Interval**: Repeat every X days/weeks/months/years (e.g., "2" for every 2 weeks)

#### Weekly Recurrence
When "Weekly" is selected, you can specify:
- **Weekdays**: Select specific days of the week for the event to recur

#### End Conditions
Choose when the recurrence should stop:
- **Never**: Event continues indefinitely
- **After Count**: Stop after a specific number of occurrences
- **Until Date**: Stop on a specific date

## How It Works

The bundle uses two approaches for handling recurring events:

1. **Virtual Occurrences**: Generate occurrences on-the-fly when displaying calendars
2. **Physical Occurrences**: Create individual event entries via command

### Virtual Occurrences

When querying events (e.g., for calendar display), the system automatically generates occurrences based on recurrence rules within the requested date range.

### Physical Occurrences

For better performance and indexing, you can generate actual event entries using the provided console command.

## Console Command

Generate event occurrences for the upcoming period:

```bash
php bin/console sulu:event:generate-recurring [--lookahead=DAYS]
```

### Parameters

- `--lookahead`: Number of days to look ahead (default: 90)

### Example

```bash
# Generate occurrences for the next 180 days
php bin/console sulu:event:generate-recurring --lookahead=180
```

### Scheduling

It's recommended to run this command regularly via cron:

```bash
# Run daily at 2:00 AM
0 2 * * * cd /path/to/project && php bin/console sulu:event:generate-recurring
```

## Usage in Code

### RecurrenceGenerator Service

The `RecurrenceGenerator` service handles occurrence calculation:

```php
use Manuxi\SuluEventBundle\Service\RecurrenceGenerator;

public function __construct(
    private RecurrenceGenerator $recurrenceGenerator
) {}

public function generateOccurrences(EventRecurrence $recurrence)
{
    $start = new \DateTimeImmutable();
    $end = new \DateTimeImmutable('+90 days');
    
    $occurrences = $this->recurrenceGenerator->generateOccurrences(
        $recurrence,
        $start,
        $end
    );
    
    // $occurrences is an array of DateTimeInterface objects
    foreach ($occurrences as $date) {
        // Process each occurrence date
    }
}
```

### Checking Recurrence in Templates

```twig
{% if event.recurrence and event.recurrence.isRecurring %}
    <div class="recurrence-info">
        <p>This event repeats {{ event.recurrence.frequency }}</p>
        
        {% if event.recurrence.endType == 'until' %}
            <p>Until: {{ event.recurrence.until|date('Y-m-d') }}</p>
        {% elseif event.recurrence.endType == 'count' %}
            <p>{{ event.recurrence.count }} occurrences</p>
        {% endif %}
    </div>
{% endif %}
```

## Recurrence Rules

### Daily
Repeats every X days.

**Example**: Every day → `frequency: daily, interval: 1`

**Example**: Every 3 days → `frequency: daily, interval: 3`

### Weekly
Repeats on specific weekdays every X weeks.

**Example**: Every Monday and Wednesday → `frequency: weekly, interval: 1, byWeekday: [1, 3]`

**Example**: Every other Friday → `frequency: weekly, interval: 2, byWeekday: [5]`

### Monthly
Repeats on the same day of the month every X months.

**Example**: Every month on the 15th → `frequency: monthly, interval: 1`

**Example**: Every 3 months → `frequency: monthly, interval: 3`

### Yearly
Repeats on the same date every X years.

**Example**: Annual event → `frequency: yearly, interval: 1`
