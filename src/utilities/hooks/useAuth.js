import { useState, useEffect } from 'react';
import { jwtDecode } from 'jwt-decode'; 

const useAuth = () => {
    const [token, setToken] = useState(null);
    const [user_id, setUserId] = useState(null);
    const [username, setUsername] = useState(null);
    const [user_type, setUserType] = useState(null);

    useEffect(() => {
        const storedToken = localStorage.getItem('jwtToken');
        setToken(storedToken);

        if (storedToken) {
            try {
                const decoded = jwtDecode(storedToken);

                setUserId(decoded?.data?.user_id || null);
                setUsername(decoded?.data?.username || null);
                setUserType(decoded?.data?.user_type || null);
            } catch (error) {
                console.error('Error decoding token:', error);
            }
        }
    }, []); 

    return { token, logged_in: token ? true : false, user_id, username, user_type };
};

export default useAuth;
