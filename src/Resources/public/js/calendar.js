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

    // ============================================================================
    // TRANSLATIONS - Centralized for easy customization
    // ============================================================================
    const translations = {
        de: {
            // Button texts
            today: 'Heute',
            month: 'Monat',
            week: 'Woche',
            day: 'Tag',
            list: 'Liste',
            multiMonthYear: 'Jahr',
            listWeek: 'Wochenliste',
            listMonth: 'Monatsliste',
            view: 'Sicht',
            // Error messages
            errorLoadingEvents: 'Fehler beim Laden der Events'
        },
        en: {
            // Button texts
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List',
            multiMonthYear: 'Year',
            listWeek: 'List Week',
            listMonth: 'List Month',
            view: 'View',
            // Error messages
            errorLoadingEvents: 'Error loading events'
        }
    };

    const t = translations[locale] || translations.en;

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

            // Locale-specific button text (from centralized translations)
            const buttonText = {
                today: t.today,
                month: t.month,
                week: t.week,
                day: t.day,
                list: t.list,
                multiMonthYear: t.multiMonthYear
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
                    listWeek: t.listWeek,
                    listMonth: t.listMonth,
                },
                buttonIcons: false,

                viewHint: function(hint) {
                    return t.view + ' ' + hint;
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

                    // Add clickable link in list view
                    if (info.view.type.startsWith('list')) {
                        const url = info.event.extendedProps?.url || info.event.url;
                        if (url) {
                            const td = info.el.querySelector('.fc-list-event-title');
                            if (td) {
                                const innerHTML = td.innerHTML;
                                td.innerHTML = `<a href="${url}" class="fc-list-event-link">${innerHTML}</a>`;
                            }
                        }
                    }

                    // Get type color from extendedProps.typeColor (API provides it here)
                    const typeColor = extendedProps.typeColor || eventColor;

                    // Set color for style
                    const styleId = 'event-type-style-' + extendedProps.type;
                    if (!document.getElementById(styleId)) {
                        const style = document.createElement('style');
                        style.id = styleId;
                        style.textContent = `.event-type-${extendedProps.type} { --event-type-color: ${typeColor}; }`;
                        document.head.appendChild(style);
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
                    if (toggleType && extendedProps.type) {
                        popoverContent += '<div class="event-popover-type">' +
                            '<span class="event-type-dot event-type-' + extendedProps.type + '" style="background-color: ' + typeColor + ';"></span>' +
                            escapeHtml(extendedProps.type_translation) +
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
                            popoverEl.style.setProperty('--event-type-color', typeColor);
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
                        buttonText: t.multiMonthYear
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
            calendarEl.innerHTML = '<div class="alert alert-danger">' + t.errorLoadingEvents + '</div>';
        });
}