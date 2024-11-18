import React from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Login from "../../pages/Login";
import Signup from "../../pages/Signup";
import Dashboard from "../../pages/Dashboard";


const AppRouter = () => {
    return (
        <Router>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/signup" element={<Signup />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="*" element={<Login />} /> {/* Fallback to Login */}
            </Routes>
        </Router>
    );
};

export default AppRouter;
