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
    const firstDay = parseInt(calendarEl.dataset.firstDay) || 1;
    const showWeekends = calendarEl.dataset.weekends !== 'false';
    const eventColor = calendarEl.dataset.eventColor || '#ccc';
    const limitToEvents = calendarEl.dataset.limitToEvents === 'true';
    const toggleView = calendarEl.dataset.toggleView === 'true';
    const toggleLocation = calendarEl.dataset.toggleLocation === 'true';
    const allowedViews = calendarEl.dataset.allowedViews || 'dayGridMonth';

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
                    right: toggleView ? allowedViews : ''
                },

                // Button text
                buttonText: buttonText,
                buttonIcons: false,

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

/*                datesSet: function(info) {
                    const isMultiMonth = info.view.type === 'multiMonthYear';
                    if (isMultiMonth) {
                        calendar.setOption('validRange', null);
                    } else {
                        calendar.setOption('validRange', validRange);
                    }
                },*/

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

                    // Determine type color FIRST
                    let typeColor = eventColor;
                    if (extendedProps.typeColor) {
                        typeColor = extendedProps.typeColor;
                    }

                    // Helper function to escape HTML
                    const escapeHtml = (text) => {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    };

                    // Build popover content with HTML
                    let popoverContent = '<div class="event-popover-content">';

                    // Title
                    popoverContent += '<div class="event-popover-title">' + escapeHtml(event.title) + '</div>';

                    // Type with colored dot - use typeColor determined above
                    if (extendedProps.type) {
                        const typeLabel = locale === 'de' ? 'Typ' : 'Type';
                        popoverContent += '<div class="event-popover-type">' +
                            '<span class="event-type-dot event-type-' + extendedProps.type + '" style="background-color: ' + extendedProps.typeColor + ';" style="--dot-color: ' + extendedProps.typeColor + ';"></span>' +
                            typeLabel + ': ' + escapeHtml(extendedProps.type) +
                            '</div>';
                    }

                    // Time if not all-day
                    if (event.start && event.start.getHours() !== 0) {
                        const startTime = event.start.toLocaleTimeString(locale, {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        let timeDisplay = '';
                        if (event.end && event.end.getHours() !== 0) {
                            const endTime = event.end.toLocaleTimeString(locale, {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            timeDisplay = startTime + ' - ' + endTime;
                        } else {
                            timeDisplay = startTime;
                        }
                        popoverContent += '<div class="event-popover-time"><i class="bi bi-clock"></i> ' + escapeHtml(timeDisplay) + '</div>';
                    }

                    // Summary (excerpt/teaser)
                    if (extendedProps.summary) {
                        popoverContent += '<div class="event-popover-summary">' + escapeHtml(extendedProps.summary) + '</div>';
                    }

                    // Full text/description if available
                    if (extendedProps.text && extendedProps.text !== extendedProps.summary) {
                        const maxLength = 200;
                        let textContent = extendedProps.text;
                        if (textContent.length > maxLength) {
                            textContent = textContent.substring(0, maxLength) + '...';
                        }
                        popoverContent += '<div class="event-popover-text">' + escapeHtml(textContent) + '</div>';
                    }

                    // Location
                    if (toggleLocation && extendedProps.location) {
                        popoverContent += '<div class="event-popover-location"><i class="bi bi-geo-alt-fill"></i> ' + escapeHtml(extendedProps.location) + '</div>';
                    }

                    popoverContent += '</div>';

                    // Initialize Bootstrap Popover
                    new Popover(info.el, {
                        trigger: 'hover focus',
                        placement: 'auto',
                        html: true,
                        content: popoverContent,
                        container: 'body',
                        customClass: 'event-popover',
                        delay: { show: 300, hide: 100 }
                    });

                    info.el.addEventListener('shown.bs.popover', () => {
                        const popoverEl = document.querySelector('.popover');
                        if (popoverEl) {
                            popoverEl.style.setProperty('--event-type-color', extendedProps.typeColor);
                        }
                    });

                    // Apply type color with subtle background for month/week view
                    info.el.style.backgroundColor = typeColor + '1a';  // 10% opacity
                    info.el.style.borderColor = typeColor;
                    info.el.style.borderWidth = '2px';
                    info.el.style.borderLeftWidth = '4px';  // Stronger left border for type indication

                    // Apply type color to list view dot
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
                        calendar.changeView('listWeek');
                    } else if (window.innerWidth < 992 && calendar.view.type === 'listWeek') {
                        calendar.changeView(initialView);
                    }
                },

                // Multi-month view configuration
                views: {
                    multiMonthYear: {
                        type: 'multiMonthYear',
                        duration: { months: 3 },
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

            /*
            //try to update validRange with no luck. Deactivate the whole thing now.
            function updateValidRange(viewName) {
                if (viewName === 'multiMonthYear') {
                    calendar.setOption('validRange', null);
                } else {
                    calendar.setOption('validRange', {
                        start: validRange.start.toISOString?.().split('T')[0] || validRange.start,
                        end: validRange.end.toISOString?.().split('T')[0] || validRange.end
                    });
                }
            }

            updateValidRange(initialView);

            const originalChangeView = calendar.changeView.bind(calendar);
            calendar.changeView = function(viewName, dateOrRange) {
                updateValidRange(viewName);
                return originalChangeView(viewName, dateOrRange);
            };
            */

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