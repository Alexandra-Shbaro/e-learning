import { useState, useEffect } from 'react';
import { jwtDecode } from 'jwt-decode'; // Import jwt-decode

const useAuth = () => {
    const [token, setToken] = useState(null);
    const [user_id, setUserId] = useState(null);
    const [username, setUsername] = useState(null);
    const [user_type, setUserType] = useState(null);

    useEffect(() => {
        // Retrieve the token from localStorage when the component mounts
        const storedToken = localStorage.getItem('jwtToken');
        setToken(storedToken);

        if (storedToken) {
            try {
                // Decode the token using jwt-decode
                const decoded = jwtDecode(storedToken);

                // Extract specific fields
                setUserId(decoded?.data?.user_id || null);
                setUsername(decoded?.data?.username || null);
                setUserType(decoded?.data?.user_type || null);
            } catch (error) {
                console.error('Error decoding token:', error);
            }
        }
    }, []); // empty dependency array , means this function will only run once when the component is loaded

    return { token, logged_in: token ? true : false, user_id, username, user_type };
};

export default useAuth;
