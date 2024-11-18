import React from "react";
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import useAuth from "../../utilities/hooks/useAuth";
import Login from "../../pages/Login";
import Signup from "../../pages/Signup";
import Dashboard from "../../pages/Dashboard";

const AppRouter = () => {
    const { logged_in } = useAuth(); // Get the logged-in status from useAuth

    return (
        <Router>
            <Routes>
                {/* Redirect logged-in users from login and signup */}
                <Route
                    path="/login"
                    element={logged_in ? <Navigate to="/dashboard" /> : <Login />}
                />
                <Route
                    path="/signup"
                    element={logged_in ? <Navigate to="/dashboard" /> : <Signup />}
                />

                {/* Dashboard Route (Only Accessible if Logged In) */}
                <Route
                    path="/dashboard"
                    element={logged_in ? <Dashboard /> : <Navigate to="/login" />}
                />

                {/* Fallback to Login */}
                <Route path="*" element={<Navigate to={logged_in ? "/dashboard" : "/login"} />} />
            </Routes>
        </Router>
    );
};

export default AppRouter;
