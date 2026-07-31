document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.getElementById(
                'globalTimeTracker'
            );

        const csrfToken =
            document
                .querySelector(
                    'meta[name="csrf-token"]'
                )
                ?.getAttribute('content');

        if (!root || !csrfToken) {
            return;
        }

        const currentUrl =
            root.dataset.currentUrl;

        const pauseUrl =
            root.dataset.pauseUrl;

        const resumeUrl =
            root.dataset.resumeUrl;

        const stopUrl =
            root.dataset.stopUrl;

        const taskLink =
            document.getElementById(
                'globalTimeTaskLink'
            );

        const projectText =
            document.getElementById(
                'globalTimeProject'
            );

        const clock =
            document.getElementById(
                'globalTimeClock'
            );

        const pauseButton =
            document.getElementById(
                'globalTimePause'
            );

        const resumeButton =
            document.getElementById(
                'globalTimeResume'
            );

        let activeEntry = null;
        let baseSeconds = 0;
        let synchronizedAt = Date.now();
        let requestRunning = false;

        function formatSeconds(seconds) {
            const safeSeconds = Math.max(
                0,
                Math.floor(seconds || 0)
            );

            const hours = Math.floor(
                safeSeconds / 3600
            );

            const minutes = Math.floor(
                (safeSeconds % 3600) / 60
            );

            const remaining =
                safeSeconds % 60;

            return [
                String(hours).padStart(2, '0'),
                String(minutes).padStart(2, '0'),
                String(remaining).padStart(2, '0'),
            ].join(':');
        }

        function currentSeconds() {
            if (!activeEntry) {
                return 0;
            }

            if (activeEntry.status !== 'running') {
                return baseSeconds;
            }

            const localElapsed = Math.floor(
                (
                    Date.now()
                    - synchronizedAt
                ) / 1000
            );

            return Math.max(
                0,
                baseSeconds + localElapsed
            );
        }

        function setState(payload) {
            activeEntry =
                payload?.active
                    ? payload.entry
                    : null;

            baseSeconds =
                activeEntry
                    ? Number(
                        activeEntry.seconds
                    ) || 0
                    : 0;

            synchronizedAt = Date.now();

            render();

            if (payload?.last_entry) {
                updateStoppedTaskTotal(
                    payload.last_entry
                );
            }

            if (payload?.message) {
                showTimeMessage(
                    payload.message,
                    'success'
                );
            }
        }

        function render() {
            const hasTimer =
                Boolean(activeEntry);

            root.classList.toggle(
                'time-tracking-hidden',
                !hasTimer
            );

            document
                .querySelectorAll(
                    '[data-time-start-url]'
                )
                .forEach(function (button) {
                    button.disabled = hasTimer;
                });

            if (!hasTimer) {
                renderTaskPanels();
                return;
            }

            taskLink.textContent =
                activeEntry.task?.title
                || 'Active Task';

            taskLink.href =
                activeEntry.task?.url
                || '#';

            projectText.textContent = [
                activeEntry.project?.code,
                activeEntry.project?.name,
            ]
                .filter(Boolean)
                .join(' · ');

            pauseButton.classList.toggle(
                'time-tracking-hidden',
                activeEntry.status !== 'running'
            );

            resumeButton.classList.toggle(
                'time-tracking-hidden',
                activeEntry.status !== 'paused'
            );

            root.classList.toggle(
                'timer-paused',
                activeEntry.status === 'paused'
            );

            renderClocks();
            renderTaskPanels();
        }

        function renderClocks() {
            const seconds =
                currentSeconds();

            clock.textContent =
                formatSeconds(seconds);

            document
                .querySelectorAll(
                    '[data-time-task-panel]'
                )
                .forEach(function (panel) {
                    const taskId = Number(
                        panel.dataset
                            .timeTaskPanel
                    );

                    const panelClock =
                        panel.querySelector(
                            '[data-time-task-clock]'
                        );

                    if (
                        panelClock
                        && activeEntry
                        && Number(
                            activeEntry.task?.id
                        ) === taskId
                    ) {
                        panelClock.textContent =
                            formatSeconds(seconds);
                    }
                });
        }

        function renderTaskPanels() {
            document
                .querySelectorAll(
                    '[data-time-task-panel]'
                )
                .forEach(function (panel) {
                    const taskId = Number(
                        panel.dataset
                            .timeTaskPanel
                    );

                    const sameTask =
                        activeEntry
                        && Number(
                            activeEntry.task?.id
                        ) === taskId;

                    const status =
                        panel.querySelector(
                            '[data-time-task-status]'
                        );

                    if (status) {
                        status.textContent =
                            sameTask
                                ? activeEntry.status
                                    .charAt(0)
                                    .toUpperCase()
                                    + activeEntry.status
                                        .slice(1)
                                : 'Inactive';
                    }

                    panel
                        .querySelectorAll(
                            '[data-time-action="pause"]'
                        )
                        .forEach(function (button) {
                            button.classList.toggle(
                                'time-tracking-hidden',
                                !sameTask
                                || activeEntry.status
                                    !== 'running'
                            );
                        });

                    panel
                        .querySelectorAll(
                            '[data-time-action="resume"]'
                        )
                        .forEach(function (button) {
                            button.classList.toggle(
                                'time-tracking-hidden',
                                !sameTask
                                || activeEntry.status
                                    !== 'paused'
                            );
                        });

                    panel
                        .querySelectorAll(
                            '[data-time-action="stop"]'
                        )
                        .forEach(function (button) {
                            button.classList.toggle(
                                'time-tracking-hidden',
                                !sameTask
                            );
                        });
                });
        }

        function updateStoppedTaskTotal(
            stoppedEntry
        ) {
            const taskId = Number(
                stoppedEntry.task?.id
            );

            document
                .querySelectorAll(
                    '[data-time-task-panel]'
                )
                .forEach(function (panel) {
                    if (
                        Number(
                            panel.dataset
                                .timeTaskPanel
                        ) !== taskId
                    ) {
                        return;
                    }

                    const total =
                        panel.querySelector(
                            '[data-time-task-total]'
                        );

                    if (total) {
                        total.textContent =
                            formatSeconds(
                                stoppedEntry
                                    .task_total_seconds
                            );
                    }

                    const panelClock =
                        panel.querySelector(
                            '[data-time-task-clock]'
                        );

                    if (panelClock) {
                        panelClock.textContent =
                            '00:00:00';
                    }
                });
        }

        async function fetchCurrent() {
            if (
                requestRunning
                || document.hidden
            ) {
                return;
            }

            requestRunning = true;

            try {
                const url = new URL(
                    currentUrl,
                    window.location.origin
                );

                url.searchParams.set(
                    '_',
                    String(Date.now())
                );

                const response = await fetch(
                    url.toString(),
                    {
                        headers: {
                            Accept:
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials:
                            'same-origin',

                        cache: 'no-store',
                    }
                );

                if (
                    response.status === 401
                    || response.status === 419
                ) {
                    return;
                }

                const payload =
                    await response.json();

                if (!response.ok) {
                    throw new Error(
                        extractError(payload)
                    );
                }

                setState(payload);
            } catch (error) {
                console.warn(
                    'Time tracking sync error:',
                    error
                );
            } finally {
                requestRunning = false;
            }
        }

        async function postJson(
            url,
            data = {}
        ) {
            if (requestRunning) {
                return null;
            }

            requestRunning = true;
            toggleButtons(true);

            try {
                const response = await fetch(
                    url,
                    {
                        method: 'POST',

                        headers: {
                            Accept:
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },

                        credentials:
                            'same-origin',

                        body:
                            JSON.stringify(data),
                    }
                );

                const payload =
                    await response.json();

                if (!response.ok) {
                    throw new Error(
                        extractError(payload)
                    );
                }

                setState(payload);

                return payload;
            } catch (error) {
                showTimeMessage(
                    error.message
                    || 'Time tracking request failed.',
                    'error'
                );

                return null;
            } finally {
                requestRunning = false;
                toggleButtons(false);
            }
        }

        function extractError(payload) {
            if (payload?.errors) {
                const firstError =
                    Object.values(
                        payload.errors
                    )[0];

                if (
                    Array.isArray(firstError)
                    && firstError[0]
                ) {
                    return firstError[0];
                }
            }

            return payload?.message
                || 'Time tracking request failed.';
        }

        function toggleButtons(disabled) {
            document
                .querySelectorAll(
                    '[data-time-start-url], [data-time-action]'
                )
                .forEach(function (button) {
                    button.disabled = disabled;
                });
        }

        function workNote() {
            if (!activeEntry) {
                return '';
            }

            const panel =
                document.querySelector(
                    `[data-time-task-panel="${activeEntry.task?.id}"]`
                );

            return panel
                ?.querySelector(
                    '[data-time-note]'
                )
                ?.value
                ?.trim()
                || '';
        }

        document.addEventListener(
            'click',
            function (event) {
                const startButton =
                    event.target.closest(
                        '[data-time-start-url]'
                    );

                if (startButton) {
                    event.preventDefault();

                    const panel =
                        startButton.closest(
                            '[data-time-task-panel]'
                        );

                    const notes =
                        panel
                            ?.querySelector(
                                '[data-time-note]'
                            )
                            ?.value
                            ?.trim()
                        || '';

                    postJson(
                        startButton.dataset
                            .timeStartUrl,
                        {
                            notes: notes,
                        }
                    );

                    return;
                }

                const actionButton =
                    event.target.closest(
                        '[data-time-action]'
                    );

                if (!actionButton) {
                    return;
                }

                event.preventDefault();

                const action =
                    actionButton.dataset
                        .timeAction;

                if (action === 'pause') {
                    postJson(pauseUrl);
                }

                if (action === 'resume') {
                    postJson(resumeUrl);
                }

                if (action === 'stop') {
                    const confirmed =
                        window.confirm(
                            'End the current work session?'
                        );

                    if (!confirmed) {
                        return;
                    }

                    postJson(
                        stopUrl,
                        {
                            notes: workNote(),
                        }
                    );
                }
            }
        );

        function showTimeMessage(
            message,
            type
        ) {
            const box =
                document.createElement('div');

            box.className =
                `time-tracking-message ${type}`;

            box.textContent = message;

            document.body.appendChild(box);

            window.setTimeout(
                function () {
                    box.classList.add(
                        'removing'
                    );

                    window.setTimeout(
                        function () {
                            box.remove();
                        },
                        220
                    );
                },
                3500
            );
        }

        /*
         * Local visual timer.
         */
        window.setInterval(
            function () {
                if (
                    activeEntry
                    && activeEntry.status
                        === 'running'
                ) {
                    renderClocks();
                }
            },
            1000
        );

        /*
         * Database ke saath regular sync.
         */
        window.setInterval(
            fetchCurrent,
            15000
        );

        document.addEventListener(
            'visibilitychange',
            function () {
                if (!document.hidden) {
                    fetchCurrent();
                }
            }
        );

        window.addEventListener(
            'focus',
            fetchCurrent
        );

        fetchCurrent();
    }
);