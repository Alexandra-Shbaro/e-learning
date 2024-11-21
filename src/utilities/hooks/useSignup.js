import { useState } from "react";
import axios from "axios";

const useSignup = () => {
    const [token, setToken] = useState(null)
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(false);

    const signup = async (username, email, password) => {
        setLoading(true);
        setError(null);
        setSuccess(false);

        try {
            const response = await axios.post(
                "http://localhost/e-learning-backend/signup.php", // Replace with your PHP endpoint URL
                { username, email, password },
                {
                    headers: {
                        "Content-Type": "application/json",
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
                setError(response.data.message || 'Signup failed');
                return { success: false, message: response.data.message || 'Signup failed' };
            }
        } catch (err) {
            setError(err.message || "An unexpected error occurred");
            return { success: false, message: err.message || "An unexpected error occurred" };
        } finally {
            setLoading(false);
        }
    };

    return { token, signup, loading, error, success };
};

export default useSignup;
