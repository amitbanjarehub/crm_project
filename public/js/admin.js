document.addEventListener('DOMContentLoaded', function () {
    /*
     * =====================================================
     * ELEMENT REFERENCES
     * =====================================================
     */

    const adminButton =
        document.getElementById(
            'adminDropdownBtn'
        );

    const adminMenu =
        document.getElementById(
            'adminDropdownMenu'
        );

    const adminArrow =
        document.getElementById(
            'dropdownArrow'
        );

    const notificationRoot =
        document.getElementById(
            'notificationDropdown'
        );

    const notificationButton =
        document.getElementById(
            'notificationDropdownBtn'
        );

    const notificationMenu =
        document.getElementById(
            'notificationDropdownMenu'
        );

    const notificationList =
        document.getElementById(
            'notificationDropdownList'
        );

    const notificationBadge =
        document.getElementById(
            'notificationBellBadge'
        );

    const notificationUnreadText =
        document.getElementById(
            'notificationUnreadText'
        );

    const notificationMarkAllForm =
        document.getElementById(
            'notificationMarkAllForm'
        );

    const toastContainer =
        document.getElementById(
            'notificationToastContainer'
        );

    /*
     * Fixed dropdown ko body ke andar move karo.
     * Isse overflow aur z-index issue nahi aayega.
     */
    if (adminMenu) {
        document.body.appendChild(
            adminMenu
        );
    }

    if (notificationMenu) {
        document.body.appendChild(
            notificationMenu
        );
    }

    let isAdminDropdownOpen = false;
    let isNotificationDropdownOpen = false;

    /*
     * =====================================================
     * COMMON DROPDOWN POSITION
     * =====================================================
     */

    function setFixedDropdownPosition(
        button,
        menu,
        preferredWidth
    ) {
        if (!button || !menu) {
            return;
        }

        const buttonRect =
            button.getBoundingClientRect();

        const screenPadding = 12;

        const menuWidth = Math.min(
            preferredWidth,
            window.innerWidth
            - (screenPadding * 2)
        );

        let leftPosition =
            buttonRect.right - menuWidth;

        if (leftPosition < screenPadding) {
            leftPosition = screenPadding;
        }

        if (
            leftPosition + menuWidth
            > window.innerWidth - screenPadding
        ) {
            leftPosition =
                window.innerWidth
                - menuWidth
                - screenPadding;
        }

        menu.style.width =
            `${menuWidth}px`;

        menu.style.top =
            `${buttonRect.bottom + 10}px`;

        menu.style.left =
            `${leftPosition}px`;

        menu.style.right = 'auto';
    }

    /*
     * =====================================================
     * ADMIN DROPDOWN
     * =====================================================
     */

    function openAdminDropdown() {
        closeNotificationDropdown();

        setFixedDropdownPosition(
            adminButton,
            adminMenu,
            230
        );

        adminMenu?.classList.add('show');

        adminButton?.classList.add(
            'dropdown-open'
        );

        adminButton?.setAttribute(
            'aria-expanded',
            'true'
        );

        adminMenu?.setAttribute(
            'aria-hidden',
            'false'
        );

        adminArrow?.classList.add(
            'rotate'
        );

        isAdminDropdownOpen = true;
    }

    function closeAdminDropdown() {
        adminMenu?.classList.remove('show');

        adminButton?.classList.remove(
            'dropdown-open'
        );

        adminButton?.setAttribute(
            'aria-expanded',
            'false'
        );

        adminMenu?.setAttribute(
            'aria-hidden',
            'true'
        );

        adminArrow?.classList.remove(
            'rotate'
        );

        isAdminDropdownOpen = false;
    }

    if (
        adminButton
        && adminMenu
    ) {
        adminButton.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (isAdminDropdownOpen) {
                    closeAdminDropdown();
                } else {
                    openAdminDropdown();
                }
            }
        );

        adminMenu.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();
            }
        );
    }

    /*
     * =====================================================
     * NOTIFICATION DROPDOWN
     * =====================================================
     */

    function openNotificationDropdown() {
        closeAdminDropdown();

        setFixedDropdownPosition(
            notificationButton,
            notificationMenu,
            390
        );

        notificationMenu?.classList.add(
            'show'
        );

        notificationButton?.classList.add(
            'notification-open'
        );

        notificationButton?.setAttribute(
            'aria-expanded',
            'true'
        );

        notificationMenu?.setAttribute(
            'aria-hidden',
            'false'
        );

        isNotificationDropdownOpen = true;
    }

    function closeNotificationDropdown() {
        notificationMenu?.classList.remove(
            'show'
        );

        notificationButton?.classList.remove(
            'notification-open'
        );

        notificationButton?.setAttribute(
            'aria-expanded',
            'false'
        );

        notificationMenu?.setAttribute(
            'aria-hidden',
            'true'
        );

        isNotificationDropdownOpen = false;
    }

    if (
        notificationButton
        && notificationMenu
    ) {
        notificationButton.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (
                    isNotificationDropdownOpen
                ) {
                    closeNotificationDropdown();
                } else {
                    openNotificationDropdown();
                }
            }
        );

        notificationMenu.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();
            }
        );
    }

    /*
     * =====================================================
     * GLOBAL DROPDOWN EVENTS
     * =====================================================
     */

    document.addEventListener(
        'click',
        function (event) {
            const clickedAdminButton =
                adminButton?.contains(
                    event.target
                );

            const clickedAdminMenu =
                adminMenu?.contains(
                    event.target
                );

            const clickedNotificationButton =
                notificationButton?.contains(
                    event.target
                );

            const clickedNotificationMenu =
                notificationMenu?.contains(
                    event.target
                );

            if (
                !clickedAdminButton
                && !clickedAdminMenu
            ) {
                closeAdminDropdown();
            }

            if (
                !clickedNotificationButton
                && !clickedNotificationMenu
            ) {
                closeNotificationDropdown();
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeAdminDropdown();
                closeNotificationDropdown();
            }
        }
    );

    function repositionOpenDropdowns() {
        if (isAdminDropdownOpen) {
            setFixedDropdownPosition(
                adminButton,
                adminMenu,
                230
            );
        }

        if (isNotificationDropdownOpen) {
            setFixedDropdownPosition(
                notificationButton,
                notificationMenu,
                390
            );
        }
    }

    window.addEventListener(
        'resize',
        repositionOpenDropdowns
    );

    window.addEventListener(
        'scroll',
        repositionOpenDropdowns,
        true
    );

    /*
     * =====================================================
     * AJAX LIVE NOTIFICATION POLLING
     * =====================================================
     */

    if (
        !notificationRoot
        || !notificationList
    ) {
        return;
    }

    const pollUrl =
        notificationRoot.dataset.pollUrl;

    const parsedInterval = Number.parseInt(
        notificationRoot.dataset.pollInterval,
        10
    );

    const pollingInterval =
        Number.isFinite(parsedInterval)
            && parsedInterval >= 3000
            ? parsedInterval
            : 4000;

    if (!pollUrl) {
        return;
    }

    /*
     * Page load ke time jo notifications already
     * visible hain unki IDs remember karo.
     *
     * In notifications ke toast dobara show nahi honge.
     */
    const knownNotificationIds =
        new Set();

    const knownNotificationOrder = [];

    function rememberNotificationId(id) {
        const normalizedId = String(id);

        if (
            knownNotificationIds.has(
                normalizedId
            )
        ) {
            return;
        }

        knownNotificationIds.add(
            normalizedId
        );

        knownNotificationOrder.push(
            normalizedId
        );

        /*
         * Bahut long-open browser tab me memory
         * unnecessarily grow na kare.
         */
        while (
            knownNotificationOrder.length
            > 200
        ) {
            const oldestId =
                knownNotificationOrder.shift();

            if (oldestId) {
                knownNotificationIds.delete(
                    oldestId
                );
            }
        }
    }

    notificationList
        .querySelectorAll(
            '[data-notification-id]'
        )
        .forEach(function (element) {
            rememberNotificationId(
                element.dataset.notificationId
            );
        });

    let isPolling = false;
    let pollingStopped = false;

    /*
     * =====================================================
     * UPDATE UNREAD COUNT
     * =====================================================
     */

    function updateUnreadCount(count) {
        const unreadCount = Math.max(
            0,
            Number.parseInt(count, 10) || 0
        );

        if (notificationBadge) {
            notificationBadge.textContent =
                unreadCount > 99
                    ? '99+'
                    : String(unreadCount);

            notificationBadge.classList.toggle(
                'notification-hidden',
                unreadCount === 0
            );
        }

        if (notificationUnreadText) {
            notificationUnreadText.textContent =
                unreadCount === 1
                    ? '1 unread notification'
                    : `${unreadCount} unread notifications`;
        }

        if (notificationMarkAllForm) {
            notificationMarkAllForm.classList.toggle(
                'notification-hidden',
                unreadCount === 0
            );
        }
    }

    /*
     * =====================================================
     * CREATE DROPDOWN NOTIFICATION ITEM
     * =====================================================
     */

    function createDropdownItem(item) {
        const link =
            document.createElement('a');

        link.href =
            item.open_url || '#';

        link.className =
            'notification-dropdown-item';

        if (item.is_unread) {
            link.classList.add('unread');
        }

        link.dataset.notificationId =
            String(item.id);

        const icon =
            document.createElement('div');

        icon.className =
            'notification-dropdown-icon';

        icon.textContent =
            item.icon || '🔔';

        const content =
            document.createElement('div');

        content.className =
            'notification-dropdown-content';

        const title =
            document.createElement('strong');

        title.textContent =
            item.title
            || 'CRM Notification';

        content.appendChild(title);

        if (item.message) {
            const message =
                document.createElement('p');

            message.textContent =
                item.message;

            content.appendChild(message);
        }

        const time =
            document.createElement('small');

        time.textContent =
            item.time_ago
            || 'Just now';

        content.appendChild(time);

        link.appendChild(icon);
        link.appendChild(content);

        if (item.is_unread) {
            const unreadDot =
                document.createElement('span');

            unreadDot.className =
                'notification-dropdown-dot';

            link.appendChild(unreadDot);
        }

        return link;
    }

    /*
     * =====================================================
     * RENDER LATEST NOTIFICATIONS IN DROPDOWN
     * =====================================================
     */

    function renderNotificationDropdown(items) {
        const fragment =
            document.createDocumentFragment();

        if (
            !Array.isArray(items)
            || items.length === 0
        ) {
            const empty =
                document.createElement('div');

            empty.className =
                'notification-dropdown-empty';

            const icon =
                document.createElement('span');

            icon.textContent = '🔔';

            const title =
                document.createElement('strong');

            title.textContent =
                'No notifications';

            const message =
                document.createElement('p');

            message.textContent =
                'New CRM updates will appear here.';

            empty.appendChild(icon);
            empty.appendChild(title);
            empty.appendChild(message);

            fragment.appendChild(empty);
        } else {
            items.forEach(function (item) {
                fragment.appendChild(
                    createDropdownItem(item)
                );
            });
        }

        notificationList.replaceChildren(
            fragment
        );
    }

    /*
     * =====================================================
     * BELL ARRIVAL ANIMATION
     * =====================================================
     */

    function animateNotificationBell() {
        if (!notificationButton) {
            return;
        }

        notificationButton.classList.remove(
            'notification-arrived'
        );

        /*
         * Browser animation restart.
         */
        void notificationButton.offsetWidth;

        notificationButton.classList.add(
            'notification-arrived'
        );

        window.setTimeout(function () {
            notificationButton.classList.remove(
                'notification-arrived'
            );
        }, 1200);
    }

    /*
     * =====================================================
     * LIVE TOAST POPUP
     * =====================================================
     */

    function showLiveToast(item) {
        if (!toastContainer) {
            return;
        }

        const allowedLevels = [
            'info',
            'success',
            'warning',
            'danger',
        ];

        const level =
            allowedLevels.includes(item.level)
                ? item.level
                : 'info';

        const toast =
            document.createElement('article');

        toast.className =
            `notification-live-toast level-${level}`;

        toast.setAttribute(
            'role',
            'status'
        );

        const icon =
            document.createElement('div');

        icon.className =
            'notification-live-toast-icon';

        icon.textContent =
            item.icon || '🔔';

        const body =
            document.createElement('div');

        body.className =
            'notification-live-toast-body';

        const title =
            document.createElement('strong');

        title.textContent =
            item.title
            || 'CRM Notification';

        body.appendChild(title);

        if (item.message) {
            const message =
                document.createElement('p');

            message.textContent =
                item.message;

            body.appendChild(message);
        }

        const footer =
            document.createElement('div');

        footer.className =
            'notification-live-toast-footer';

        const time =
            document.createElement('span');

        time.textContent =
            item.time_ago
            || 'Just now';

        const openLink =
            document.createElement('a');

        openLink.href =
            item.open_url || '#';

        openLink.textContent =
            'Open';

        footer.appendChild(time);
        footer.appendChild(openLink);

        body.appendChild(footer);

        const closeButton =
            document.createElement('button');

        closeButton.type = 'button';

        closeButton.className =
            'notification-live-toast-close';

        closeButton.setAttribute(
            'aria-label',
            'Close notification'
        );

        closeButton.textContent = '×';

        toast.appendChild(icon);
        toast.appendChild(body);
        toast.appendChild(closeButton);

        toastContainer.appendChild(toast);

        let removeTimer =
            window.setTimeout(
                function () {
                    removeToast(toast);
                },
                8000
            );

        toast.addEventListener(
            'mouseenter',
            function () {
                window.clearTimeout(
                    removeTimer
                );
            }
        );

        toast.addEventListener(
            'mouseleave',
            function () {
                removeTimer =
                    window.setTimeout(
                        function () {
                            removeToast(toast);
                        },
                        4000
                    );
            }
        );

        closeButton.addEventListener(
            'click',
            function () {
                removeToast(toast);
            }
        );
    }

    function removeToast(toast) {
        if (
            !toast
            || toast.classList.contains(
                'removing'
            )
        ) {
            return;
        }

        toast.classList.add('removing');

        window.setTimeout(function () {
            toast.remove();
        }, 220);
    }

    /*
     * =====================================================
     * POLL SERVER
     * =====================================================
     */

    async function pollNotifications() {
        if (
            isPolling
            || pollingStopped
            || document.hidden
        ) {
            return;
        }

        isPolling = true;

        try {
            const requestUrl =
                new URL(
                    pollUrl,
                    window.location.origin
                );

            /*
             * Browser/proxy cache bypass.
             */
            requestUrl.searchParams.set(
                '_',
                String(Date.now())
            );

            const response = await fetch(
                requestUrl.toString(),
                {
                    method: 'GET',

                    headers: {
                        'Accept':
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    credentials:
                        'same-origin',

                    cache:
                        'no-store',
                }
            );

            /*
             * Login session expire ho gayi.
             */
            if (
                response.status === 401
                || response.status === 419
            ) {
                pollingStopped = true;
                return;
            }

            if (!response.ok) {
                throw new Error(
                    `Notification polling failed: ${response.status}`
                );
            }

            const payload =
                await response.json();

            if (!payload.success) {
                return;
            }

            const notifications =
                Array.isArray(
                    payload.notifications
                )
                    ? payload.notifications
                    : [];

            /*
             * Jo ID pehle known nahi thi wahi
             * actual new notification hai.
             */
            const newNotifications =
                notifications.filter(
                    function (item) {
                        return (
                            item
                            && item.id
                            && !knownNotificationIds
                                .has(
                                    String(item.id)
                                )
                        );
                    }
                );

            updateUnreadCount(
                payload.unread_count
            );

            renderNotificationDropdown(
                notifications
            );

            notifications.forEach(
                function (item) {
                    if (item && item.id) {
                        rememberNotificationId(
                            item.id
                        );
                    }
                }
            );

            if (
                newNotifications.length > 0
            ) {
                animateNotificationBell();

                /*
                 * API latest-first return karti hai.
                 * Toast oldest-first show karenge.
                 */
                newNotifications
                    .slice()
                    .reverse()
                    .forEach(
                        function (item) {
                            showLiveToast(item);
                        }
                    );
            }
        } catch (error) {
            /*
             * Temporary network/server error par
             * page ya UI break nahi hogi.
             */
            console.warn(
                'Live notification polling error:',
                error
            );
        } finally {
            isPolling = false;
        }
    }

    /*
     * Page load ke 2 seconds baad first check.
     */
    window.setTimeout(
        pollNotifications,
        2000
    );

    /*
     * Phir every 4 seconds check.
     */
    window.setInterval(
        pollNotifications,
        pollingInterval
    );

    /*
     * Hidden browser tab me polling skip hogi.
     * Tab visible hote hi immediately check.
     */
    document.addEventListener(
        'visibilitychange',
        function () {
            if (!document.hidden) {
                pollNotifications();
            }
        }
    );

    window.addEventListener(
        'focus',
        pollNotifications
    );

    /*
 * =====================================================
 * SIDEBAR COLLAPSIBLE MENUS
 * =====================================================
 */

    const sidebarGroupToggles =
        document.querySelectorAll(
            '.sidebar-group-toggle'
        );

    sidebarGroupToggles.forEach(
        function (toggle) {

            toggle.addEventListener(
                'click',
                function () {

                    const group =
                        toggle.closest(
                            '.sidebar-menu-group'
                        );

                    if (!group) {
                        return;
                    }

                    const submenu =
                        group.querySelector(
                            '.sidebar-submenu'
                        );

                    if (!submenu) {
                        return;
                    }

                    const isOpen =
                        toggle.getAttribute(
                            'aria-expanded'
                        ) === 'true';

                    /*
                     * Toggle current submenu.
                     */
                    if (isOpen) {

                        toggle.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                        submenu.hidden =
                            true;

                        group.classList.remove(
                            'is-open'
                        );

                    } else {

                        toggle.setAttribute(
                            'aria-expanded',
                            'true'
                        );

                        submenu.hidden =
                            false;

                        group.classList.add(
                            'is-open'
                        );

                    }

                }
            );

        }
    );
});