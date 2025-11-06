/**
 * FullCalendar Integration for SuluEventBundle
 * FullCalendar v6 compatible
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
 * - Multiple calendar instances support
 */

// Import FullCalendar core
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import multiMonthPlugin from '@fullcalendar/multimonth';
import bootstrap5Plugin from '@fullcalendar/bootstrap5';

// Import FullCalendar locales
import deLocale from '@fullcalendar/core/locales/de';
import enLocale from '@fullcalendar/core/locales/en-gb';

// Import Bootstrap Popover for tooltips
import { Popover } from 'bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const calendarElements = document.querySelectorAll('.event-calendar');

    if (calendarElements.length === 0) {
        return;
    }

    // Initialize each calendar instance
    calendarElements.forEach(function(calendarEl) {
        initializeCalendar(calendarEl);
    });
});

function initializeCalendar(calendarEl) {
    if (!calendarEl) {
        return;
    }

    // Get configuration from data attributes
    const eventsUrl = calendarEl.dataset.eventsUrl;
    const locale = calendarEl.dataset.locale || 'de';
    const initialView = calendarEl.dataset.initialView || 'dayGridMonth';
    const weekNumbers = calendarEl.dataset.weekNumbers === 'true';
    const weekDayStart = calendarEl.dataset.weekDayStart || '00:00';
    const weekDayEnd = calendarEl.dataset.weekDayEnd || '23:59';
    const yearMonths = parseInt(calendarEl.dataset.yearMonths) || 3;
    const eventLimit = parseInt(calendarEl.dataset.eventLimit) || 3;
    const firstDay = parseInt(calendarEl.dataset.firstDay) || 1;
    const showWeekends = calendarEl.dataset.weekends !== 'false';
    const eventColor = calendarEl.dataset.eventColor || '#ccc';
    const limitToEvents = calendarEl.dataset.limitToEvents === 'true';
    const toggleView = calendarEl.dataset.toggleView === 'true';
    const toggleLocation = calendarEl.dataset.toggleLocation === 'true';
    const toggleType = calendarEl.dataset.toggleType === 'true';

    // Handle allowedViews - can be comma-separated string or already parsed
    let allowedViews = calendarEl.dataset.allowedViews || 'dayGridMonth';
    if (allowedViews && typeof allowedViews === 'string' && allowedViews.includes(',')) {
        allowedViews = allowedViews.split(',').join(',');
    }

    const desiredOrder = [
        'timeGridWeek',
        'dayGridMonth',
        'multiMonthYear',
        'listWeek',
        'listMonth'
    ];

    const allowedViewsArray = allowedViews.split(',').map(v => v.trim());

    allowedViewsArray.sort((a, b) => {
        return desiredOrder.indexOf(a) - desiredOrder.indexOf(b);
    });

    const sortedAllowedViewsString = allowedViewsArray.join(',');
    const showViewToggle = allowedViewsArray.length > 1;

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

            const buttonIcons = {
                prev: 'arrow-left-square-fill',
                next: 'arrow-right-square-fill',
                prevYear: 'chevrons-left', // double chevron
                nextYear: 'chevrons-right' // double chevron
            };

            // Initialize calendar
            const calendar = new Calendar(calendarEl, {
                // Plugins
                plugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    listPlugin,
                    multiMonthPlugin,
                    bootstrap5Plugin
                ],

                // Theme
                themeSystem: 'bootstrap5',

                // Locale
                locale: locale === 'de' ? deLocale : enLocale,
                firstDay: firstDay,  // Monday

                // Initial view - use data attribute
                initialView: initialView,
                //validRange: validRange,

                // Header toolbar
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: toggleView && showViewToggle ? sortedAllowedViewsString : ''
                },

                // Button text
                buttonText: {
                    listWeek: locale === 'de' ? 'Wochenliste' : 'List Week',
                    listMonth: locale === 'de' ? 'Monatsliste' : 'List Month',
                },
                buttonIcons: false,

                viewHint: function(hint) {
                    return (locale === 'de' ? 'Sicht' : 'View') +' '+ hint;
                },

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
                    const isAllDay = !event.start ||
                        (event.start.getHours() === 0 &&
                            event.start.getMinutes() === 0 &&
                            (!event.end || (event.end.getHours() === 0 && event.end.getMinutes() === 0)));

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
                // Add Bootstrap Popover with full information and apply type color
                eventDidMount: function(info) {
                    const event = info.event;
                    const extendedProps = event.extendedProps;

                    // Get type color or fallback to default
                    const typeColor = (extendedProps.type && extendedProps.type.color)
                        ? extendedProps.type.color
                        : eventColor;

                    // Apply color
                    info.el.style.backgroundColor = typeColor + '1a';  // 10% opacity
                    info.el.style.borderColor = typeColor;
                    info.el.style.color = typeColor;

                    // Build tooltip content
                    let tooltipContent = '<div class="event-popover">';
                    tooltipContent += '<strong>' + event.title + '</strong><br>';

                    if (event.start) {
                        const startStr = event.start.toLocaleString(locale === 'de' ? 'de-DE' : 'en-GB', {
                            weekday: 'short',
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        tooltipContent += '<small>' + startStr + '</small><br>';
                    }

                    if (toggleLocation && extendedProps.location) {
                        tooltipContent += '<small><i class="bi bi-geo-alt"></i> ' + extendedProps.location + '</small><br>';
                    }

                    if (toggleType && extendedProps.type && extendedProps.type.name) {
                        tooltipContent += '<small><i class="bi bi-tag"></i> ' + extendedProps.type.name + '</small>';
                    }

                    tooltipContent += '</div>';

                    // Initialize Bootstrap Popover
                    new Popover(info.el, {
                        title: '',
                        content: tooltipContent,
                        html: true,
                        trigger: 'hover',
                        placement: 'top',
                        container: 'body'
                    });

                    // Style the dot for list view
                    const dot = info.el.querySelector('.fc-list-event-dot');
                    if (dot) {
                        dot.style.borderColor = typeColor;
                        dot.style.backgroundColor = typeColor;
                    }

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
                        calendar.changeView('listMonth');
                    } else if (window.innerWidth < 992 && calendar.view.type === 'listMonth') {
                        calendar.changeView(initialView);
                    }
                },

                // Multi-month view configuration
                views: {
                    dayGridMonth: {
                        validRange: validRange
                    },
                    timeGridWeek: {
                        validRange: validRange,
                        slotMinTime: weekDayStart,
                        slotMaxTime: weekDayEnd
                    },
                    timeGridDay: {
                        validRange: validRange
                    },
                    multiMonthYear: {
                        type: 'multiMonthYear',
                        duration: { months: yearMonths },
                        buttonText: locale === 'de' ? 'Jahr' : 'Year'
                    }
                },
                contentHeight: 'auto',
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
                calendar.changeView('listMonth');
            }
        })
        .catch(error => {
            console.error('Error loading calendar events:', error);
            const errorMsg = locale === 'de'
                ? 'Fehler beim Laden der Events'
                : 'Error loading events';
            calendarEl.innerHTML = '<div class="alert alert-danger">' + errorMsg + '</div>';
        });
}