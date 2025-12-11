# Fixtures (Test Data)

The bundle includes a fixture class (`EventFixture`) to generate test data for development. This is particularly useful for testing layout and functionality (e.g., pagination, filters).

## Usage

Fixtures can be loaded via the Symfony console. Since they are assigned to the `events` group, they can be executed specifically.

```bash
# Loads the event fixtures
php bin/console doctrine:fixtures:load --group=events --append
```

**Note:** The `--append` option ensures that data is added instead of purging the entire database.

## Generated Data

The `EventFixture` creates:
- **25 Events** with various titles and types (Conference, Workshop, Meeting, etc.).
- **Timeframes**: A mix of past and future events (ranging from -30 to +45 days).
- **Languages**: Content is created for both English (`en`) and German (`de`).
- **Status**: Most events are published; every 5th event remains in draft status to allow testing of unpublished event behavior.
- **Details**: Populates title, subtitle, summary, description, footer, SEO data, excerpts, contact info, and locations.

## Prerequisites

Ensure the database is up to date and necessary tables exist:

```bash
php bin/console doctrine:schema:update --force
```
