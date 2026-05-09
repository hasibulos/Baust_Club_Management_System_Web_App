// Simple welcome redirect if needed
setTimeout(() => {
    if (window.location.pathname.endsWith('welcome.php')) {
        window.location.href = '../user/home.php';
    }
}, 2500);
