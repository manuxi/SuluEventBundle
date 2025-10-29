import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import deLocale from '@fullcalendar/core/locales/de';
import enLocale from '@fullcalendar/core/locales/en-gb';

export default class EventCalendar {
    /**
     * Initialize the calendar
     * @param {HTMLElement} element - Calendar container element
     * @param {Object} options - Calendar configuration options
     */
    constructor(element, options = {}) {
        this.element = element;
        
        const locales = {
            'de': deLocale,
            'en': enLocale
        };
        
        this.options = {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
            initialView: options.initialView || 'dayGridMonth',
            locale: options.locale || 'de',
            locales: Object.values(locales),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: (info, successCallback, failureCallback) => {
                const url = new URL(options.eventsUrl, window.location.origin);
                url.searchParams.set('start', info.startStr);
                url.searchParams.set('end', info.endStr);
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => failureCallback(error));
            },
            eventClick: this.handleEventClick.bind(this),
            ...options
        };
        
        this.init();
    }

    init() {
        this.calendar = new Calendar(this.element, this.options);
        this.calendar.render();
    }

    /**
     * Handle event click
     * @param {Object} info - Event info object
     */
    handleEventClick(info) {
        if (info.event.url) {
            window.location.href = info.event.url;
            info.jsEvent.preventDefault();
        }
    }

    /**
     * Refresh calendar events
     */
    refetch() {
        this.calendar.refetchEvents();
    }

    /**
     * Destroy calendar instance
     */
    destroy() {
        if (this.calendar) {
            this.calendar.destroy();
        }
    }
}
