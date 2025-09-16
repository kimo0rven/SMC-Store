function requireLogin(actionIfLoggedIn) {
    if (!isLoggedIn) {
        modal.showModal();
        return false;
    }
    if (typeof actionIfLoggedIn === 'function') {
        actionIfLoggedIn();
    }
    return true;
}
