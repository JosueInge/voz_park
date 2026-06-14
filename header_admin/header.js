const logoutBtn = document.getElementById('logoutBtn');
const logoutModal = document.getElementById('logoutModal');

const cancelBtn = document.getElementById('cabcelBtn');
const confirmBtn = document.getElementById('confirmBtn');

logoutBtn.addEventListener('click', () => {
    logoutModal.classList.add('active');  
});

cancelBtn.addEventListener('click', () => {
    logoutModal.classList.remove('active');
});

confirmBtn.addEventListener('click', () => {
    logoutModal.classList.remove('active');

    window.location.href = 'login.php';
});