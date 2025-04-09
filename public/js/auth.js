// Archivo para manejar la autenticación y almacenamiento de tokens

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form[action*="login"]');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            axios.post('/api/login', {
                email: email,
                password: password
            }, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                localStorage.setItem('token', response.data.access_token);
                window.location.href = '/weather';
            })
            .catch(error => {
                console.error('Error de autenticación:', error);
                alert('Error de autenticación. Por favor, verifique sus credenciales.');
            });
        });
    }
});


axios.interceptors.request.use(function (config) {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});