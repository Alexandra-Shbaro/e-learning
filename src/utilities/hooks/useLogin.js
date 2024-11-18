import { useState } from 'react';
import axios from 'axios';

const useLogin = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [token, setToken] = useState(null);

    const login = async (username, password) => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.post(
                'http://localhost/e-learning-backend/login.php', // Replace with your PHP endpoint URL
                { username, password },
                {
                    headers: {
                        'Content-Type': 'application/json',
                    },
                }
            );

            if (response.data.success) {
                const receivedToken = response.data.token;

                // Store the token in localStorage for persistent login
                localStorage.setItem('jwtToken', receivedToken);

                setToken(receivedToken);
                return { success: true, token: receivedToken };
            } else {
                setError(response.data.message || 'Login failed');
                return { success: false, message: response.data.message || 'Login failed' };
            }
        } catch (err) {
            setError(err.message || 'An unexpected error occurred');
            return { success: false, message: err.message || 'An unexpected error occurred' };
        } finally {
            setLoading(false);
        }
    };

    return { login, loading, error, token };
};

export default useLogin;
