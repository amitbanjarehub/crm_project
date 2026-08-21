document.addEventListener(
    'DOMContentLoaded',
    function () {
        'use strict';

        const app =
            document.getElementById(
                'taskKanbanApp'
            );

        if (!app) {
            return;
        }

        const boardContainer =
            document.getElementById(
                'taskKanbanBoardContainer'
            );

        const filterForm =
            document.getElementById(
                'taskKanbanFilterForm'
            );

        const groupBy =
            document.getElementById(
                'taskKanbanGroupBy'
            );

        const searchInput =
            document.getElementById(
                'taskKanbanSearch'
            );

        const statusFilter =
            document.getElementById(
                'taskKanbanStatus'
            );

        const priorityFilter =
            document.getElementById(
                'taskKanbanPriority'
            );

        const dueFilter =
            document.getElementById(
                'taskKanbanDue'
            );

        const hideEmpty =
            document.getElementById(
                'taskKanbanHideEmpty'
            );

        const refreshButton =
            document.getElementById(
                'taskKanbanRefresh'
            );

        const resetButton =
            document.getElementById(
                'taskKanbanReset'
            );

        const undoButton =
            document.getElementById(
                'taskKanbanUndo'
            );

        const savingState =
            document.getElementById(
                'taskKanbanSavingState'
            );

        const totalTasks =
            document.getElementById(
                'taskKanbanTotal'
            );

        const updatedAt =
            document.getElementById(
                'taskKanbanUpdated'
            );

        const drawerOverlay =
            document.getElementById(
                'taskKanbanDrawerOverlay'
            );

        const drawer =
            document.getElementById(
                'taskKanbanDrawer'
            );

        const toastContainer =
            document.getElementById(
                'taskKanbanToastContainer'
            );

        const csrfToken =
            document
                .querySelector(
                    'meta[name="csrf-token"]'
                )
                ?.getAttribute(
                    'content'
                );

        /*
        |--------------------------------------------------------------------------
        | DRAG STATE
        |--------------------------------------------------------------------------
        */

        let draggedCard = null;
        let cardSnapshot = null;
        let cardDropHandled = false;

        let draggedColumn = null;
        let columnSnapshot = null;
        let columnDropHandled = false;

        let isDragging = false;

        let pendingRequests = 0;
        let searchTimer = null;
        let refreshTimer = null;

        let lastUndo = null;
        let selectedCard = null;


        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function replaceTask(
            template,
            taskId
        ) {
            return template.replace(
                '__TASK__',
                String(taskId)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SAVING STATE
        |--------------------------------------------------------------------------
        */

        function updateSavingState(
            text,
            state
        ) {
            if (!savingState) {
                return;
            }

            savingState.textContent =
                text;

            savingState.className =
                'task-kanban-saving ' +
                state;
        }


        /*
        |--------------------------------------------------------------------------
        | API REQUEST
        |--------------------------------------------------------------------------
        */

        async function apiRequest(
            url,
            options = {}
        ) {
            pendingRequests++;

            /*
             * IMPORTANT:
             * This global saving indicator is allowed.
             *
             * But we NEVER add .saving class to
             * dragged cards or columns.
             */

            updateSavingState(
                'Saving...',
                'saving'
            );

            try {
                const response =
                    await fetch(
                        url,
                        {
                            method:
                                options.method
                                || 'GET',

                            headers: {
                                Accept:
                                    'application/json',

                                'Content-Type':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'X-CSRF-TOKEN':
                                    csrfToken,
                            },

                            body:
                                options.body
                                    ? JSON.stringify(
                                        options.body
                                    )
                                    : undefined,
                        }
                    );

                let payload = {};

                try {
                    payload =
                        await response.json();
                } catch (error) {
                    payload = {};
                }

                if (!response.ok) {
                    const message =
                        payload.message
                        || (
                            payload.errors
                                ? Object.values(
                                    payload.errors
                                ).flat()[0]
                                : null
                        )
                        || 'Request failed.';

                    const error =
                        new Error(
                            message
                        );

                    error.status =
                        response.status;

                    throw error;
                }

                return payload;

            } finally {

                pendingRequests =
                    Math.max(
                        0,
                        pendingRequests - 1
                    );

                if (
                    pendingRequests === 0
                ) {
                    updateSavingState(
                        'Saved',
                        'saved'
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TOAST
        |--------------------------------------------------------------------------
        */

        function showToast(
            message,
            type = 'success'
        ) {
            if (!toastContainer) {
                return;
            }

            const toast =
                document.createElement(
                    'div'
                );

            toast.className =
                'task-kanban-toast ' +
                type;

            toast.textContent =
                message;

            toastContainer.appendChild(
                toast
            );

            requestAnimationFrame(
                function () {
                    toast.classList.add(
                        'show'
                    );
                }
            );

            setTimeout(
                function () {
                    toast.classList.remove(
                        'show'
                    );

                    setTimeout(
                        function () {
                            toast.remove();
                        },
                        250
                    );
                },
                3500
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT BOARD
        |--------------------------------------------------------------------------
        */

        function currentBoard() {
            return document.getElementById(
                'taskKanbanBoard'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | QUERY URL
        |--------------------------------------------------------------------------
        */

        function buildQueryUrl() {
            const url =
                new URL(
                    app.dataset.boardUrl,
                    window.location.origin
                );

            const formData =
                new FormData(
                    filterForm
                );

            formData.forEach(
                function (
                    value,
                    key
                ) {
                    const cleaned =
                        String(value).trim();

                    if (cleaned !== '') {
                        url.searchParams.set(
                            key,
                            cleaned
                        );
                    }
                }
            );

            url.searchParams.set(
                'group_by',
                groupBy.value
            );

            return url;
        }


        /*
        |--------------------------------------------------------------------------
        | BROWSER URL
        |--------------------------------------------------------------------------
        */

        function updateBrowserUrl() {
            const url =
                new URL(
                    window.location.href
                );

            [
                'group_by',
                'search',
                'status',
                'priority',
                'due',
            ].forEach(
                function (key) {
                    url.searchParams.delete(
                        key
                    );
                }
            );

            const formData =
                new FormData(
                    filterForm
                );

            formData.forEach(
                function (
                    value,
                    key
                ) {
                    const cleaned =
                        String(value).trim();

                    if (cleaned !== '') {
                        url.searchParams.set(
                            key,
                            cleaned
                        );
                    }
                }
            );

            url.searchParams.set(
                'group_by',
                groupBy.value
            );

            window.history.replaceState(
                {},
                '',
                url
            );
        }


        /*
        |--------------------------------------------------------------------------
        | REFRESH BOARD
        |--------------------------------------------------------------------------
        */

        async function refreshBoard(
            silent = false
        ) {
            if (
                isDragging
                || pendingRequests > 0
            ) {
                return;
            }

            if (!silent) {
                boardContainer.classList.add(
                    'is-loading'
                );
            }

            try {
                const response =
                    await fetch(
                        buildQueryUrl(),
                        {
                            headers: {
                                Accept:
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                if (!response.ok) {
                    throw new Error(
                        'Unable to refresh Task Kanban board.'
                    );
                }

                const payload =
                    await response.json();

                closeDrawer();

                boardContainer.innerHTML =
                    payload.html;

                if (totalTasks) {
                    totalTasks.textContent =
                        payload.total;
                }

                if (updatedAt) {
                    updatedAt.textContent =
                        payload.updated_at;
                }

                updateBrowserUrl();

                if (!silent) {
                    showToast(
                        'Task Kanban board refreshed.'
                    );
                }

            } catch (error) {

                if (!silent) {
                    showToast(
                        error.message,
                        'error'
                    );
                }

            } finally {

                boardContainer.classList.remove(
                    'is-loading'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PREVIOUS TASK
        |--------------------------------------------------------------------------
        */

        function previousTaskId(
            card
        ) {
            let element =
                card.previousElementSibling;

            while (element) {

                if (
                    element.classList.contains(
                        'task-kanban-card'
                    )
                ) {
                    return Number(
                        element.dataset.taskId
                    );
                }

                element =
                    element.previousElementSibling;
            }

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | NEXT TASK
        |--------------------------------------------------------------------------
        */

        function nextTaskId(
            card
        ) {
            let element =
                card.nextElementSibling;

            while (element) {

                if (
                    element.classList.contains(
                        'task-kanban-card'
                    )
                ) {
                    return Number(
                        element.dataset.taskId
                    );
                }

                element =
                    element.nextElementSibling;
            }

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD POSITION
        |--------------------------------------------------------------------------
        */

        function getCardAfterElement(
            container,
            mouseY
        ) {
            const cards =
                Array.from(
                    container.querySelectorAll(
                        '.task-kanban-card:not(.dragging)'
                    )
                );

            return cards.reduce(
                function (
                    closest,
                    child
                ) {
                    const box =
                        child.getBoundingClientRect();

                    const offset =
                        mouseY
                        - box.top
                        - box.height / 2;

                    if (
                        offset < 0
                        && offset
                            > closest.offset
                    ) {
                        return {
                            offset,
                            element:
                                child,
                        };
                    }

                    return closest;
                },
                {
                    offset:
                        Number.NEGATIVE_INFINITY,

                    element:
                        null,
                }
            ).element;
        }


        /*
        |--------------------------------------------------------------------------
        | COLUMN POSITION
        |--------------------------------------------------------------------------
        */

        function getColumnAfterElement(
            board,
            mouseX
        ) {
            const columns =
                Array.from(
                    board.querySelectorAll(
                        '.task-kanban-column:not(.column-dragging)'
                    )
                );

            return columns.reduce(
                function (
                    closest,
                    column
                ) {
                    const box =
                        column.getBoundingClientRect();

                    const offset =
                        mouseX
                        - box.left
                        - box.width / 2;

                    if (
                        offset < 0
                        && offset
                            > closest.offset
                    ) {
                        return {
                            offset,
                            element:
                                column,
                        };
                    }

                    return closest;
                },
                {
                    offset:
                        Number.NEGATIVE_INFINITY,

                    element:
                        null,
                }
            ).element;
        }


        /*
        |--------------------------------------------------------------------------
        | COLUMN COUNTS
        |--------------------------------------------------------------------------
        */

        function updateColumnCounts() {

            document
                .querySelectorAll(
                    '.task-kanban-column'
                )
                .forEach(
                    function (column) {

                        const list =
                            column.querySelector(
                                '.task-kanban-card-list'
                            );

                        const cards =
                            list
                                ? list.querySelectorAll(
                                    '.task-kanban-card'
                                )
                                : [];

                        const count =
                            column.querySelector(
                                '.task-kanban-column-count'
                            );

                        if (count) {
                            count.textContent =
                                cards.length;
                        }

                        if (
                            cards.length === 0
                            && list
                            && !list.querySelector(
                                '.task-kanban-empty-column'
                            )
                        ) {
                            const empty =
                                document.createElement(
                                    'div'
                                );

                            empty.className =
                                'task-kanban-empty-column';

                            empty.textContent =
                                'Drop a Task here';

                            list.appendChild(
                                empty
                            );
                        }

                        if (
                            cards.length > 0
                        ) {
                            list
                                ?.querySelector(
                                    '.task-kanban-empty-column'
                                )
                                ?.remove();
                        }
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | RESTORE CARD
        |--------------------------------------------------------------------------
        */

        function restoreCard(
            card,
            snapshot
        ) {
            if (
                !card
                || !snapshot
                || !snapshot.parent
            ) {
                return;
            }

            if (
                snapshot.nextSibling
                && snapshot.nextSibling.parentNode
                    === snapshot.parent
            ) {
                snapshot.parent.insertBefore(
                    card,
                    snapshot.nextSibling
                );
            } else {
                snapshot.parent.appendChild(
                    card
                );
            }

            /*
             * IMPORTANT:
             * Never leave dragging/saving classes.
             */

            card.classList.remove(
                'dragging',
                'saving'
            );

            updateColumnCounts();
        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR DRAG VISUAL STATE
        |--------------------------------------------------------------------------
        */

        function clearDragVisualState() {

            if (draggedCard) {
                draggedCard.classList.remove(
                    'dragging',
                    'saving'
                );

                draggedCard.style.removeProperty(
                    'opacity'
                );

                draggedCard.style.removeProperty(
                    'filter'
                );

                draggedCard.style.removeProperty(
                    'transform'
                );
            }

            if (draggedColumn) {
                draggedColumn.classList.remove(
                    'column-dragging',
                    'saving'
                );

                draggedColumn.style.removeProperty(
                    'opacity'
                );

                draggedColumn.style.removeProperty(
                    'filter'
                );

                draggedColumn.style.removeProperty(
                    'transform'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | APPLY SERVER TASK RESPONSE
        |--------------------------------------------------------------------------
        */

        function applyTaskResponse(
            card,
            task
        ) {
            if (
                !card
                || !task
            ) {
                return;
            }

            /*
             * Remove every temporary visual state.
             */

            card.classList.remove(
                'saving',
                'dragging'
            );

            card.style.removeProperty(
                'opacity'
            );

            card.style.removeProperty(
                'filter'
            );

            card.style.removeProperty(
                'transform'
            );

            /*
             * STATUS
             */

            if (
                task.status !== undefined
            ) {
                card.dataset.status =
                    task.status;
            }

            /*
             * PRIORITY
             */

            if (
                task.priority !== undefined
            ) {
                card.dataset.priority =
                    task.priority;
            }

            /*
             * STATUS BADGE
             */

            const statusBadge =
                card.querySelector(
                    '.task-kanban-card-status'
                );

            if (statusBadge) {

                if (
                    typeof task.status
                    === 'object'
                    && task.status !== null
                ) {
                    statusBadge.textContent =
                        task.status.name
                        || task.status.label
                        || '';

                    if (
                        task.status.color
                    ) {
                        statusBadge.style.setProperty(
                            '--kanban-badge-color',
                            task.status.color
                        );
                    }

                } else {

                    statusBadge.textContent =
                        String(
                            task.status
                            || ''
                        );
                }
            }

            /*
             * PRIORITY BADGE
             */

            const priorityBadge =
                card.querySelector(
                    '.task-kanban-card-priority'
                );

            if (priorityBadge) {

                if (
                    typeof task.priority
                    === 'object'
                    && task.priority !== null
                ) {

                    priorityBadge.textContent =
                        task.priority.name
                        || task.priority.label
                        || '';

                    if (
                        task.priority.color
                    ) {
                        priorityBadge.style.setProperty(
                            '--kanban-badge-color',
                            task.priority.color
                        );
                    }

                } else {

                    priorityBadge.textContent =
                        String(
                            task.priority
                            || ''
                        );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE CARD VISUAL STATE
        |--------------------------------------------------------------------------
        */

        function updateTaskCardVisualState(
            card,
            targetColumn,
            task = null
        ) {
            if (!card) {
                return;
            }

            /*
             * NEVER blur card after drop.
             */

            card.classList.remove(
                'saving',
                'dragging'
            );

            card.style.removeProperty(
                'opacity'
            );

            card.style.removeProperty(
                'filter'
            );

            card.style.removeProperty(
                'transform'
            );

            /*
             * Save current column.
             */

            if (targetColumn) {
                card.dataset.columnSlug =
                    targetColumn;
            }

            /*
             * Server response.
             */

            if (task) {

                if (
                    task.status !== undefined
                ) {
                    card.dataset.status =
                        task.status;
                }

                if (
                    task.priority !== undefined
                ) {
                    card.dataset.priority =
                        task.priority;
                }
            }

            /*
             * Status badge.
             */

            const statusBadge =
                card.querySelector(
                    '[data-task-status]'
                )
                ||
                card.querySelector(
                    '.task-kanban-card-status'
                );

            if (
                statusBadge
                && task
                && task.status !== undefined
            ) {

                if (
                    typeof task.status
                    === 'object'
                    && task.status !== null
                ) {
                    statusBadge.textContent =
                        task.status.name
                        || task.status.label
                        || '';

                    if (
                        task.status.color
                    ) {
                        statusBadge.style.setProperty(
                            '--kanban-badge-color',
                            task.status.color
                        );
                    }

                } else {

                    statusBadge.textContent =
                        task.status;
                }
            }

            /*
             * Priority badge.
             */

            const priorityBadge =
                card.querySelector(
                    '[data-task-priority]'
                )
                ||
                card.querySelector(
                    '.task-kanban-card-priority'
                );

            if (
                priorityBadge
                && task
                && task.priority !== undefined
            ) {

                if (
                    typeof task.priority
                    === 'object'
                    && task.priority !== null
                ) {
                    priorityBadge.textContent =
                        task.priority.name
                        || task.priority.label
                        || '';

                    if (
                        task.priority.color
                    ) {
                        priorityBadge.style.setProperty(
                            '--kanban-badge-color',
                            task.priority.color
                        );
                    }

                } else {

                    priorityBadge.textContent =
                        task.priority;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PERSIST CARD MOVE
        |--------------------------------------------------------------------------
        */

        async function persistCardMove(
            card,
            targetList,
            snapshot
        ) {

            const taskId =
                Number(
                    card.dataset.taskId
                );

            const targetColumn =
                targetList.dataset.columnSlug;

            /*
             * ---------------------------------------------------------------
             * IMPORTANT FIX
             * ---------------------------------------------------------------
             *
             * Drop hote hi card completely normal rahega.
             *
             * NO .saving class
             * NO opacity
             * NO filter
             * NO overlay
             *
             * ---------------------------------------------------------------
             */

            card.classList.remove(
                'saving',
                'dragging'
            );

            card.style.removeProperty(
                'opacity'
            );

            card.style.removeProperty(
                'filter'
            );

            card.style.removeProperty(
                'transform'
            );

            /*
             * Immediately update target column.
             */

            updateTaskCardVisualState(
                card,
                targetColumn
            );

            /*
             * Immediately update counts.
             */

            updateColumnCounts();

            /*
             * Capture position BEFORE API call.
             */

            const beforeId =
                previousTaskId(
                    card
                );

            const afterId =
                nextTaskId(
                    card
                );

            /*
             * API request runs in background.
             */

            try {

                const response =
                    await apiRequest(
                        replaceTask(
                            app.dataset
                                .moveUrlTemplate,
                            taskId
                        ),
                        {
                            method:
                                'PATCH',

                            body: {
                                group_by:
                                    groupBy.value,

                                target_column:
                                    targetColumn,

                                before_id:
                                    beforeId,

                                after_id:
                                    afterId,
                            },
                        }
                    );

                /*
                 * Server response.
                 */

                const task =
                    response.task;

                if (task) {

                    /*
                     * Final server state.
                     */

                    if (
                        task.status
                        !== undefined
                    ) {
                        card.dataset.status =
                            task.status;
                    }

                    if (
                        task.priority
                        !== undefined
                    ) {
                        card.dataset.priority =
                            task.priority;
                    }

                    /*
                     * Apply final UI.
                     */

                    updateTaskCardVisualState(
                        card,
                        targetColumn,
                        task
                    );
                }

                /*
                 * Save undo information.
                 */

                lastUndo = {
                    taskId,

                    card,

                    targetColumn:
                        snapshot.column,

                    previousColumn:
                        snapshot.column,

                    snapshot,
                };

                updateUndoButton();

                /*
                 * Make absolutely sure card
                 * remains clear.
                 */

                card.classList.remove(
                    'saving',
                    'dragging'
                );

                card.style.removeProperty(
                    'opacity'
                );

                card.style.removeProperty(
                    'filter'
                );

                card.style.removeProperty(
                    'transform'
                );

                updateColumnCounts();

                showToast(
                    'Task moved successfully.'
                );

            } catch (error) {

                /*
                 * API failed.
                 */

                restoreCard(
                    card,
                    snapshot
                );

                if (
                    error.status === 409
                ) {
                    await refreshBoard(
                        true
                    );
                }

                showToast(
                    error.message,
                    'error'
                );

            } finally {

                /*
                 * Final safety cleanup.
                 */

                card.classList.remove(
                    'saving',
                    'dragging'
                );

                card.style.removeProperty(
                    'opacity'
                );

                card.style.removeProperty(
                    'filter'
                );

                card.style.removeProperty(
                    'transform'
                );

                updateColumnCounts();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RESTORE COLUMN
        |--------------------------------------------------------------------------
        */

        function restoreColumn() {

            if (!draggedColumn) {
                return;
            }

            if (
                columnSnapshot?.nextSibling
                && columnSnapshot.nextSibling
                    .parentNode
                === columnSnapshot.parent
            ) {

                columnSnapshot.parent.insertBefore(
                    draggedColumn,
                    columnSnapshot.nextSibling
                );

            } else {

                columnSnapshot?.parent?.appendChild(
                    draggedColumn
                );
            }

            /*
             * Remove visual states.
             */

            draggedColumn.classList.remove(
                'column-dragging',
                'saving'
            );

            draggedColumn.style.removeProperty(
                'opacity'
            );

            draggedColumn.style.removeProperty(
                'filter'
            );

            draggedColumn.style.removeProperty(
                'transform'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DRAG START
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'dragstart',
            function (event) {

                /*
                 * CARD DRAG
                 */

                const cardHandle =
                    event.target.closest(
                        '.task-kanban-card-handle'
                    );

                if (cardHandle) {

                    draggedCard =
                        cardHandle.closest(
                            '.task-kanban-card'
                        );

                    if (!draggedCard) {
                        return;
                    }

                    cardSnapshot = {
                        parent:
                            draggedCard.parentElement,

                        nextSibling:
                            draggedCard
                                .nextElementSibling,

                        column:
                            draggedCard
                                .parentElement
                                .dataset
                                .columnSlug,
                    };

                    cardDropHandled =
                        false;

                    isDragging =
                        true;

                    /*
                     * Only drag visual.
                     */

                    draggedCard.classList.add(
                        'dragging'
                    );

                    event.dataTransfer.effectAllowed =
                        'move';

                    event.dataTransfer.setData(
                        'text/plain',
                        draggedCard.dataset.taskId
                    );

                    return;
                }


                /*
                 * COLUMN DRAG
                 */

                const columnHandle =
                    event.target.closest(
                        '.task-kanban-column-handle'
                    );

                if (columnHandle) {

                    draggedColumn =
                        columnHandle.closest(
                            '.task-kanban-column'
                        );

                    if (!draggedColumn) {
                        return;
                    }

                    columnSnapshot = {
                        parent:
                            draggedColumn.parentElement,

                        nextSibling:
                            draggedColumn
                                .nextElementSibling,
                    };

                    columnDropHandled =
                        false;

                    isDragging =
                        true;

                    draggedColumn.classList.add(
                        'column-dragging'
                    );

                    event.dataTransfer.effectAllowed =
                        'move';

                    event.dataTransfer.setData(
                        'text/plain',
                        draggedColumn.dataset
                            .columnSlug
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAG OVER
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'dragover',
            function (event) {

                /*
                 * CARD
                 */

                if (draggedCard) {

                    const list =
                        event.target.closest(
                            '.task-kanban-card-list'
                        );

                    if (!list) {
                        return;
                    }

                    event.preventDefault();

                    list
                        .querySelector(
                            '.task-kanban-empty-column'
                        )
                        ?.remove();

                    const after =
                        getCardAfterElement(
                            list,
                            event.clientY
                        );

                    if (!after) {

                        list.appendChild(
                            draggedCard
                        );

                    } else {

                        list.insertBefore(
                            draggedCard,
                            after
                        );
                    }

                    return;
                }


                /*
                 * COLUMN
                 */

                if (draggedColumn) {

                    const board =
                        currentBoard();

                    if (!board) {
                        return;
                    }

                    event.preventDefault();

                    const after =
                        getColumnAfterElement(
                            board,
                            event.clientX
                        );

                    if (!after) {

                        board.appendChild(
                            draggedColumn
                        );

                    } else {

                        board.insertBefore(
                            draggedColumn,
                            after
                        );
                    }
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DROP
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'drop',
            async function (event) {

                /*
                 * CARD DROP
                 */

                if (draggedCard) {

                    const list =
                        event.target.closest(
                            '.task-kanban-card-list'
                        );

                    if (!list) {
                        return;
                    }

                    event.preventDefault();

                    cardDropHandled =
                        true;

                    /*
                     * Remove dragging state
                     * IMMEDIATELY.
                     */

                    draggedCard.classList.remove(
                        'dragging',
                        'saving'
                    );

                    draggedCard.style.removeProperty(
                        'opacity'
                    );

                    draggedCard.style.removeProperty(
                        'filter'
                    );

                    draggedCard.style.removeProperty(
                        'transform'
                    );

                    updateColumnCounts();

                    await persistCardMove(
                        draggedCard,
                        list,
                        cardSnapshot
                    );

                    return;
                }


                /*
                 * COLUMN DROP
                 */

                if (draggedColumn) {

                    const board =
                        currentBoard();

                    if (!board) {
                        return;
                    }

                    event.preventDefault();

                    columnDropHandled =
                        true;

                    /*
                     * Remove visual state immediately.
                     */

                    draggedColumn.classList.remove(
                        'column-dragging',
                        'saving'
                    );

                    draggedColumn.style.removeProperty(
                        'opacity'
                    );

                    draggedColumn.style.removeProperty(
                        'filter'
                    );

                    draggedColumn.style.removeProperty(
                        'transform'
                    );

                    const columns =
                        Array.from(
                            board.querySelectorAll(
                                '.task-kanban-column'
                            )
                        ).map(
                            function (
                                column
                            ) {
                                return column
                                    .dataset
                                    .columnSlug;
                            }
                        );

                    try {

                        await apiRequest(
                            app.dataset
                                .columnOrderUrl,
                            {
                                method:
                                    'PUT',

                                body: {
                                    group_by:
                                        groupBy.value,

                                    columns,
                                },
                            }
                        );

                        /*
                         * Make sure column is normal.
                         */

                        draggedColumn.classList.remove(
                            'column-dragging',
                            'saving'
                        );

                        draggedColumn.style.removeProperty(
                            'opacity'
                        );

                        draggedColumn.style.removeProperty(
                            'filter'
                        );

                        draggedColumn.style.removeProperty(
                            'transform'
                        );

                        showToast(
                            'Column order saved.'
                        );

                    } catch (error) {

                        restoreColumn();

                        showToast(
                            error.message,
                            'error'
                        );
                    }
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAG END
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'dragend',
            function () {

                /*
                 * Always clear visual states.
                 */

                clearDragVisualState();

                /*
                 * If card was not successfully
                 * dropped, restore it.
                 */

                if (
                    draggedCard
                    && !cardDropHandled
                    && cardSnapshot
                ) {
                    restoreCard(
                        draggedCard,
                        cardSnapshot
                    );
                }

                /*
                 * If column was not successfully
                 * dropped, restore it.
                 */

                if (
                    draggedColumn
                    && !columnDropHandled
                ) {
                    restoreColumn();
                }

                /*
                 * Reset drag state.
                 */

                draggedCard = null;
                cardSnapshot = null;
                cardDropHandled = false;

                draggedColumn = null;
                columnSnapshot = null;
                columnDropHandled = false;

                isDragging = false;

                updateColumnCounts();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | UNDO BUTTON
        |--------------------------------------------------------------------------
        */

        function updateUndoButton() {

            if (!undoButton) {
                return;
            }

            undoButton.disabled =
                !lastUndo;
        }


        undoButton?.addEventListener(
            'click',
            async function () {

                if (
                    !lastUndo
                    || pendingRequests > 0
                ) {
                    return;
                }

                const card =
                    document.querySelector(
                        '.task-kanban-card[data-task-id="' +
                        lastUndo.taskId +
                        '"]'
                    );

                if (!card) {

                    lastUndo = null;

                    updateUndoButton();

                    return;
                }

                const targetList =
                    document.querySelector(
                        '.task-kanban-card-list[data-column-slug="' +
                        CSS.escape(
                            lastUndo.targetColumn
                        ) +
                        '"]'
                    );

                if (!targetList) {
                    return;
                }

                const rollbackSnapshot = {
                    parent:
                        card.parentElement,

                    nextSibling:
                        card.nextElementSibling,
                };

                targetList.appendChild(
                    card
                );

                /*
                 * Immediately keep card clear.
                 */

                card.classList.remove(
                    'saving',
                    'dragging'
                );

                card.style.removeProperty(
                    'opacity'
                );

                card.style.removeProperty(
                    'filter'
                );

                card.style.removeProperty(
                    'transform'
                );

                try {

                    await apiRequest(
                        replaceTask(
                            app.dataset
                                .moveUrlTemplate,
                            lastUndo.taskId
                        ),
                        {
                            method:
                                'PATCH',

                            body: {
                                group_by:
                                    groupBy.value,

                                target_column:
                                    lastUndo
                                        .targetColumn,

                                before_id:
                                    null,

                                after_id:
                                    null,
                            },
                        }
                    );

                    lastUndo = null;

                    updateUndoButton();

                    showToast(
                        'Last move undone.'
                    );

                } catch (error) {

                    restoreCard(
                        card,
                        rollbackSnapshot
                    );

                    showToast(
                        error.message,
                        'error'
                    );
                }

                updateColumnCounts();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAWER
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function (event) {

                if (
                    event.target.closest(
                        '[data-no-drawer]'
                    )
                ) {
                    return;
                }

                /*
                 * Don't open drawer after drag.
                 */

                if (isDragging) {
                    return;
                }

                const card =
                    event.target.closest(
                        '.task-kanban-card'
                    );

                if (!card) {
                    return;
                }

                openDrawer(
                    card
                );
            }
        );


        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Enter'
                    && event.target.classList.contains(
                        'task-kanban-card'
                    )
                ) {
                    openDrawer(
                        event.target
                    );
                }

                if (
                    event.key === 'Escape'
                ) {
                    closeDrawer();
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | OPEN DRAWER
        |--------------------------------------------------------------------------
        */

        async function openDrawer(
            card
        ) {

            const taskId =
                card.dataset.taskId;

            selectedCard
                ?.classList.remove(
                    'selected'
                );

            selectedCard =
                card;

            selectedCard.classList.add(
                'selected'
            );

            drawerOverlay.classList.add(
                'open'
            );

            drawerOverlay.setAttribute(
                'aria-hidden',
                'false'
            );

            drawer.innerHTML =
                '<div class="task-kanban-drawer-loading">'
                + 'Loading Task details...'
                + '</div>';

            try {

                const response =
                    await fetch(
                        replaceTask(
                            app.dataset
                                .detailsUrlTemplate,
                            taskId
                        ),
                        {
                            headers: {
                                Accept:
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                if (!response.ok) {
                    throw new Error(
                        'Task details could not be loaded.'
                    );
                }

                const payload =
                    await response.json();

                drawer.innerHTML =
                    payload.html;

            } catch (error) {

                drawer.innerHTML =
                    '<div class="task-kanban-drawer-error">'
                    + escapeHtml(
                        error.message
                    )
                    + '</div>';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE DRAWER
        |--------------------------------------------------------------------------
        */

        function closeDrawer() {

            drawerOverlay.classList.remove(
                'open'
            );

            drawerOverlay.setAttribute(
                'aria-hidden',
                'true'
            );

            selectedCard
                ?.classList.remove(
                    'selected'
                );

            selectedCard = null;
        }


        document.addEventListener(
            'click',
            function (event) {

                if (
                    event.target.id
                    === 'taskKanbanDrawerClose'
                ) {
                    closeDrawer();
                }

                if (
                    event.target
                    === drawerOverlay
                ) {
                    closeDrawer();
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(
            value
        ) {
            const div =
                document.createElement(
                    'div'
                );

            div.textContent =
                String(value);

            return div.innerHTML;
        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        searchInput?.addEventListener(
            'input',
            function () {

                clearTimeout(
                    searchTimer
                );

                searchTimer =
                    setTimeout(
                        function () {
                            refreshBoard();
                        },
                        350
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        [
            groupBy,
            statusFilter,
            priorityFilter,
            dueFilter,
        ].forEach(
            function (
                element
            ) {

                element?.addEventListener(
                    'change',
                    async function () {

                        if (
                            element
                            === groupBy
                        ) {

                            try {

                                await apiRequest(
                                    app.dataset
                                        .preferenceUrl,
                                    {
                                        method:
                                            'PATCH',

                                        body: {
                                            group_by:
                                                groupBy.value,
                                        },
                                    }
                                );

                            } catch (error) {

                                showToast(
                                    error.message,
                                    'error'
                                );
                            }
                        }

                        refreshBoard();
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | HIDE EMPTY
        |--------------------------------------------------------------------------
        */

        hideEmpty?.addEventListener(
            'change',
            async function () {

                try {

                    await apiRequest(
                        app.dataset
                            .preferenceUrl,
                        {
                            method:
                                'PATCH',

                            body: {
                                hide_empty_columns:
                                    hideEmpty.checked,
                            },
                        }
                    );

                    refreshBoard();

                } catch (error) {

                    showToast(
                        error.message,
                        'error'
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        resetButton?.addEventListener(
            'click',
            function () {

                searchInput.value = '';
                statusFilter.value = '';
                priorityFilter.value = '';
                dueFilter.value = '';

                refreshBoard();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | MANUAL REFRESH
        |--------------------------------------------------------------------------
        */

        refreshButton?.addEventListener(
            'click',
            function () {
                refreshBoard();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | WINDOW FOCUS
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'focus',
            function () {
                refreshBoard(true);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | VISIBILITY CHANGE
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'visibilitychange',
            function () {

                if (
                    document.visibilityState
                    === 'visible'
                ) {
                    refreshBoard(true);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | AUTO REFRESH
        |--------------------------------------------------------------------------
        */

        refreshTimer =
            setInterval(
                function () {

                    if (
                        document.visibilityState
                        === 'visible'
                    ) {
                        refreshBoard(true);
                    }

                },
                45000
            );


        /*
        |--------------------------------------------------------------------------
        | INITIAL STATE
        |--------------------------------------------------------------------------
        */

        updateUndoButton();
    }
);