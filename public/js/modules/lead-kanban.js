document.addEventListener(
    'DOMContentLoaded',
    function () {
        'use strict';

        const app =
            document.getElementById(
                'leadKanbanApp'
            );

        if (!app) {
            return;
        }

        const boardContainer =
            document.getElementById(
                'leadKanbanBoardContainer'
            );

        const filterForm =
            document.getElementById(
                'leadKanbanFilterForm'
            );

        const groupBySelect =
            document.getElementById(
                'kanbanGroupBy'
            );

        const searchInput =
            document.getElementById(
                'kanbanSearch'
            );

        const hideEmptyCheckbox =
            document.getElementById(
                'kanbanHideEmpty'
            );

        const refreshButton =
            document.getElementById(
                'kanbanRefreshButton'
            );

        const resetButton =
            document.getElementById(
                'kanbanResetFilters'
            );

        const undoButton =
            document.getElementById(
                'kanbanUndoButton'
            );

        const savingState =
            document.getElementById(
                'kanbanSavingState'
            );

        const lastUpdated =
            document.getElementById(
                'kanbanLastUpdated'
            );

        const totalLeads =
            document.getElementById(
                'kanbanTotalLeads'
            );

        const drawerOverlay =
            document.getElementById(
                'kanbanDrawerOverlay'
            );

        const drawer =
            document.getElementById(
                'kanbanLeadDrawer'
            );

        const toastContainer =
            document.getElementById(
                'kanbanToastContainer'
            );

        const csrfToken =
            document
                .querySelector(
                    'meta[name="csrf-token"]'
                )
                ?.getAttribute(
                    'content'
                );

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

        function replaceLeadInUrl(
            template,
            leadId
        ) {
            return template.replace(
                '__LEAD__',
                String(leadId)
            );
        }

        function getKanbanReturnUrl() {
            const url =
                new URL(
                    window.location.href
                );

            /*
             * Drawer open hone par URL me ?lead=ID
             * hota hai. Return karte waqt drawer automatically
             * open na ho, isliye lead parameter remove karenge.
             */
            url.searchParams.delete(
                'lead'
            );

            return url.toString();
        }

        function syncKanbanReturnLink(
            link
        ) {
            if (!link) {
                return;
            }

            const url =
                new URL(
                    link.href,
                    window.location.origin
                );

            url.searchParams.set(
                'return_url',
                getKanbanReturnUrl()
            );

            link.href =
                url.toString();
        }

        function syncKanbanReturnForm(
            form
        ) {
            if (!form) {
                return;
            }

            let input =
                form.querySelector(
                    'input[name="return_url"]'
                );

            if (!input) {
                input =
                    document.createElement(
                        'input'
                    );

                input.type =
                    'hidden';

                input.name =
                    'return_url';

                form.appendChild(
                    input
                );
            }

            input.value =
                getKanbanReturnUrl();
        }

        function syncKanbanReturnTargets(
            root = document
        ) {
            root
                .querySelectorAll(
                    'a.js-kanban-return-link'
                )
                .forEach(
                    function (link) {
                        syncKanbanReturnLink(
                            link
                        );
                    }
                );

            root
                .querySelectorAll(
                    'form.js-kanban-return-form'
                )
                .forEach(
                    function (form) {
                        syncKanbanReturnForm(
                            form
                        );
                    }
                );
        }

        document.addEventListener(
            'click',
            function (event) {
                const link =
                    event.target.closest(
                        'a.js-kanban-return-link'
                    );

                if (!link) {
                    return;
                }

                syncKanbanReturnLink(
                    link
                );
            },
            true
        );

        document.addEventListener(
            'submit',
            function (event) {
                const form =
                    event.target.closest(
                        'form.js-kanban-return-form'
                    );

                if (!form) {
                    return;
                }

                syncKanbanReturnForm(
                    form
                );
            },
            true
        );

        async function apiRequest(
            url,
            options = {}
        ) {
            pendingRequests++;

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
                                'Accept':
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
                        firstValidationMessage(
                            payload
                        )
                        || payload.message
                        || 'Request failed.';

                    const requestError =
                        new Error(message);

                    requestError.status =
                        response.status;

                    requestError.payload =
                        payload;

                    throw requestError;
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

        function firstValidationMessage(
            payload
        ) {
            if (
                !payload
                || !payload.errors
            ) {
                return '';
            }

            const firstKey =
                Object.keys(
                    payload.errors
                )[0];

            if (!firstKey) {
                return '';
            }

            const messages =
                payload.errors[firstKey];

            return Array.isArray(
                messages
            )
                ? messages[0]
                : String(messages);
        }

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
                'kanban-saving-state '
                + state;
        }

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
                'kanban-toast '
                + type;

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

            window.setTimeout(
                function () {
                    toast.classList.remove(
                        'show'
                    );

                    window.setTimeout(
                        function () {
                            toast.remove();
                        },
                        250
                    );
                },
                3500
            );
        }

        function currentBoard() {
            return document.getElementById(
                'leadKanbanBoard'
            );
        }

        function currentGroupBy() {
            return groupBySelect
                ?.value
                || 'status';
        }

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
                        String(value)
                            .trim();

                    if (cleaned !== '') {
                        url.searchParams.set(
                            key,
                            cleaned
                        );
                    }
                }
            );

            return url;
        }

        function updateBrowserUrl() {
            const url =
                new URL(
                    window.location.href
                );

            const formData =
                new FormData(
                    filterForm
                );

            [
                'group_by',
                'search',
                'status',
                'priority',
                'source',
                'assigned_to',
            ].forEach(
                function (key) {
                    url.searchParams.delete(
                        key
                    );
                }
            );

            formData.forEach(
                function (
                    value,
                    key
                ) {
                    const cleaned =
                        String(value)
                            .trim();

                    if (cleaned !== '') {
                        url.searchParams.set(
                            key,
                            cleaned
                        );
                    }
                }
            );

            window.history.replaceState(
                {},
                '',
                url
            );
        }

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
                boardContainer
                    .classList
                    .add('is-loading');
            }

            try {
                const response =
                    await fetch(
                        buildQueryUrl(),
                        {
                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                if (!response.ok) {
                    throw new Error(
                        'Unable to refresh Kanban board.'
                    );
                }

                const payload =
                    await response.json();

                closeDrawer();

                boardContainer.innerHTML =
                    payload.html;

                syncKanbanReturnTargets(
                    boardContainer
                );

                if (totalLeads) {
                    totalLeads.textContent =
                        payload.total;
                }

                if (lastUpdated) {
                    lastUpdated.textContent =
                        payload.updated_at;
                }

                updateBrowserUrl();

                if (!silent) {
                    showToast(
                        'Kanban board refreshed.'
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
                boardContainer
                    .classList
                    .remove('is-loading');
            }
        }

        async function savePreference(
            data
        ) {
            return apiRequest(
                app.dataset.preferenceUrl,
                {
                    method: 'PATCH',
                    body: data,
                }
            );
        }

        function updateColumnCounts() {
            document
                .querySelectorAll(
                    '.lead-kanban-column'
                )
                .forEach(
                    function (column) {
                        const list =
                            column.querySelector(
                                '.kanban-card-list'
                            );

                        const count =
                            list
                                ? Array.from(
                                    list.children
                                ).filter(
                                    function (
                                        child
                                    ) {
                                        return child
                                            .classList
                                            .contains(
                                                'kanban-lead-card'
                                            );
                                    }
                                ).length
                                : 0;

                        const countElement =
                            column.querySelector(
                                '.kanban-column-count'
                            );

                        if (
                            countElement
                        ) {
                            countElement
                                .textContent =
                                count;
                        }

                        updateEmptyPlaceholder(
                            list
                        );
                    }
                );
        }

        function updateEmptyPlaceholder(
            list
        ) {
            if (!list) {
                return;
            }

            const cards =
                Array.from(
                    list.children
                ).filter(
                    function (child) {
                        return child
                            .classList
                            .contains(
                                'kanban-lead-card'
                            );
                    }
                );

            const existing =
                list.querySelector(
                    '.kanban-empty-column'
                );

            if (
                cards.length === 0
                && !existing
            ) {
                const empty =
                    document.createElement(
                        'div'
                    );

                empty.className =
                    'kanban-empty-column';

                empty.textContent =
                    'Drop a Lead here';

                list.appendChild(
                    empty
                );
            }

            if (
                cards.length > 0
                && existing
            ) {
                existing.remove();
            }
        }

        function removeEmptyPlaceholder(
            list
        ) {
            list
                ?.querySelector(
                    '.kanban-empty-column'
                )
                ?.remove();
        }

        function previousCardId(
            card
        ) {
            let element =
                card.previousElementSibling;

            while (element) {
                if (
                    element.classList
                        .contains(
                            'kanban-lead-card'
                        )
                ) {
                    return Number(
                        element.dataset
                            .leadId
                    );
                }

                element =
                    element
                        .previousElementSibling;
            }

            return null;
        }

        function nextCardId(
            card
        ) {
            let element =
                card.nextElementSibling;

            while (element) {
                if (
                    element.classList
                        .contains(
                            'kanban-lead-card'
                        )
                ) {
                    return Number(
                        element.dataset
                            .leadId
                    );
                }

                element =
                    element
                        .nextElementSibling;
            }

            return null;
        }

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
                && snapshot
                    .nextSibling
                    .parentNode
                === snapshot.parent
            ) {
                snapshot.parent
                    .insertBefore(
                        card,
                        snapshot.nextSibling
                    );
            } else {
                snapshot.parent
                    .appendChild(card);
            }

            updateColumnCounts();
        }

        function getCardAfterElement(
            container,
            mouseY
        ) {
            const cards =
                Array.from(
                    container.querySelectorAll(
                        '.kanban-lead-card:not(.dragging)'
                    )
                );

            return cards.reduce(
                function (
                    closest,
                    child
                ) {
                    const box =
                        child
                            .getBoundingClientRect();

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
                            offset:
                                offset,

                            element:
                                child,
                        };
                    }

                    return closest;
                },
                {
                    offset:
                        Number
                            .NEGATIVE_INFINITY,

                    element: null,
                }
            ).element;
        }

        function getColumnAfterElement(
            board,
            mouseX
        ) {
            const columns =
                Array.from(
                    board.querySelectorAll(
                        '.lead-kanban-column:not(.column-dragging)'
                    )
                );

            return columns.reduce(
                function (
                    closest,
                    column
                ) {
                    const box =
                        column
                            .getBoundingClientRect();

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
                            offset:
                                offset,

                            element:
                                column,
                        };
                    }

                    return closest;
                },
                {
                    offset:
                        Number
                            .NEGATIVE_INFINITY,

                    element: null,
                }
            ).element;
        }

        document.addEventListener(
            'dragstart',
            function (event) {
                const cardHandle =
                    event.target.closest(
                        '.kanban-card-drag-handle'
                    );

                if (cardHandle) {
                    draggedCard =
                        cardHandle.closest(
                            '.kanban-lead-card'
                        );

                    if (!draggedCard) {
                        return;
                    }

                    const parent =
                        draggedCard.parentElement;

                    cardSnapshot = {
                        parent:
                            parent,

                        nextSibling:
                            draggedCard
                                .nextElementSibling,

                        column:
                            parent.dataset
                                .columnSlug,

                        previousId:
                            previousCardId(
                                draggedCard
                            ),

                        nextId:
                            nextCardId(
                                draggedCard
                            ),
                    };

                    cardDropHandled =
                        false;

                    isDragging = true;

                    draggedCard
                        .classList
                        .add(
                            'dragging'
                        );

                    event.dataTransfer
                        .effectAllowed =
                        'move';

                    event.dataTransfer
                        .setData(
                            'text/plain',
                            draggedCard
                                .dataset
                                .leadId
                        );

                    return;
                }

                const columnHandle =
                    event.target.closest(
                        '.kanban-column-drag-handle'
                    );

                if (columnHandle) {
                    draggedColumn =
                        columnHandle.closest(
                            '.lead-kanban-column'
                        );

                    if (!draggedColumn) {
                        return;
                    }

                    columnSnapshot = {
                        parent:
                            draggedColumn
                                .parentElement,

                        nextSibling:
                            draggedColumn
                                .nextElementSibling,
                    };

                    columnDropHandled =
                        false;

                    isDragging = true;

                    draggedColumn
                        .classList
                        .add(
                            'column-dragging'
                        );

                    event.dataTransfer
                        .effectAllowed =
                        'move';

                    event.dataTransfer
                        .setData(
                            'text/plain',
                            draggedColumn
                                .dataset
                                .columnSlug
                        );
                }
            }
        );

        document.addEventListener(
            'dragover',
            function (event) {
                if (draggedCard) {
                    const list =
                        event.target.closest(
                            '.kanban-card-list'
                        );

                    if (!list) {
                        return;
                    }

                    const column =
                        list.closest(
                            '.lead-kanban-column'
                        );

                    if (
                        column.dataset
                            .dropAllowed
                        === '0'
                    ) {
                        column.classList.add(
                            'drop-denied'
                        );

                        return;
                    }

                    event.preventDefault();

                    removeEmptyPlaceholder(
                        list
                    );

                    const afterElement =
                        getCardAfterElement(
                            list,
                            event.clientY
                        );

                    if (!afterElement) {
                        list.appendChild(
                            draggedCard
                        );
                    } else {
                        list.insertBefore(
                            draggedCard,
                            afterElement
                        );
                    }

                    return;
                }

                if (draggedColumn) {
                    const board =
                        currentBoard();

                    if (!board) {
                        return;
                    }

                    event.preventDefault();

                    const afterColumn =
                        getColumnAfterElement(
                            board,
                            event.clientX
                        );

                    if (!afterColumn) {
                        board.appendChild(
                            draggedColumn
                        );
                    } else {
                        board.insertBefore(
                            draggedColumn,
                            afterColumn
                        );
                    }
                }
            }
        );

        document.addEventListener(
            'dragleave',
            function (event) {
                const column =
                    event.target.closest(
                        '.lead-kanban-column'
                    );

                column
                    ?.classList
                    .remove(
                        'drop-denied'
                    );
            }
        );

        document.addEventListener(
            'drop',
            async function (event) {
                if (draggedCard) {
                    const list =
                        event.target.closest(
                            '.kanban-card-list'
                        );

                    if (!list) {
                        return;
                    }

                    event.preventDefault();

                    const column =
                        list.closest(
                            '.lead-kanban-column'
                        );

                    if (
                        column.dataset
                            .dropAllowed
                        === '0'
                    ) {
                        restoreCard(
                            draggedCard,
                            cardSnapshot
                        );

                        showToast(
                            'Converted column me drag allowed nahi hai. Convert button use karein.',
                            'error'
                        );

                        return;
                    }

                    cardDropHandled =
                        true;

                    updateColumnCounts();

                    await persistCardMove(
                        draggedCard,
                        list,
                        cardSnapshot
                    );

                    return;
                }

                if (draggedColumn) {
                    const board =
                        currentBoard();

                    if (!board) {
                        return;
                    }

                    event.preventDefault();

                    columnDropHandled =
                        true;

                    const columns =
                        Array.from(
                            board.querySelectorAll(
                                '.lead-kanban-column'
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
                                        currentGroupBy(),

                                    columns:
                                        columns,
                                },
                            }
                        );

                        showToast(
                            'Column order saved.'
                        );
                    } catch (error) {
                        if (
                            columnSnapshot
                                ?.nextSibling
                            && columnSnapshot
                                .nextSibling
                                .parentNode
                            === columnSnapshot
                                .parent
                        ) {
                            columnSnapshot
                                .parent
                                .insertBefore(
                                    draggedColumn,
                                    columnSnapshot
                                        .nextSibling
                                );
                        } else {
                            columnSnapshot
                                ?.parent
                                ?.appendChild(
                                    draggedColumn
                                );
                        }

                        showToast(
                            error.message,
                            'error'
                        );
                    }
                }
            }
        );

        document.addEventListener(
            'dragend',
            function () {
                document
                    .querySelectorAll(
                        '.lead-kanban-column'
                    )
                    .forEach(
                        function (column) {
                            column
                                .classList
                                .remove(
                                    'drop-denied'
                                );
                        }
                    );

                if (draggedCard) {
                    if (
                        !cardDropHandled
                    ) {
                        restoreCard(
                            draggedCard,
                            cardSnapshot
                        );
                    }

                    draggedCard
                        .classList
                        .remove(
                            'dragging'
                        );
                }

                if (draggedColumn) {
                    if (
                        !columnDropHandled
                    ) {
                        if (
                            columnSnapshot
                                ?.nextSibling
                            && columnSnapshot
                                .nextSibling
                                .parentNode
                            === columnSnapshot
                                .parent
                        ) {
                            columnSnapshot
                                .parent
                                .insertBefore(
                                    draggedColumn,
                                    columnSnapshot
                                        .nextSibling
                                );
                        } else {
                            columnSnapshot
                                ?.parent
                                ?.appendChild(
                                    draggedColumn
                                );
                        }
                    }

                    draggedColumn
                        .classList
                        .remove(
                            'column-dragging'
                        );
                }

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

        async function persistCardMove(
            card,
            targetList,
            originalSnapshot
        ) {
            const leadId =
                Number(
                    card.dataset
                        .leadId
                );

            const targetColumn =
                targetList.dataset
                    .columnSlug;

            const payload = {
                group_by:
                    currentGroupBy(),

                target_column:
                    targetColumn,

                before_id:
                    previousCardId(
                        card
                    ),

                after_id:
                    nextCardId(
                        card
                    ),

                expected_version:
                    Number(
                        card.dataset
                            .kanbanVersion
                    ),
            };

            card.classList.add(
                'saving'
            );

            try {
                const response =
                    await apiRequest(
                        replaceLeadInUrl(
                            app.dataset
                                .moveUrlTemplate,
                            leadId
                        ),
                        {
                            method:
                                'PATCH',

                            body:
                                payload,
                        }
                    );

                card.dataset
                    .kanbanVersion =
                    response.lead
                        .kanban_version;

                card.dataset.status =
                    response.lead
                        .status
                        .slug;

                card.dataset.priority =
                    response.lead
                        .priority
                        .slug;

                applyLeadResponse(
                    card,
                    response.lead
                );

                lastUndo = {
                    leadId:
                        leadId,

                    card:
                        card,

                    targetColumn:
                        originalSnapshot
                            .column,

                    previousId:
                        originalSnapshot
                            .previousId,

                    nextId:
                        originalSnapshot
                            .nextId,

                    version:
                        response.lead
                            .kanban_version,
                };

                updateUndoButton();

                showToast(
                    'Lead moved successfully.'
                );
            } catch (error) {
                restoreCard(
                    card,
                    originalSnapshot
                );

                if (
                    error.status === 409
                ) {
                    showToast(
                        error.message,
                        'error'
                    );

                    await refreshBoard(
                        true
                    );
                } else {
                    showToast(
                        error.message,
                        'error'
                    );
                }
            } finally {
                card.classList.remove(
                    'saving'
                );

                updateColumnCounts();
            }
        }

        function applyLeadResponse(
            card,
            lead
        ) {
            const statusBadge =
                card.querySelector(
                    '.kanban-card-status'
                );

            if (statusBadge) {
                statusBadge.textContent =
                    lead.status.name;

                statusBadge.style
                    .setProperty(
                        '--kanban-badge-color',
                        lead.status.color
                    );
            }

            const priorityBadge =
                card.querySelector(
                    '.kanban-card-priority'
                );

            if (priorityBadge) {
                priorityBadge.textContent =
                    lead.priority.name;

                priorityBadge.style
                    .setProperty(
                        '--kanban-badge-color',
                        lead.priority.color
                    );
            }

            const followUp =
                card.querySelector(
                    '.kanban-followup'
                );

            if (
                followUp
                && !lead
                    .next_follow_up_at
            ) {
                followUp.classList.remove(
                    'overdue'
                );

                const strong =
                    followUp
                        .querySelector(
                            'strong'
                        );

                if (strong) {
                    strong.textContent =
                        'Not scheduled';
                }

                followUp
                    .querySelector(
                        'small'
                    )
                    ?.remove();
            }
        }

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
                        '.kanban-lead-card[data-lead-id="'
                        + lastUndo.leadId
                        + '"]'
                    );

                const targetList =
                    document.querySelector(
                        '.kanban-card-list[data-column-slug="'
                        + CSS.escape(
                            lastUndo
                                .targetColumn
                        )
                        + '"]'
                    );

                if (
                    !card
                    || !targetList
                ) {
                    lastUndo = null;
                    updateUndoButton();
                    return;
                }

                const rollbackSnapshot = {
                    parent:
                        card.parentElement,

                    nextSibling:
                        card
                            .nextElementSibling,
                };

                removeEmptyPlaceholder(
                    targetList
                );

                const nextCard =
                    lastUndo.nextId
                        ? targetList
                            .querySelector(
                                '.kanban-lead-card[data-lead-id="'
                                + lastUndo.nextId
                                + '"]'
                            )
                        : null;

                const previousCard =
                    lastUndo.previousId
                        ? targetList
                            .querySelector(
                                '.kanban-lead-card[data-lead-id="'
                                + lastUndo.previousId
                                + '"]'
                            )
                        : null;

                if (nextCard) {
                    targetList.insertBefore(
                        card,
                        nextCard
                    );
                } else if (
                    previousCard
                ) {
                    previousCard.after(
                        card
                    );
                } else {
                    targetList.appendChild(
                        card
                    );
                }

                updateColumnCounts();

                try {
                    const response =
                        await apiRequest(
                            replaceLeadInUrl(
                                app.dataset
                                    .moveUrlTemplate,
                                lastUndo
                                    .leadId
                            ),
                            {
                                method:
                                    'PATCH',

                                body: {
                                    group_by:
                                        currentGroupBy(),

                                    target_column:
                                        lastUndo
                                            .targetColumn,

                                    before_id:
                                        previousCardId(
                                            card
                                        ),

                                    after_id:
                                        nextCardId(
                                            card
                                        ),

                                    expected_version:
                                        Number(
                                            card.dataset
                                                .kanbanVersion
                                        ),
                                },
                            }
                        );

                    card.dataset
                        .kanbanVersion =
                        response.lead
                            .kanban_version;

                    card.dataset.status =
                        response.lead
                            .status
                            .slug;

                    card.dataset.priority =
                        response.lead
                            .priority
                            .slug;

                    applyLeadResponse(
                        card,
                        response.lead
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
            }
        );

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

                const card =
                    event.target.closest(
                        '.kanban-lead-card'
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
                    && event.target
                        .classList
                        .contains(
                            'kanban-lead-card'
                        )
                ) {
                    openDrawer(
                        event.target
                    );
                }

                if (
                    event.key
                    === 'Escape'
                ) {
                    closeDrawer();
                }
            }
        );

        async function openDrawer(
            card
        ) {
            const leadId =
                card.dataset
                    .leadId;

            selectedCard
                ?.classList
                .remove(
                    'selected'
                );

            selectedCard =
                card;

            selectedCard
                .classList
                .add(
                    'selected'
                );

            drawerOverlay
                .classList
                .add('open');

            drawerOverlay
                .setAttribute(
                    'aria-hidden',
                    'false'
                );

            drawer.innerHTML =
                '<div class="kanban-drawer-loading">'
                + 'Loading Lead details...'
                + '</div>';

            const url =
                new URL(
                    window.location.href
                );

            url.searchParams.set(
                'lead',
                leadId
            );

            window.history.replaceState(
                {},
                '',
                url
            );

            try {
                const response =
                    await fetch(
                        replaceLeadInUrl(
                            app.dataset
                                .detailsUrlTemplate,
                            leadId
                        ),
                        {
                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                if (!response.ok) {
                    throw new Error(
                        'Lead details could not be loaded.'
                    );
                }

                const payload =
                    await response.json();

                drawer.innerHTML =
                    payload.html;

                syncKanbanReturnTargets(
                    drawer
                );
            } catch (error) {
                drawer.innerHTML =
                    '<div class="kanban-drawer-error">'
                    + escapeHtml(
                        error.message
                    )
                    + '</div>';
            }
        }

        function closeDrawer() {
            drawerOverlay
                .classList
                .remove('open');

            drawerOverlay
                .setAttribute(
                    'aria-hidden',
                    'true'
                );

            selectedCard
                ?.classList
                .remove(
                    'selected'
                );

            selectedCard = null;

            const url =
                new URL(
                    window.location.href
                );

            url.searchParams.delete(
                'lead'
            );

            window.history.replaceState(
                {},
                '',
                url
            );
        }

        document.addEventListener(
            'click',
            function (event) {
                if (
                    event.target.id
                    === 'kanbanDrawerClose'
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

        searchInput?.addEventListener(
            'input',
            function () {
                window.clearTimeout(
                    searchTimer
                );

                searchTimer =
                    window.setTimeout(
                        function () {
                            refreshBoard();
                        },
                        350
                    );
            }
        );

        filterForm?.addEventListener(
            'submit',
            function (event) {
                event.preventDefault();
                refreshBoard();
            }
        );

        filterForm?.addEventListener(
            'change',
            async function (event) {
                if (
                    event.target
                    === groupBySelect
                ) {
                    lastUndo = null;
                    updateUndoButton();

                    try {
                        await savePreference({
                            group_by:
                                groupBySelect
                                    .value,
                        });

                        await refreshBoard();
                    } catch (error) {
                        showToast(
                            error.message,
                            'error'
                        );
                    }

                    return;
                }

                if (
                    event.target
                    === hideEmptyCheckbox
                ) {
                    try {
                        await savePreference({
                            hide_empty_columns:
                                hideEmptyCheckbox
                                    .checked,
                        });

                        await refreshBoard();
                    } catch (error) {
                        showToast(
                            error.message,
                            'error'
                        );
                    }

                    return;
                }

                refreshBoard();
            }
        );

        refreshButton?.addEventListener(
            'click',
            function () {
                refreshBoard();
            }
        );

        resetButton?.addEventListener(
            'click',
            function () {
                if (searchInput) {
                    searchInput.value =
                        '';
                }

                [
                    'kanbanStatusFilter',
                    'kanbanPriorityFilter',
                    'kanbanSourceFilter',
                    'kanbanAssignedFilter',
                ].forEach(
                    function (id) {
                        const element =
                            document
                                .getElementById(
                                    id
                                );

                        if (element) {
                            element.value =
                                '';
                        }
                    }
                );

                refreshBoard();
            }
        );

        function scheduleSilentRefresh() {
            window.clearInterval(
                refreshTimer
            );

            refreshTimer =
                window.setInterval(
                    function () {
                        if (
                            document
                                .visibilityState
                            === 'visible'
                        ) {
                            refreshBoard(
                                true
                            );
                        }
                    },
                    45000
                );
        }

        window.addEventListener(
            'focus',
            function () {
                refreshBoard(
                    true
                );
            }
        );

        document.addEventListener(
            'visibilitychange',
            function () {
                if (
                    document
                        .visibilityState
                    === 'visible'
                ) {
                    refreshBoard(
                        true
                    );
                }
            }
        );

        syncKanbanReturnTargets(
            app
        );
        scheduleSilentRefresh();
        updateUndoButton();

        const initialLead =
            new URL(
                window.location.href
            ).searchParams.get(
                'lead'
            );

        if (initialLead) {
            const initialCard =
                document.querySelector(
                    '.kanban-lead-card[data-lead-id="'
                    + initialLead
                    + '"]'
                );

            if (initialCard) {
                openDrawer(
                    initialCard
                );
            }
        }
    }
);