document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var defaultView = calendarEl.getAttribute('data-default-view');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: defaultView,
            events: function (fetchInfo, successCallback, failureCallback) {
                fetch(myCalendarAjax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'fetch_events',
                        start_date: fetchInfo.startStr,
                        end_date: fetchInfo.endStr
                    })
                })
                    .then(response => response.json()   )
                    .then(data => {
                        if (data.success) {
                            successCallback(data.data);
                        } else {
                            failureCallback();
                        }
                    })
                    .catch(() => {
                        failureCallback();
                    });
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                var event = info.event;

                console.log(event.extendedProps);

                // Create and display modal using Vanilla JS
                var modalHtml = `
                    <div id="eventModal" class="mec-event-modal-overlay mec-event-modal-category-${event.extendedProps.categories[0].replace(/\s+/g, '-').toLowerCase()}">
                        <div class="mec-event-modal-content">
                            <div class="mec-event-modal-header">
                                <h2>${event.title}</h2>
                                <button type="button" class="mec-event-modal-close mec-event-modal-close-small">&times;</button>
                            </div>
                            <div class="mec-event-modal-body">
                                ${event.extendedProps.image ? `<img src="${event.extendedProps.image}" alt="${event.title}" class="img-fluid mb-3" />` : ''}
                                <div class="mec-event-modal-details">
                                    <p><strong>Start Date:</strong> ${event.extendedProps.start_datetime_formatted}</p>
                                    ${event.extendedProps.end_datetime_formatted ? `<p><strong>End Date:</strong> ${event.extendedProps.end_datetime_formatted}</p>` : ''}
                                    ${event.extendedProps.all_day_event ? '' : (event.extendedProps.start_time_formatted ? `<p><strong>Start Time:</strong> ${event.extendedProps.start_time_formatted}</p>` : '')}
                                    ${event.extendedProps.all_day_event ? '' : (event.extendedProps.end_time_formatted ? `<p><strong>End Time:</strong> ${event.extendedProps.end_time_formatted}</p>` : '')}
                                    ${event.extendedProps.location ? `<p><strong>Event Location:</strong> ${event.extendedProps.location}</p>` : ''}
                                    ${event.extendedProps.excerpt ? `<p>${event.extendedProps.excerpt}</p>` : ''}
                                </div>
                            </div>
                            <div class="mec-event-modal-footer">
                                <a href="${event.url}" class="btn btn-primary mec-event-modal-view-details">View Details</a>
                                <button type="button" class="btn btn-secondary mec-event-modal-close mec-event-modal-close-button">Close</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Close modal functionality using Vanilla JS
                function closeModal() {
                    var modal = document.getElementById('eventModal');
                    if (modal) {
                        modal.remove();
                    }
                }

                var closeModalButtons = document.querySelectorAll('.mec-event-modal-close');
                closeModalButtons.forEach(function (button) {
                    button.addEventListener('click', closeModal);
                });

                window.addEventListener('click', function (event) {
                    var modal = document.getElementById('eventModal');
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            },
            eventDidMount: function (info) {
                // Apply background and text colors if available
                if (info.event.extendedProps.categoryColor) {
                    info.el.style.backgroundColor = info.event.extendedProps.categoryColor;
                }
                if (info.event.extendedProps.textColor) {
                    info.el.style.color = info.event.extendedProps.textColor;
                }

                // Add category classes if available in extendedProps
                if (info.event.extendedProps.categories) {
                    const categories = info.event.extendedProps.categories;
                    info.el.classList.add('mec-event-category-' + categories[0].replace(/\s+/g, '-').toLowerCase());
                }

                // Set text content for titles (example)
                var titleElement = info.el.querySelector('.fc-event-title');
                if (titleElement) {
                    titleElement.textContent = info.event.extendedProps.formatted_title;
                }            
            }
        });

        calendar.render();
    }
});