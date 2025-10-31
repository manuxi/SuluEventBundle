/**
 * FullCalendar Integration for SuluEventBundle
 *
 * Features:
 * - Bootstrap 5 Theme
 * - German/English locale support with proper formatting
 * - Custom time formatting (24h, no AM/PM)
 * - All-day event detection (00:00-00:00)
 * - Responsive behavior
 * - Event tooltips with location
 * - Valid range limited to events (optional)
 * - Custom event rendering for better readability
 * - Event type colors
 * - Improved title display with line breaks
 */
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('event-calendar');

    if (!calendarEl) {
        return;
    }

    // Get configuration from data attributes
    const eventsUrl = calendarEl.dataset.eventsUrl;
    const locale = calendarEl.dataset.locale || 'de';
    const initialView = calendarEl.dataset.initialView || 'dayGridMonth';
    const weekNumbers = calendarEl.dataset.weekNumbers === 'true';
    const eventLimit = parseInt(calendarEl.dataset.eventLimit) || 3;
    const showWeekends = calendarEl.dataset.weekends !== 'false';
    const eventColor = calendarEl.dataset.eventColor || '#0d6efd';
    const limitToEvents = calendarEl.dataset.limitToEvents === 'true';

    // Fetch events to determine valid range
    fetch(eventsUrl)
        .then(response => response.json())
        .then(events => {
            let validRange = undefined;

            // Limit calendar to event date range if enabled
            if (limitToEvents && events.length > 0) {
                const dates = events.map(e => new Date(e.start));
                const minDate = new Date(Math.min(...dates));
                const maxDate = new Date(Math.max(...dates));

                // Add some padding (1 week before first event, 1 week after last event)
                minDate.setDate(minDate.getDate() - 7);
                maxDate.setDate(maxDate.getDate() + 7);

                validRange = {
                    start: minDate,
                    end: maxDate
                };
            }

            // Locale-specific button text
            const buttonText = locale === 'de' ? {
                today: 'Heute',
                month: 'Monat',
                week: 'Woche',
                day: 'Tag',
                list: 'Liste',
                multiMonthYear: 'Jahr'
            } : {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day',
                list: 'List',
                multiMonthYear: 'Year'
            };

            // Initialize calendar
            const calendar = new FullCalendar.Calendar(calendarEl, {
                // Theme
                themeSystem: 'bootstrap5',

                // Locale
                locale: locale,
                firstDay: 1,  // Monday

                // Initial view - use data attribute
                initialView: initialView,
                validRange: validRange,

                // Header toolbar
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth,timeGridWeek,listMonth'
                },

                // Button text
                buttonText: buttonText,

                // Display options
                weekNumbers: weekNumbers,
                weekends: showWeekends,
                dayMaxEvents: eventLimit,
                nowIndicator: true,
                navLinks: true,

                // Time format - 24 hour for German, 12 hour for English
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: locale === 'de' ? false : 'short',
                    hour12: locale !== 'de'
                },

                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: locale === 'de' ? false : 'short',
                    hour12: locale !== 'de'
                },

                // Event display
                eventDisplay: 'block',

                // Events source
                events: eventsUrl,

                // Custom event rendering with line break after time
                eventContent: function(arg) {
                    const event = arg.event;

                    // Check if all-day (00:00 - 00:00 means no time display)
                    const isAllDay = event.start.getHours() === 0 &&
                        event.start.getMinutes() === 0 &&
                        (!event.end || (event.end.getHours() === 0 && event.end.getMinutes() === 0));

                    let html = '<div class="fc-event-main-frame">';

                    // Time (only if not all-day) with line break
                    if (!isAllDay && arg.timeText) {
                        html += '<div class="fc-event-time" style="font-size: 0.85em; font-weight: normal;">' +
                            arg.timeText +
                            '</div>';
                    }

                    // Title with smaller font and normal weight
                    html += '<div class="fc-event-title-container">';
                    html += '<div class="fc-event-title fc-sticky" style="font-size: 0.9em; font-weight: normal; line-height: 1.3;">' +
                        event.title +
                        '</div>';
                    html += '</div>';

                    html += '</div>';

                    return { html: html };
                },

                // Add tooltip with full information and apply type color
                eventDidMount: function(info) {
                    const event = info.event;
                    const extendedProps = event.extendedProps;

                    // Build tooltip
                    let tooltip = event.title;

                    // Add time if not all-day
                    if (event.start && event.start.getHours() !== 0) {
                        const startTime = event.start.toLocaleTimeString(locale, {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        if (event.end && event.end.getHours() !== 0) {
                            const endTime = event.end.toLocaleTimeString(locale, {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            tooltip = startTime + ' - ' + endTime + '\n' + tooltip;
                        } else {
                            tooltip = startTime + '\n' + tooltip;
                        }
                    }

                    // Add summary
                    if (extendedProps.summary) {
                        tooltip += '\n\n' + extendedProps.summary;
                    }

                    // Add location
                    if (extendedProps.location) {
                        tooltip += '\n\n📍 ' + extendedProps.location;
                    }

                    // Add type if available
                    if (extendedProps.type) {
                        tooltip += '\n\nTyp: ' + extendedProps.type;
                    }

                    info.el.title = tooltip;

                    // Apply type color with subtle background
                    let typeColor = eventColor;
                    if (extendedProps.typeColor) {
                        typeColor = extendedProps.typeColor;
                    }

                    // Light background with border
                    info.el.style.backgroundColor = typeColor + '1a';  // 10% opacity
                    info.el.style.borderColor = typeColor;
                    info.el.style.borderWidth = '2px';
                    info.el.style.borderLeftWidth = '4px';  // Stronger left border for type indication

                    // Hover effect
                    info.el.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = typeColor + '33';  // 20% opacity on hover
                    });
                    info.el.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = typeColor + '1a';  // Back to 10%
                    });
                },

                // Click event to open detail page
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                },

                // Responsive behavior
                windowResize: function(view) {
                    if (window.innerWidth < 576) {
                        calendar.changeView('listWeek');
                    } else if (window.innerWidth < 992 && calendar.view.type === 'listWeek') {
                        calendar.changeView(initialView);
                    }
                },

                // Multi-month view configuration
                views: {
                    multiMonthYear: {
                        type: 'multiMonth',
                        duration: { months: 4 },
                        buttonText: locale === 'de' ? 'Jahr' : 'Year'
                    }
                },

                // Loading indicator
                loading: function(isLoading) {
                    if (isLoading) {
                        calendarEl.style.opacity = '0.5';
                    } else {
                        calendarEl.style.opacity = '1';
                    }
                }
            });

            calendar.render();

            // Initial responsive check
            if (window.innerWidth < 576) {
                calendar.changeView('listWeek');
            }
        })
        .catch(error => {
            console.error('Error loading calendar events:', error);
            const errorMsg = locale === 'de'
                ? 'Fehler beim Laden der Events'
                : 'Error loading events';
            calendarEl.innerHTML = '<div class="alert alert-danger">' + errorMsg + '</div>';
        });
});